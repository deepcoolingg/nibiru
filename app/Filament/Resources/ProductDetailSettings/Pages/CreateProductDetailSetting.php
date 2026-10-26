<?php

namespace App\Filament\Resources\ProductDetailSettings\Pages;

use App\Filament\Resources\ProductDetailSettings\ProductDetailSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductDetailSetting extends CreateRecord
{
    protected static string $resource = ProductDetailSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
