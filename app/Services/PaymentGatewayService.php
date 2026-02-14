<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    protected $gateway;
    protected $config;

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

    // ==================== PHONEPE IMPLEMENTATION ====================

    protected function createPhonePePayment(Project $project, string $paymentPurpose, float $amount): array
    {
        $config = config('paymentgateways.phonepe');
        
        if (!$config['enabled']) {
            throw new \Exception('PhonePe payment gateway is not enabled');
        }

        $merchantTransactionId = 'PU-' . $project->id . '-' . strtoupper($paymentPurpose) . '-' . time();
        $merchantUserId = 'CUST-' . $project->customer_id ?? 'GUEST';
        
        $payload = [
            'merchantId' => $config['merchant_id'],
            'merchantTransactionId' => $merchantTransactionId,
            'merchantUserId' => $merchantUserId,
            'amount' => (int) ($amount * 100),
            'redirectUrl' => route('customer.payment.callback', ['gateway' => 'phonepe']),
            'redirectMode' => 'POST',
            'callbackUrl' => route('api.payment.phonepe.callback'),
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];

        $base64Payload = base64_encode(json_encode($payload));
        $verifyString = $base64Payload . '/pg/v1/status/' . $config['merchant_id'] . '/' . $merchantTransactionId;
        $hash = hash('sha256', $verifyString) . '###' . $config['salt_index'];

        $isSandbox = $config['environment'] === 'sandbox';
        $baseUrl = $isSandbox 
            ? 'https://api-preprod.phonepe.com/apis/pg/payment/v4/create' 
            : 'https://api.phonepe.com/apis/pg/payment/v4/create';

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $hash,
                'X-MERCHANT-ID' => $config['merchant_id'],
            ])->post($baseUrl, $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['data']['instrumentResponse']['redirectInfo']['url'])) {
                return [
                    'success' => true,
                    'gateway' => 'phonepe',
                    'transaction_id' => $merchantTransactionId,
                    'payment_url' => $result['data']['instrumentResponse']['redirectInfo']['url'],
                    'amount' => $amount,
                    'purpose' => $paymentPurpose,
                ];
            }

            Log::error('PhonePe payment creation failed', ['response' => $result]);
            return [
                'success' => false,
                'error' => $result['message'] ?? 'Payment creation failed',
            ];
        } catch (\Exception $e) {
            Log::error('PhonePe payment error', ['exception' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function verifyPhonePeCallback(array $data): bool
    {
        $config = config('paymentgateways.phonepe');
        
        if (!isset($data['response'])) {
            return false;
        }

        $responseData = json_decode(base64_decode($data['response']), true);
        
        return isset($responseData['success']) && $responseData['success'] === 'true';
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
            ? 'https://test.ccavenue.com/apis/servlet/DoWebTrans' 
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