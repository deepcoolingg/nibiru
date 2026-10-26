<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Schemas\Schema;

// Ingat kasta v5: Section di Schemas, Inputan di Forms!
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pembayaran')
                    ->description('Data ini ditarik otomatis dari Payment Gateway dan tidak bisa diubah manual.')
                    ->schema([
                        Placeholder::make('order_number_display')
                            ->label('No. Order')
                            ->content(fn ($record) => $record?->order?->order_number ?? '-'),
                        Placeholder::make('recipient_name_display')
                            ->label('Nama Penerima')
                            ->content(fn ($record) => $record?->order?->recipient_name ?? '-'),
                        TextInput::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->disabled(),
                        TextInput::make('gateway')
                            ->label('Payment Gateway')
                            ->disabled(),
                        TextInput::make('gateway_transaction_id')
                            ->label('ID Transaksi Gateway')
                            ->disabled(),
                        TextInput::make('amount')
                            ->label('Nominal Dibayar')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('status')
                            ->label('Status Pembayaran')
                            ->disabled(),
                        Placeholder::make('order_grand_total_display')
                            ->label('Grand Total Order')
                            ->content(fn ($record) => 'Rp ' . number_format($record?->order?->grand_total ?? 0, 0, ',', '.')),
                    ])->columns(2),
            ]);
    }
}