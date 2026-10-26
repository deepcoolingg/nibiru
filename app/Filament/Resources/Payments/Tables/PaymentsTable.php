<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;

// IMPORT SAKTI YANG UDAH TERBUKTI JALAN DI ORDER
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid', 'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
            ])
            ->actions([
                // Pake Action Edit biasa, tapi bajunya kita ganti jadi tombol "Lihat Detail"
                Action::make('edit')
                    ->label('View Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Payment $record): string => route('filament.nibiru.resources.payments.edit', $record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc');
    }
}