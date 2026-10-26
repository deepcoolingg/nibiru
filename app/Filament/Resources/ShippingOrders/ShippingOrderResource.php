<?php

namespace App\Filament\Resources\ShippingOrders;

use App\Filament\Resources\ShippingOrders\Pages\ListShippingOrders;
use App\Filament\Resources\ShippingOrders\Tables\ShippingOrdersTable;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShippingOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-truck';
    }

    public static function getNavigationLabel(): string
    {
        return 'Shipping';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Transaksi';
    }

    public static function getNavigationSort(): ?int
    {
        return 11;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ShippingOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShippingOrders::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['paid', 'shipped'])
            ->whereHas('payments', function (Builder $query): void {
                $query->whereIn('status', ['completed', 'paid']);
            });
    }
}
