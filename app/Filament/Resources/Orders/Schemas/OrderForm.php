<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Product;
use Filament\Schemas\Schema;

// INI KUNCINYA BRE! (Dipisah sesuai rumah aslinya masing-masing)
use Filament\Schemas\Components\Section; // Layout ada di Schemas
use Filament\Forms\Components\TextInput; // Inputan ada di Forms
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('1. Informasi Pelanggan (Dari Frontend)')
                    ->description('Data ini diisi otomatis oleh pelanggan via API dan tidak dapat diubah.')
                    ->schema([
                        TextInput::make('order_number')->label('No. Order')->disabled(),
                        TextInput::make('recipient_name')->label('Nama Penerima')->disabled(),
                        TextInput::make('phone_number')->label('No. HP')->disabled(),
                        Placeholder::make('user_phone_number')
                            ->label('No. HP User')
                            ->content(fn ($record): string => $record?->user?->phone_number ?? '-'),
                        Textarea::make('shipping_address')->label('Alamat Lengkap')->columnSpanFull()->disabled(),
                        TextInput::make('country')->label('Negara Tujuan')->disabled(),
                    ])->columns(2),

                Section::make('2. Update Pengiriman (Akses Admin)')
                    ->description('Kurir otomatis: Indonesia → J&T Express, Luar Negeri → DHL Express.')
                    ->schema([
                        TextInput::make('shipping_courier')
                            ->label('Kurir Pengiriman')
                            ->disabled()
                            ->hint('Otomatis berdasarkan negara'),
                        TextInput::make('tracking_number')
                            ->label('Nomor Resi')
                            ->placeholder('Masukkan nomor resi di sini'),
                        Select::make('status')
                            ->label('Status Pesanan')
                            ->options([
                                'pending' => 'Pending (Belum Bayar)',
                                'paid' => 'Paid (Menunggu Dikirim)',
                                'shipped' => 'Shipped (Sedang Dikirim)',
                                'completed' => 'Completed (Selesai)',
                                'canceled' => 'Canceled (Batal)',
                            ])
                            ->required(),
                    ])->columns(3),

                Section::make('3. Daftar Produk yang Dipesan')
                    ->schema([
                        Repeater::make('orderItems')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Jilbab')
                                    ->options(Product::query()->pluck('name', 'id'))
                                    ->disabled(),
                                TextInput::make('qty')
                                    ->label('Jumlah')
                                    ->disabled(),
                                TextInput::make('price_at_checkout')
                                    ->label('Harga Beli')
                                    ->prefix('Rp')
                                    ->disabled(),
                            ])
                            ->columns(3)
                            ->disableItemCreation()
                            ->disableItemDeletion()
                    ]),

                Section::make('4. Rincian Pembayaran')
                    ->schema([
                        Placeholder::make('subtotal_display')
                            ->label('Subtotal Produk')
                            ->content(fn ($record) => 'Rp ' . number_format($record?->subtotal ?? 0, 0, ',', '.')),
                        Placeholder::make('shipping_cost_display')
                            ->label('Ongkos Kirim')
                            ->content(fn ($record) => 'Rp ' . number_format($record?->shipping_cost ?? 0, 0, ',', '.')),
                        Placeholder::make('qty_discount_display')
                            ->label('Diskon Qty (5% jika ≥ 2 item)')
                            ->content(fn ($record) => '- Rp ' . number_format($record?->qty_discount_amount ?? 0, 0, ',', '.')),
                        Placeholder::make('voucher_discount_display')
                            ->label('Diskon Voucher')
                            ->content(fn ($record) => '- Rp ' . number_format($record?->voucher_discount_amount ?? 0, 0, ',', '.')),
                        Placeholder::make('grand_total_display')
                            ->label('Grand Total')
                            ->content(fn ($record) => 'Rp ' . number_format($record?->grand_total ?? 0, 0, ',', '.')),
                    ])->columns(2),
            ]);
    }
}