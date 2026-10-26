<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BiteshipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(public BiteshipService $biteship) {}

    /**
     * Get available shipping rates.
     */
    public function rates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin_postal_code' => ['required', 'string'],
            'destination_postal_code' => ['required', 'string'],
            'couriers' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.weight' => ['required', 'integer', 'min:1'],
            'items.*.value' => ['required', 'integer', 'min:0'],
        ]);

        $result = $this->biteship->getShippingRates($validated);

        return response()->json($result);
    }

    /**
     * Track a shipment by Biteship order ID.
     */
    public function track(string $orderId): JsonResponse
    {
        $result = $this->biteship->trackShipment($orderId);

        return response()->json($result);
    }
}
