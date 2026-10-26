<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class XenditWebhookController extends Controller
{
    public function __construct(public XenditService $xendit) {}

    /**
     * Handle Xendit invoice webhook callback.
     */
    public function handleInvoice(Request $request): JsonResponse
    {
        $token = $request->header('x-callback-token', '');

        abort_unless($this->xendit->verifyWebhookToken($token), 403, 'Invalid webhook token.');

        $payload = $request->all();
        $externalId = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;
        $invoiceId = $payload['id'] ?? null;

        $order = Order::query()->where('order_number', $externalId)->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $payment = $order->payments()
            ->where('gateway_transaction_id', $invoiceId)
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        $newStatus = match ($status) {
            'PAID', 'SETTLED' => 'completed',
            'EXPIRED' => 'expired',
            default => 'pending',
        };

        DB::transaction(function () use ($order, $payment, $newStatus, $payload): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $lockedPayment = $lockedOrder->payments()
                ->where('id', $payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $wasCompleted = $lockedPayment->status === 'completed';

            $lockedPayment->update([
                'status' => $newStatus,
                'paid_at' => $newStatus === 'completed' ? now() : null,
                'raw_response' => json_encode($payload),
            ]);

            if ($newStatus === 'completed' && ! $wasCompleted) {
                $lockedOrder->load('orderItems');

                foreach ($lockedOrder->orderItems as $item) {
                    $product = Product::query()->lockForUpdate()->find($item->product_id);

                    abort_unless(
                        $product && $product->is_active && $product->stock >= $item->qty,
                        422,
                        "Insufficient stock when finalizing paid order for {$item->product_name}."
                    );
                }

                foreach ($lockedOrder->orderItems as $item) {
                    Product::query()
                        ->where('id', $item->product_id)
                        ->decrement('stock', $item->qty);
                }

                $lockedOrder->update(['status' => 'paid']);
            }
        });

        ActivityLog::log('payment_' . $newStatus, "Payment {$newStatus} for order {$order->order_number}.", $order->user_id, [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'xendit_invoice_id' => $invoiceId,
        ]);

        return response()->json(['message' => 'Webhook processed.']);
    }
}
