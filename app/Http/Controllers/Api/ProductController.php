<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductDetailSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->where('is_active', true)
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate($request->input('per_page', 12));

        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_active, 404);

        return new ProductResource($product);
    }

    public function image(Product $product): BinaryFileResponse
    {
        abort_unless($product->is_active, 404);

        return $this->serveProductAsset($product->image);
    }

    public function sizeFitImage(Product $product): BinaryFileResponse
    {
        abort_unless($product->is_active, 404);

        return $this->serveProductAsset($this->resolveSizeFitImagePath($product));
    }

    private function resolveSizeFitImagePath(Product $product): ?string
    {
        if (! empty($product->size_fit_image)) {
            return $product->size_fit_image;
        }

        $globalSetting = ProductDetailSetting::query()->first();
        if (! empty($globalSetting?->size_fit_image)) {
            return $globalSetting->size_fit_image;
        }

        return config('catalog.product_detail.size_fit_image');
    }

    private function serveProductAsset(?string $path): BinaryFileResponse
    {
        abort_unless($path, 404);

        $privatePath = ltrim($path, '/');

        if (Storage::disk('local')->exists($privatePath)) {
            return response()->file(
                Storage::disk('local')->path($privatePath),
                ['Cache-Control' => 'public, max-age=86400']
            );
        }

        abort_unless(Storage::disk('public')->exists($path), 404);

        return response()->file(
            Storage::disk('public')->path($path),
            ['Cache-Control' => 'public, max-age=86400']
        );
    }
}
