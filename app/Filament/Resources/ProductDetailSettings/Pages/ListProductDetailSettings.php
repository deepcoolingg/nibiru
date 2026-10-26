<?php

namespace App\Filament\Resources\ProductDetailSettings\Pages;

use App\Filament\Resources\ProductDetailSettings\ProductDetailSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductDetailSettings extends ListRecords
{
    protected static string $resource = ProductDetailSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => static::getResource()::canCreate()),
        ];
    }
}
