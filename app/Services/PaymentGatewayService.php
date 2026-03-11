<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    protected $gateway;

    protected $config;

    private const PHONEPE_TOKEN_CACHE_KEY = 'phonepe_access_token';

    public function __construct()
    {
        $this->gateway = config('paymentgateways.default', 'phonepe');
        $this->config = config('paymentgateways.' . $this->gateway);
    }

    /**
     * Create a payment order for the given project and amount
     */
    public function createPayment(Project $project, string $paymentType, float $amount): array
    {
        $paymentTypeMap = [
            'booking' => 'BOOKING',
            'mid' => 'MID',
            'final' => 'FINAL',
        ];

        $paymentPurpose = $paymentTypeMap[$paymentType] ?? 'BOOKING';

        return match ($this->gateway) {
            'phonepe' => $this->createPhonePePayment($project, $paymentPurpose, $amount),
            'ccavenue' => $this->createCCAvenuePayment($project, $paymentPurpose, $amount),
            default => throw new \Exception("Payment gateway '{$this->gateway}' is not supported"),
        };
    }

    /**
     * Verify payment callback from gateway
     */
    public function verifyCallback(string $gateway, array $data): bool
    {
        return match ($gateway) {
            'phonepe' => $this->verifyPhonePeCallback($data),
            'ccavenue' => $this->verifyCCAvenueCallback($data),
            default => false,
        };
    }

    /**
     * Get current gateway name
     */
    public function getGatewayName(): string
    {
        return $this->gateway;
    }

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    // ==================== PHONEPE V2 IMPLEMENTATION ====================

    /**
     * Get PhonePe OAuth access token (cached until expiry).
     */
    protected function getPhonePeAccessToken(): string
    {
        $config = config('paymentgateways.phonepe');
        $cached = Cache::get(self::PHONEPE_TOKEN_CACHE_KEY);
        if ($cached && isset($cached['token'], $cached['expires_at']) && $cached['expires_at'] > time() + 60) {
            return $cached['token'];
        }

        $isSandbox = ($config['environment'] ?? 'sandbox') === 'sandbox';
        $tokenUrl = $isSandbox
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token'
            : 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

        $response = Http::asForm()->post($tokenUrl, [
            'client_id' => $config['client_id'],
            'client_version' => $config['client_version'] ?? 1,
            'client_secret' => $config['client_secret'],
            'grant_type' => 'client_credentials',
        ]);

        $result = $response->json();
        if (!$response->successful() || empty($result['access_token'])) {
            Log::error('PhonePe OAuth token failed', ['response' => $result]);
            throw new \Exception('PhonePe authentication failed. Check PHONEPE_CLIENT_ID, PHONEPE_CLIENT_SECRET, PHONEPE_CLIENT_VERSION in .env');
        }

        $expiresAt = $result['expires_at'] ?? (time() + 3600);
        Cache::put(self::PHONEPE_TOKEN_CACHE_KEY, [
            'token' => $result['access_token'],
            'expires_at' => $expiresAt,
        ], now()->addSeconds($expiresAt - time() - 60));

        return $result['access_token'];
    }

    protected function createPhonePePayment(Project $project, string $paymentPurpose, float $amount): array
    {
        $config = config('paymentgateways.phonepe');

        if (!$config['enabled']) {
            throw new \Exception('PhonePe payment gateway is not enabled');
        }

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            throw new \Exception('PhonePe V2 credentials missing. Set PHONEPE_CLIENT_ID and PHONEPE_CLIENT_SECRET in .env (from PhonePe Dashboard > Developer Settings).');
        }

        // merchantOrderId: max 63 chars, only _ and - allowed
        $merchantOrderId = 'PU-' . $project->id . '-' . strtoupper($paymentPurpose) . '-' . time();
        $amountPaisa = (int) round($amount * 100);
        if ($amountPaisa < 100) {
            throw new \Exception('Amount must be at least ₹1 (100 paisa).');
        }

        $redirectUrl = route('customer.payment.callback', ['gateway' => 'phonepe']);

        $body = [
            'merchantOrderId' => $merchantOrderId,
            'amount' => $amountPaisa,
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'merchantUrls' => [
                    'redirectUrl' => $redirectUrl,
                ],
            ],
        ];

        $isSandbox = ($config['environment'] ?? 'sandbox') === 'sandbox';
        $payUrl = $isSandbox
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay'
            : 'https://api.phonepe.com/apis/pg/checkout/v2/pay';

        try {
            $token = $this->getPhonePeAccessToken();
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $token,
            ])->post($payUrl, $body);

            $result = $response->json();

            if ($response->successful() && !empty($result['redirectUrl'])) {
                return [
                    'success' => true,
                    'gateway' => 'phonepe',
                    'transaction_id' => $merchantOrderId,
                    'payment_url' => $result['redirectUrl'],
                    'amount' => $amount,
                    'purpose' => $paymentPurpose,
                ];
            }

            Log::error('PhonePe payment creation failed', ['response' => $result]);
            return [
                'success' => false,
                'error' => $result['message'] ?? $result['code'] ?? 'Payment creation failed',
            ];
        } catch (\Exception $e) {
            Log::error('PhonePe payment error', ['exception' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check PhonePe order status (V2 Order Status API).
     * Returns state: PENDING | COMPLETED | FAILED, or null on error.
     */
    public function getPhonePeOrderStatus(string $merchantOrderId): ?array
    {
        $config = config('paymentgateways.phonepe');
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return null;
        }

        $isSandbox = ($config['environment'] ?? 'sandbox') === 'sandbox';
        $statusUrl = $isSandbox
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/order/' . urlencode($merchantOrderId) . '/status'
            : 'https://api.phonepe.com/apis/pg/checkout/v2/order/' . urlencode($merchantOrderId) . '/status';

        try {
            $token = $this->getPhonePeAccessToken();
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $token,
            ])->get($statusUrl . '?details=false');

            $result = $response->json();
            if ($response->successful() && isset($result['state'])) {
                return $result;
            }
            return null;
        } catch (\Exception $e) {
            Log::warning('PhonePe order status failed', ['order' => $merchantOrderId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    protected function verifyPhonePeCallback(array $data): bool
    {
        // V2: redirect does not send 'response'; verification is done via Order Status API in controller using session merchantOrderId
        if (isset($data['response'])) {
            $decoded = json_decode(base64_decode($data['response']), true);
            return isset($decoded['success']) && $decoded['success'] === 'true';
        }
        return false;
    }

    // ==================== CCAVENUE IMPLEMENTATION ====================

    protected function createCCAvenuePayment(Project $project, string $paymentPurpose, float $amount): array
    {
        $config = config('paymentgateways.ccavenue');

        if (!$config['enabled']) {
            throw new \Exception('CCAvenue payment gateway is not enabled');
        }

        $orderId = 'PU-' . $project->id . '-' . strtoupper($paymentPurpose) . '-' . time();

        $params = [
            'merchant_id' => $config['merchant_id'],
            'order_id' => $orderId,
            'currency' => 'INR',
            'amount' => number_format($amount, 2, '.', ''),
            'redirect_url' => route('api.payment.ccavenue.redirect'),
            'cancel_url' => route('customer.payment.cancel', ['gateway' => 'ccavenue']),
            'language' => 'EN',
            'billing_name' => $project->customer_name ?? 'Customer',
            'billing_tel' => $project->customer_phone ?? '',
            'billing_email' => $project->customer_email ?? '',
        ];

        $encryptedParams = $this->encryptCCAvenueParams($params);

        $isSandbox = $config['environment'] === 'sandbox';
        $baseUrl = $isSandbox
            ? 'https://secure.ccavenue.com/apis/servlet/DoWebTrans'
            : 'https://secure.ccavenue.com/apis/servlet/DoWebTrans';

        return [
            'success' => true,
            'gateway' => 'ccavenue',
            'transaction_id' => $orderId,
            'payment_url' => $baseUrl,
            'encrypted_params' => $encryptedParams,
            'access_code' => $config['access_code'],
            'amount' => $amount,
            'purpose' => $paymentPurpose,
        ];
    }

    protected function encryptCCAvenueParams(array $params): string
    {
        $config = config('paymentgateways.ccavenue');
        $workingKey = $config['working_key'];

        $plainText = http_build_query($params);

        $encrypted = '';
        for ($i = 0; $i < strlen($plainText); $i++) {
            $encrypted .= chr(ord($plainText[$i]) ^ ord($workingKey[$i % strlen($workingKey)]));
        }

        return bin2hex($encrypted);
    }

    protected function verifyCCAvenueCallback(array $data): bool
    {
        if (!isset($data['encResp'])) {
            return false;
        }

        return true;
    }
}
