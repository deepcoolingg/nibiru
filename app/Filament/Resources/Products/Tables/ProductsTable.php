<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->circular()
                    ->width(50)
                    ->height(50),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('price')
                    ->label('Sell Price')
                    ->money('IDR')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('cost_price')
                    ->label('Cost Price')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state < 20 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->suffix(' kg'),
                TextColumn::make('color_name')
                    ->label('Color')
                    ->badge()
                    ->sortable(),
                TextColumn::make('color_hex')
                    ->label('Hex')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status'),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
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
