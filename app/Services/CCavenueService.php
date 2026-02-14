<?php

namespace App\Services;

use Config;

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
    }

    /**
     * Encrypt the data using AES-128-CBC
     * CCAvenue uses a 16-byte IV (128 bits)
     */
    public function encrypt($plainText): string
    {
        $key = $this->hexToBytes($this->workingKey);
        // Use a 16-byte IV - CCAvenue uses this specific IV
        $iv = $this->hexToBytes('31323334353637383930313233343536'); // "1234567890123456" in hex
        
        $encrypted = openssl_encrypt(
            $plainText,
            'AES-128-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return strtoupper(bin2hex($encrypted));
    }

    /**
     * Decrypt the data using AES-128-CBC
     */
    public function decrypt($encryptedText): string
    {
        $key = $this->hexToBytes($this->workingKey);
        // Use a 16-byte IV - CCAvenue uses this specific IV
        $iv = $this->hexToBytes('31323334353637383930313233343536'); // "1234567890123456" in hex
        
        $encrypted = hexToBin($encryptedText);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-128-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted;
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
        
        // Use test URL for test mode, live URL for production
        $baseUrl = $this->env === 'test'
            ? 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction'
            : 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
        
        return $baseUrl . '&encRequest=' . $encRequest . '&access_code=' . $this->accessCode;
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

        $plainText = http_build_query($requestData);
        
        return $this->encrypt($plainText);
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