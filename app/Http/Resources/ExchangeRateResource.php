<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currency_code' => $this->currency_code,
            'rate_to_idr' => $this->rate_to_idr,
            'updated_at' => $this->updated_at,
        ];
    }
}
