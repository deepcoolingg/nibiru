<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BiteshipService
{
    private string $apiKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.biteship.api_key');
        $this->baseUrl = config('services.biteship.base_url');
    }

    /**
     * Get shipping rates for given origin, destination, and items.
     *
     * @param  array{
     *     origin_postal_code: string,
     *     destination_postal_code: string,
     *     couriers: string,
     *     items: array<int, array{name: string, quantity: int, weight: int, value: int}>
     * }  $params
     * @return array<string, mixed>
     */
    public function getShippingRates(array $params): array
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withHeaders(['Authorization' => $this->apiKey])
            ->post('/v1/rates/couriers', $params);

        $response->throw();

        return $response->json();
    }

    /**
     * Create a shipment order on Biteship.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function createShipment(array $params): array
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withHeaders(['Authorization' => $this->apiKey])
            ->post('/v1/orders', $params);

        $response->throw();

        return $response->json();
    }

    /**
     * Track a shipment by Biteship order ID.
     */
    public function trackShipment(string $orderId): array
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withHeaders(['Authorization' => $this->apiKey])
            ->get("/v1/trackings/{$orderId}");

        $response->throw();

        return $response->json();
    }
}
