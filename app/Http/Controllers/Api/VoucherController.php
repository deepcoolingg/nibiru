<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $voucher = Voucher::query()
            ->where('code', $request->input('code'))
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->first();

        if (! $voucher) {
            return response()->json(['message' => 'Voucher is invalid or expired.'], 422);
        }

        return response()->json([
            'data' => new VoucherResource($voucher),
        ]);
    }
}
