<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request): CartResource
    {
        $cart = $this->resolveCart($request);

        $cart->load('items.product');

        return new CartResource($cart);
    }

    public function addItem(AddCartItemRequest $request): CartResource
    {
        $cart = $this->resolveCart($request, createIfMissing: true);

        $product = Product::query()->where('is_active', true)->findOrFail($request->validated('product_id'));

        $existingItem = $cart->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            $existingItem->update([
                'qty' => $existingItem->qty + $request->validated('qty'),
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'qty' => $request->validated('qty'),
            ]);
        }

        $cart->load('items.product');

        return new CartResource($cart);
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): CartResource
    {
        $cart = $this->resolveCart($request);

        abort_unless($cartItem->cart_id === $cart->id, 403);

        $cartItem->update([
            'qty' => $request->validated('qty'),
        ]);

        $cart->load('items.product');

        return new CartResource($cart);
    }

    public function removeItem(Request $request, CartItem $cartItem): CartResource
    {
        $cart = $this->resolveCart($request);

        abort_unless($cartItem->cart_id === $cart->id, 403);

        $cartItem->delete();

        $cart->load('items.product');

        return new CartResource($cart);
    }

    /**
     * Resolve the cart for the current user or session.
     */
    private function resolveCart(Request $request, bool $createIfMissing = false): Cart
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id');

        if ($user) {
            $query = Cart::query()->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query = Cart::query()->where('session_id', $sessionId);
        } else {
            abort(400, 'Provide authentication or X-Session-Id header.');
        }

        $cart = $query->first();

        if (! $cart && $createIfMissing) {
            $cart = Cart::query()->create([
                'user_id' => $user?->id,
                'session_id' => $user ? null : $sessionId,
            ]);
        }

        abort_unless($cart, 404, 'Cart not found.');

        return $cart;
    }
}
