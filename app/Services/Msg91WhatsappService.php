<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Msg91WhatsappService
{
    protected string $authKey;
    protected string $integratedNumber;
    protected string $templateName;
    protected string $namespace;

    public function __construct()
    {
        $this->authKey = env('MSG91_AUTH_KEY', '');
        $this->integratedNumber = env('MSG91_WHATSAPP_NUMBER', '916384238919');
        $this->templateName = env('MSG91_TEMPLATE_NAME', 'otp_auth');
        $this->namespace = env('MSG91_TEMPLATE_NAMESPACE', '');
    }

    /**
     * Send OTP via MSG91 WhatsApp using otp_auth template
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        // Clean phone number - remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure phone has country code
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        // Build payload EXACTLY as per working curl
        $payload = [
            'integrated_number' => $this->integratedNumber,
            'content_type' => 'template',
            'payload' => [
                'messaging_product' => 'whatsapp',
                'type' => 'template',
                'template' => [
                    'name' => $this->templateName,
                    'language' => [
                        'code' => 'en',
                        'policy' => 'deterministic'
                    ],
                    'namespace' => $this->namespace,
                    'to_and_components' => [
                        [
                            'to' => [$phone],
                            'components' => [
                                'body_1' => [
                                    'type' => 'text',
                                    'value' => $otp
                                ],
                                'button_1' => [
                                    'subtype' => 'url',
                                    'type' => 'text',
                                    'value' => '1'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Log request payload
        Log::info('MSG91 REQUEST PAYLOAD', $payload);

        try {
            // Skip if no auth key configured (dev mode)
            if (empty($this->authKey)) {
                Log::warning('MSG91 AUTH KEY not configured - skipping OTP send');
                return true; // Return true to allow login in dev mode
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'authkey' => $this->authKey,
            ])->post('https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/', $payload);

            // Log response body
            Log::info('MSG91 RESPONSE BODY', ['body' => $response->body()]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('MSG91 OTP ERROR', ['message' => $e->getMessage()]);
            return true; // Return true to allow login even if OTP fails
        }
    }

    /**
     * Send custom WhatsApp message (for quote notifications)
     */
    public function sendCustomMessage(string $phone, string $message): bool
    {
        // Clean phone number - remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure phone has country code
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        // Build payload EXACTLY as per working curl
        $payload = [
            'integrated_number' => $this->integratedNumber,
            'content_type' => 'template',
            'payload' => [
                'messaging_product' => 'whatsapp',
                'type' => 'template',
                'template' => [
                    'name' => $this->templateName,
                    'language' => [
                        'code' => 'en',
                        'policy' => 'deterministic'
                    ],
                    'namespace' => $this->namespace,
                    'to_and_components' => [
                        [
                            'to' => [$phone],
                            'components' => [
                                'body_1' => [
                                    'type' => 'text',
                                    'value' => $message
                                ],
                                'button_1' => [
                                    'subtype' => 'url',
                                    'type' => 'text',
                                    'value' => '1'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Log request payload
        Log::info('MSG91 REQUEST PAYLOAD', $payload);

        try {
            // Skip if no auth key configured (dev mode)
            if (empty($this->authKey)) {
                Log::warning('MSG91 AUTH KEY not configured - skipping message');
                return true;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'authkey' => $this->authKey,
            ])->post('https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/', $payload);

            // Log response body
            Log::info('MSG91 RESPONSE BODY', ['body' => $response->body()]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('MSG91 ERROR', ['message' => $e->getMessage()]);
            return true; // Return true to allow app to continue
        }
    }

    /**
     * Send quote shared message using paintup_quote_shared template
     */
    public function sendQuoteSharedMessage(string $phone, string $customerName): bool
    {
        // Clean phone number - remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure phone has country code
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        // Template expects 1 parameter: customer name
        // The login URL is hardcoded in the template itself
        $payload = [
            'integrated_number' => $this->integratedNumber,
            'content_type' => 'template',
            'payload' => [
                'messaging_product' => 'whatsapp',
                'type' => 'template',
                'template' => [
                    'name' => 'paintup_quote_shared',
                    'language' => [
                        'code' => 'en',
                        'policy' => 'deterministic'
                    ],
                    'namespace' => '66c315f0_de10_4a62_9a38_e17644d88cd2',
                    'to_and_components' => [
                        [
                            'to' => [$phone],
                            'components' => [
                                'body_1' => [
                                    'type' => 'text',
                                    'value' => $customerName
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        Log::info('MSG91 QUOTE SEND PAYLOAD', $payload);

        try {
            // Skip if no auth key configured (dev mode)
            if (empty($this->authKey)) {
                Log::warning('MSG91 AUTH KEY not configured - skipping quote message');
                return true;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'authkey' => $this->authKey,
            ])->post('https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/', $payload);

            Log::info('MSG91 QUOTE RESPONSE', ['body' => $response->body()]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('MSG91 QUOTE ERROR', ['message' => $e->getMessage()]);
            return true; // Return true to allow app to continue
        }
    }
}
