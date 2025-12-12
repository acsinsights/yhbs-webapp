<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OttuService
{
    protected string $merchantId;
    protected string $apiKey;
    protected string $apiUrl;
    protected string $currency;

    public function __construct()
    {
        $this->merchantId = config('services.ottu.merchant_id');
        $this->apiKey = config('services.ottu.api_key');
        $this->apiUrl = config('services.ottu.api_url');
        $this->currency = config('services.ottu.currency', 'KWD');
    }

    /**
     * Create a payment session with Ottu
     *
     * @param array $data Payment data
     * @return array Response from Ottu API
     */
    public function createPaymentSession(array $data): array
    {
        try {
            $payload = $this->buildPaymentPayload($data);

            $response = Http::withHeaders([
                'Authorization' => "Api-Key {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/b/checkout/v1/pymt-txn/", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Ottu payment session creation failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Failed to create payment session',
            ];
        } catch (\Exception $e) {
            Log::error('Ottu payment session exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while creating payment session',
            ];
        }
    }

    /**
     * Get payment session details
     *
     * @param string $sessionId
     * @return array
     */
    public function getPaymentSession(string $sessionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Api-Key {$this->apiKey}",
            ])->get("{$this->apiUrl}/b/checkout/v1/pymt-txn/{$sessionId}/");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to retrieve payment session',
            ];
        } catch (\Exception $e) {
            Log::error('Ottu get payment session exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while retrieving payment session',
            ];
        }
    }

    /**
     * Verify webhook signature
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('services.ottu.webhook_secret');
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Build payment payload for Ottu API
     *
     * @param array $data
     * @return array
     */
    protected function buildPaymentPayload(array $data): array
    {
        return [
            'type' => $data['type'] ?? 'payment_request',
            'pg_codes' => $data['pg_codes'] ?? [],
            'amount' => $data['amount'],
            'currency_code' => $data['currency_code'] ?? $this->currency,
            'customer_id' => $data['customer_id'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_first_name' => $data['customer_first_name'] ?? null,
            'customer_last_name' => $data['customer_last_name'] ?? null,
            'order_no' => $data['order_no'] ?? Str::uuid()->toString(),
            'reference_number' => $data['reference_number'] ?? null,
            'notifications' => [
                'email' => $data['notifications']['email'] ?? [],
                'sms' => $data['notifications']['sms'] ?? [],
            ],
            'vendor_name' => $data['vendor_name'] ?? config('app.name'),
            'webhook_url' => $data['webhook_url'] ?? route('ottu.webhook'),
            'redirect_url' => $data['redirect_url'] ?? route('ottu.success'),
            'language' => $data['language'] ?? app()->getLocale(),
            'disclosure_url' => $data['disclosure_url'] ?? null,
            'extra' => $data['extra'] ?? null,
            'expiration_time' => $data['expiration_time'] ?? null,
            'include_sdk_setup_preload' => $data['include_sdk_setup_preload'] ?? true,
        ];
    }

    /**
     * Get supported payment gateways
     *
     * @return array
     */
    public function getSupportedPaymentGateways(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Api-Key {$this->apiKey}",
            ])->get("{$this->apiUrl}/b/pbl/v2/payment-gateway/");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to retrieve payment gateways',
            ];
        } catch (\Exception $e) {
            Log::error('Ottu get payment gateways exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while retrieving payment gateways',
            ];
        }
    }

    /**
     * Cancel a payment transaction
     *
     * @param string $sessionId
     * @return array
     */
    public function cancelPayment(string $sessionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Api-Key {$this->apiKey}",
            ])->delete("{$this->apiUrl}/b/checkout/v1/pymt-txn/{$sessionId}/");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to cancel payment',
            ];
        } catch (\Exception $e) {
            Log::error('Ottu cancel payment exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while canceling payment',
            ];
        }
    }
}
