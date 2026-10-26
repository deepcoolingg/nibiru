<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Payment;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(public XenditService $xendit) {}

    /**
     * Create a Xendit invoice for a pending order.
     */
    public function create(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->status === 'pending', 422, 'Order is not payable.');

        // Check if there's already a pending payment
        $existingPayment = $order->payments()->where('status', 'pending')->first();
        if ($existingPayment && $existingPayment->gateway_transaction_id) {
            $invoice = $this->xendit->getInvoice($existingPayment->gateway_transaction_id);

            return response()->json([
                'payment_id' => $existingPayment->id,
                'invoice_url' => $invoice['invoice_url'],
                'status' => $existingPayment->status,
            ]);
        }

        $exchangeRate = (float) ($order->exchange_rate ?: 1);
        $currency = (string) ($order->currency ?: 'IDR');
        $amountInOrderCurrency = $currency === 'IDR'
            ? (float) $order->grand_total
            : round((float) $order->grand_total / max($exchangeRate, 1), 2);

        $invoice = $this->xendit->createInvoice([
            'external_id' => $order->order_number,
            'amount' => $amountInOrderCurrency,
            'currency' => $currency,
            'payer_email' => $request->user()->email,
            'description' => "Payment for order {$order->order_number}",
        ]);

        $payment = $order->payments()->create([
            'payment_method' => 'xendit_invoice',
            'gateway' => 'xendit',
            'gateway_transaction_id' => $invoice['id'],
            'amount' => $order->grand_total,
            'status' => 'pending',
        ]);

        ActivityLog::log('payment_created', "Payment initiated for order {$order->order_number}.", $request->user()->id, [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'amount' => $order->grand_total,
        ]);

        return response()->json([
            'payment_id' => $payment->id,
            'invoice_url' => $invoice['invoice_url'],
            'status' => 'pending',
        ]);
    }

    /**
     * Check payment status.
     */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        abort_unless($payment->order->user_id === $request->user()->id, 403);

        return response()->json([
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'gateway' => $payment->gateway,
            'paid_at' => $payment->paid_at,
        ]);
    }
}
