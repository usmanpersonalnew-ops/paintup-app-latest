<?php

namespace App\Services;

use Config;
use Illuminate\Support\Facades\Log;

class CCavenueService
{
    protected $merchantId;
    protected $accessCode;
    protected $workingKey;
    protected $redirectUrl;
    protected $env;

    public function __construct()
    {
        $this->merchantId = config('ccavenue.merchant_id');
        $this->accessCode = config('ccavenue.access_code');
        $this->workingKey = config('ccavenue.working_key');
        $this->redirectUrl = config('ccavenue.redirect_url');
        $this->env = config('ccavenue.env', 'test');

        // Validate working key is not empty
        if (empty($this->workingKey)) {
            Log::error('CCAvenue: Working key is empty! Please set CCAVENUE_WORKING_KEY in .env');
            throw new \Exception('CCAvenue working key is not configured. Please set CCAVENUE_WORKING_KEY in .env file.');
        }

        // Validate working key format (should be 32 hex characters for 128-bit key)
        if (strlen($this->workingKey) !== 32 || !ctype_xdigit($this->workingKey)) {
            Log::warning('CCAvenue: Working key format may be incorrect. Expected 32 hex characters.');
        }
    }

    /**
     * Encrypt the data using AES-128-CBC
     * CCAvenue uses a 16-byte IV (128 bits)
     * Based on CCAvenue's official encryption method
     */
    public function encrypt($plainText): string
    {
        if (empty($this->workingKey)) {
            Log::error('CCAvenue encrypt: Working key is empty');
            throw new \Exception('Working key is not configured');
        }

        if (empty($plainText)) {
            Log::error('CCAvenue encrypt: Plain text is empty');
            throw new \Exception('Plain text cannot be empty');
        }

        try {
            // Convert working key from hex to binary (16 bytes for AES-128)
            $key = $this->hexToBytes($this->workingKey);

            // CCAvenue uses a fixed IV: "1234567890123456" (16 bytes)
            // Converted to hex: 31323334353637383930313233343536
            $iv = hex2bin('31323334353637383930313233343536');

            // Ensure key is exactly 16 bytes (AES-128)
            if (strlen($key) !== 16) {
                Log::error('CCAvenue encrypt: Invalid key length', [
                    'key_length' => strlen($key),
                    'expected' => 16
                ]);
                throw new \Exception('Working key must be 32 hex characters (16 bytes)');
            }

            // Encrypt using AES-128-CBC with PKCS7 padding (default in OpenSSL)
            $encrypted = openssl_encrypt(
                $plainText,
                'AES-128-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($encrypted === false) {
                $error = openssl_error_string();
                Log::error('CCAvenue encrypt failed', [
                    'error' => $error,
                    'working_key_length' => strlen($this->workingKey),
                    'plain_text_length' => strlen($plainText),
                    'plain_text_preview' => substr($plainText, 0, 100)
                ]);
                throw new \Exception('Encryption failed: ' . ($error ?: 'Unknown error'));
            }

            // Convert binary to uppercase hex string
            $encryptedHex = strtoupper(bin2hex($encrypted));

            Log::debug('CCAvenue encryption successful', [
                'plain_length' => strlen($plainText),
                'encrypted_length' => strlen($encryptedHex),
                'first_50_chars' => substr($encryptedHex, 0, 50)
            ]);

            return $encryptedHex;
        } catch (\Exception $e) {
            Log::error('CCAvenue encryption error', [
                'message' => $e->getMessage(),
                'working_key_set' => !empty($this->workingKey),
                'plain_text_length' => strlen($plainText ?? '')
            ]);
            throw $e;
        }
    }

