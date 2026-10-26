<?php

namespace App\Filament\Resources\ProductDetailSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductDetailSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->limit(70)
                    ->wrap(),
                TextColumn::make('description_highlights')
                    ->label('Highlights')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? implode(' | ', $state) : '')
                    ->limit(80),
                ImageColumn::make('size_fit_image')
                    ->label('Size & Fit')
                    ->square()
                    ->width(60)
                    ->height(60),
                TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
