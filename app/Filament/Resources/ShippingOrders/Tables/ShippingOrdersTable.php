<?php

namespace App\Filament\Resources\ShippingOrders\Tables;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingOrdersTable
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
                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Recipient')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->label('Destination')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Indonesia' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('shipping_courier')
                    ->label('Courier')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Tracking Number')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'warning',
                        'shipped' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'paid' => 'Paid',
                        'shipped' => 'Shipped',
                    ]),
            ])
            ->recordActions([
                Action::make('markShipped')
                    ->label('Mark Shipped')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (Order $record): bool => $record->status === 'paid')
                    ->form([
                        TextInput::make('tracking_number')
                            ->label('Tracking Number')
                            ->required()
                            ->maxLength(100)
                            ->default(fn (Order $record): ?string => $record->tracking_number),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update([
                            'tracking_number' => $data['tracking_number'],
                            'status' => 'shipped',
                        ]);
                    }),
                Action::make('markDelivered')
                    ->label('Mark Delivered')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->status === 'shipped')
                    ->action(function (Order $record): void {
                        $record->update([
                            'status' => 'completed',
                        ]);
                    }),
                Action::make('printResi')
                    ->label('Print Resi')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->action(function (Order $record) {
                        $pdf = Pdf::loadView('pdf.invoice', ['order' => $record]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "Resi-{$record->order_number}.pdf"
                        );
                    }),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