    /**
     * Decrypt the data using AES-128-CBC
     */
    public function decrypt($encryptedText): string
    {
        if (empty($this->workingKey)) {
            Log::error('CCAvenue decrypt: Working key is empty');
            throw new \Exception('Working key is not configured');
        }

        try {
            $key = $this->hexToBytes($this->workingKey);
            // Use a 16-byte IV - CCAvenue uses this specific IV
            $iv = $this->hexToBytes('31323334353637383930313233343536'); // "1234567890123456" in hex

            $encrypted = hex2bin($encryptedText);

            if ($encrypted === false) {
                Log::error('CCAvenue decrypt: Failed to convert hex to binary', [
                    'encrypted_text_length' => strlen($encryptedText)
                ]);
                throw new \Exception('Invalid encrypted text format');
            }

            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-128-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                Log::error('CCAvenue decrypt failed', [
                    'error' => openssl_error_string()
                ]);
                throw new \Exception('Decryption failed: ' . openssl_error_string());
            }

            return $decrypted;
        } catch (\Exception $e) {
            Log::error('CCAvenue decryption error', [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Convert hex string to bytes
     */
    protected function hexToBytes(string $hex): string
    {
        return hex2bin($hex);
    }

    /**
     * Generate payment URL for a specific amount
     */
    public function getPaymentUrl(string $orderId, float $amount, string $customerName, string $customerEmail, string $customerPhone, string $paymentType = 'booking'): string
    {
        $encRequest = $this->getEncryptedRequest($orderId, $amount, $customerName, $customerEmail, $customerPhone, $paymentType);

        // Validate encrypted request is not empty
        if (empty($encRequest)) {
            Log::error('CCAvenue: Encrypted request is empty');
            throw new \Exception('Failed to encrypt payment request. Please check your CCAvenue configuration.');
        }

        // Use test URL for test mode, live URL for production
        $baseUrl = $this->env === 'test'
            ? 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction'
            : 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction';

        $paymentUrl = $baseUrl . '&encRequest=' . urlencode($encRequest) . '&access_code=' . urlencode($this->accessCode);

        Log::info('CCAvenue: Generated payment URL', [
            'order_id' => $orderId,
            'amount' => $amount,
            'enc_request_length' => strlen($encRequest),
            'url_length' => strlen($paymentUrl)
        ]);

        return $paymentUrl;
    }

    /**
     * Get the base URL for payment page (for iframe embed)
     */
    public function getPaymentBaseUrl(): string
    {
        return $this->env === 'test'
            ? 'https://test.ccavenue.com'
            : 'https://secure.ccavenue.com';
    }

    /**
     * Generate encrypted request data
     */
    public function getEncryptedRequest(string $orderId, float $amount, string $customerName, string $customerEmail, string $customerPhone, string $paymentType = 'booking'): string
    {
        // Validate required fields
        if (empty($this->merchantId) || empty($this->workingKey) || empty($this->accessCode)) {
            Log::error('CCAvenue: Missing required configuration', [
                'merchant_id_set' => !empty($this->merchantId),
                'working_key_set' => !empty($this->workingKey),
                'access_code_set' => !empty($this->accessCode),
            ]);
            throw new \Exception('CCAvenue configuration is incomplete. Please check your .env file.');
        }

        $billingName = $customerName;
        $billingAddress = 'Address';
        $billingCity = 'Mumbai';
        $billingState = 'Maharashtra';
        $billingZip = '400001';
        $billingCountry = 'India';
        $billingTel = $customerPhone;
        $billingEmail = $customerEmail;

        $deliveryName = $customerName;
        $deliveryAddress = 'Address';
        $deliveryCity = 'Mumbai';
        $deliveryState = 'Maharashtra';
        $deliveryZip = '400001';
        $deliveryCountry = 'India';
        $deliveryTel = $customerPhone;

        $merchantParam1 = $paymentType; // booking, mid, final

        $requestData = [
            'merchant_id' => $this->merchantId,
            'order_id' => $orderId,
            'currency' => 'INR',
            'amount' => number_format($amount, 2, '.', ''),
            'redirect_url' => route('payment.ccavenue.callback'),
            'cancel_url' => route('payment.ccavenue.cancel'),
            'language' => 'EN',
            'billing_name' => $billingName,
            'billing_address' => $billingAddress,
            'billing_city' => $billingCity,
            'billing_state' => $billingState,
            'billing_zip' => $billingZip,
            'billing_country' => $billingCountry,
            'billing_tel' => $billingTel,
            'billing_email' => $billingEmail,
            'delivery_name' => $deliveryName,
            'delivery_address' => $deliveryAddress,
            'delivery_city' => $deliveryCity,
            'delivery_state' => $deliveryState,
            'delivery_zip' => $deliveryZip,
            'delivery_country' => $deliveryCountry,
            'delivery_tel' => $deliveryTel,
            'merchant_param1' => $merchantParam1,
        ];

        // Build query string - CCAvenue requires specific format
        // DO NOT URL encode before encryption - http_build_query handles encoding
        $plainText = http_build_query($requestData, '', '&');

        // Validate that plain text is not empty
        if (empty($plainText)) {
            Log::error('CCAvenue: Request data is empty', [
                'request_data' => $requestData
            ]);
            throw new \Exception('Request data cannot be empty');
        }

        // Log the plain text (first 200 chars only for security)
        Log::info('CCAvenue: Encrypting request', [
            'merchant_id' => $this->merchantId,
            'order_id' => $orderId,
            'amount' => $amount,
            'working_key_length' => strlen($this->workingKey),
            'working_key_preview' => substr($this->workingKey, 0, 8) . '...',
            'plain_text_length' => strlen($plainText),
            'plain_text_preview' => substr($plainText, 0, 200)
        ]);

        $encrypted = $this->encrypt($plainText);

        Log::info('CCAvenue: Encryption successful', [
            'encrypted_length' => strlen($encrypted),
            'encrypted_preview' => substr($encrypted, 0, 100)
        ]);

        return $encrypted;
    }

    /**
     * Get merchant ID
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * Get access code
     */
    public function getAccessCode(): string
    {
        return $this->accessCode;
    }

    /**
     * Check if in test mode
     */
    public function isTestMode(): bool
    {
        return $this->env === 'test';
    }
}
