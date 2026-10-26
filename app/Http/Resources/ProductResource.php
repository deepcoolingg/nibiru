<?php

namespace App\Http\Resources;

use App\Models\ProductDetailSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $defaultProductDetail = config('catalog.product_detail', []);
        $globalSetting = ProductDetailSetting::query()->first();
        $stock = (int) $this->stock;
        $imageUrl = route('api.products.image', ['product' => $this->id]);
        $sizeFitImageUrl = route('api.products.size-fit-image', ['product' => $this->id]);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description
                ?: ($globalSetting?->description ?: ($defaultProductDetail['description'] ?? null)),
            'description_highlights' => $globalSetting?->description_highlights
                ?: ($defaultProductDetail['highlights'] ?? []),
            'image' => $imageUrl,
            'image_path' => $this->image,
            'image_url' => $imageUrl,
            'imageUrl' => $imageUrl,
            'size_fit_image' => $this->size_fit_image
                ?: ($globalSetting?->size_fit_image ?: ($defaultProductDetail['size_fit_image'] ?? null)),
            'size_fit_image_path' => $this->size_fit_image,
            'size_fit_image_url' => $sizeFitImageUrl,
            'sizeFitImageUrl' => $sizeFitImageUrl,
            'color_name' => $this->color_name,
            'color_hex' => $this->color_hex,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock' => $this->stock,
            'badge' => $this->resolveBadge($stock),
            'is_sold_out' => $stock <= 0,
            'weight' => $this->weight,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }

    protected function resolveBadge(int $stock): string
    {
        if ($stock <= 0) {
            return 'SOLD OUT';
        }

        if ($this->isRecentlyRestocked()) {
            return 'RESTOCK!';
        }

        if ($stock < 10) {
            return 'LIMIT!';
        }

        if ($this->isRecentlyCreated()) {
            return 'NEW!';
        }

        return crc32((string) $this->id) % 2 === 0 ? 'CUTE!' : 'BOLD!';
    }

    protected function isRecentlyCreated(): bool
    {
        return $this->created_at !== null && $this->created_at->greaterThan(now()->subDays(7));
    }

    protected function isRecentlyRestocked(): bool
    {
        if ($this->restocked_at === null) {
            return false;
        }

        return $this->restocked_at->greaterThan(now()->subDays(7));
    }
}
