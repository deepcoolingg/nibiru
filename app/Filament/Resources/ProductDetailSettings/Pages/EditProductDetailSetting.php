<?php

namespace App\Filament\Resources\ProductDetailSettings\Pages;

use App\Filament\Resources\ProductDetailSettings\ProductDetailSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditProductDetailSetting extends EditRecord
{
    protected static string $resource = ProductDetailSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
