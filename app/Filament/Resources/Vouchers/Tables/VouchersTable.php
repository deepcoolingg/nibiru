<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->weight('bold')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('discount_type')
                    ->badge()
                    ->color('info')
                    ->label('Type'),
                TextColumn::make('discount_value')
                    ->label('Discount Value')
                    ->formatStateUsing(fn (int $state): string => "{$state}%")
                    ->sortable(),
                TextColumn::make('min_qty')
                    ->label('Min. Qty')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_uses')
                    ->label('Max Uses')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('used_count')
                    ->label('Used')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (int $state, $record): string => "{$state} / {$record->max_uses}"),
                TextColumn::make('valid_until')
                    ->label('Expiry')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
