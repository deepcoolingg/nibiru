<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ExchangeRateController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\XenditWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/auth/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/google', [AuthController::class, 'googleLogin']);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}/image', [ProductController::class, 'image'])->name('api.products.image');
Route::get('/products/{product}/size-fit-image', [ProductController::class, 'sizeFitImage'])->name('api.products.size-fit-image');
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::get('/exchange-rates', [ExchangeRateController::class, 'index']);

Route::get('/ping', fn () => response()->json([
    'message' => 'API connected',
    'time' => now()->toIso8601String(),
]));

Route::post('/vouchers/check', [VoucherController::class, 'check']);

// Cart — works with X-Session-Id header (guest) or auth token
Route::get('/cart', [CartController::class, 'show']);
Route::post('/cart/items', [CartController::class, 'addItem']);
Route::put('/cart/items/{cartItem}', [CartController::class, 'updateItem']);
Route::delete('/cart/items/{cartItem}', [CartController::class, 'removeItem']);

// Shipping rates (public)
Route::post('/shipping/rates', [ShippingController::class, 'rates']);
Route::get('/checkout/options', [CheckoutController::class, 'options']);

// Xendit webhook (public, verified by callback token)
Route::post('/webhooks/xendit', [XenditWebhookController::class, 'handleInvoice']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateProfile']);

    Route::post('/checkout', [CheckoutController::class, 'store']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    Route::post('/orders/{order}/pay', [PaymentController::class, 'create']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);

    Route::get('/shipping/track/{orderId}', [ShippingController::class, 'track']);
});
