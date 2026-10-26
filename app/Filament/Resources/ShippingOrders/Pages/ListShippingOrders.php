<?php

namespace App\Filament\Resources\ShippingOrders\Pages;

use App\Filament\Resources\ShippingOrders\ShippingOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListShippingOrders extends ListRecords
{
    protected static string $resource = ShippingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
