<?php

namespace App\Filament\Resources\ProductDetailSettings;

use App\Filament\Resources\ProductDetailSettings\Pages\CreateProductDetailSetting;
use App\Filament\Resources\ProductDetailSettings\Pages\EditProductDetailSetting;
use App\Filament\Resources\ProductDetailSettings\Pages\ListProductDetailSettings;
use App\Filament\Resources\ProductDetailSettings\Schemas\ProductDetailSettingForm;
use App\Filament\Resources\ProductDetailSettings\Tables\ProductDetailSettingsTable;
use App\Models\ProductDetailSetting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductDetailSettingResource extends Resource
{
    protected static ?string $model = ProductDetailSetting::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationLabel(): string
    {
        return 'Product Detail Global';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Content Management';
    }

    public static function canCreate(): bool
    {
        return ProductDetailSetting::query()->count() === 0;
    }

    public static function form(Schema $schema): Schema
    {
        return ProductDetailSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductDetailSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductDetailSettings::route('/'),
            'create' => CreateProductDetailSetting::route('/create'),
            'edit' => EditProductDetailSetting::route('/{record}/edit'),
        ];
    }
}
