<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class XenditService
{
    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl('https://api.xendit.co')
            ->withBasicAuth(config('services.xendit.secret_key'), '')
            ->acceptJson()
            ->asJson();
    }

    /**
     * Create an invoice for the given order.
     *
    * @param  array{external_id: string, amount: float, payer_email: string, description: string, currency?: string, success_redirect_url?: string, failure_redirect_url?: string}  $params
     * @return array<string, mixed>
     */
    public function createInvoice(array $params): array
    {
        $response = $this->http->post('/v2/invoices', $params);

        $response->throw();

        return $response->json();
    }

    /**
     * Get invoice detail by ID.
     *
     * @return array<string, mixed>
     */
    public function getInvoice(string $invoiceId): array
    {
        $response = $this->http->get("/v2/invoices/{$invoiceId}");

        $response->throw();

        return $response->json();
    }

    /**
     * Verify webhook callback token.
     */
    public function verifyWebhookToken(string $token): bool
    {
        $webhookToken = (string) config('services.xendit.webhook_token');

        return $webhookToken !== '' && hash_equals($webhookToken, $token);
    }
}
