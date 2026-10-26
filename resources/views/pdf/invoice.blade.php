<!DOCTYPE html>
<html>
<head>
    <title>Resi #{{ $order->order_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 20px; }
        .content { line-height: 1.6; }
        .badge { padding: 4px 8px; color: white; background-color: #000; border-radius: 4px; font-weight: bold; font-size: 12px; margin-left: 10px; }
        .box { border: 1px solid #ddd; padding: 15px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RESI PENGIRIMAN - SEHELAI NIBIRU</h2>
        <p>No. Order: <strong>{{ $order->order_number }}</strong></p>
    </div>
    
    <div class="box content">
        <p><strong>Penerima:</strong> {{ $order->recipient_name }}</p>
        <p><strong>No. HP:</strong> {{ $order->phone_number }}</p>
        <p><strong>Alamat Lengkap:</strong><br> {{ $order->shipping_address }}</p>
        <p>
            <strong>Tujuan:</strong> {{ $order->country }}
            @if(strtolower($order->country) === 'indonesia')
                <span class="badge" style="background-color: #10b981;">LOKAL</span>
            @else
                <span class="badge" style="background-color: #f59e0b;">INTERNASIONAL</span>
            @endif
        </p>
        <hr style="border: 0; border-top: 1px solid #ddd;">
        <p><strong>Kurir:</strong> {{ $order->shipping_courier ?? 'Belum Dipilih' }}</p>
        <p><strong>Total Belanja:</strong> Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
    </div>
</body>
</html>