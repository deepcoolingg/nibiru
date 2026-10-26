<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;

// INI YANG BENER BRE! (Balik ke punya lu yang udah sukses kemaren)
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.phone_number')
                    ->label('No. HP User')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Recipient')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('country')
                    ->label('Destination')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Indonesia' => 'success',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('voucher.code')
                    ->label('Voucher Code')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shipping_cost')
                    ->label('Ongkir')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'shipped' => 'info',
                        'completed' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            // UDAH BENER PAKE actions() BIAR NONGOL DI KANAN
            ->actions([
                // Kita pake Action langsung dari use di atas
                Action::make('edit')
                    ->label('View Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Order $record): string => route('filament.nibiru.resources.orders.edit', $record)),

                Action::make('print')
                    ->label('Print Resi')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (Order $record) {
                        $pdf = Pdf::loadView('pdf.invoice', ['order' => $record]);
                        return response()->streamDownload(
                            fn () => print($pdf->output()), 
                            "Resi-{$record->order_number}.pdf"
                        );
                    }),
            ])
            // UDAH BENER PAKE bulkActions() BIAR BISA DITANDAI MASSAL
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc');
    }
}