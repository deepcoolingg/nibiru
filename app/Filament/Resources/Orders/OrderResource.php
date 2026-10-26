<?php

namespace App\Filament\Resources\Orders;

// CreateOrder nggak perlu di-use lagi karena udah kita hapus fiturnya
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    // Pakai Method biar nggak kena fatal error type mismatch di v5
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Transaksi';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    protected static ?string $recordTitleAttribute = 'order_number';

    // ========================================================
    // KUNCI UTAMA: Matiin tombol "New Order" di dashboard
    // ========================================================
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
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
            'index' => ListOrders::route('/'),
            // Rute 'create' udah gua hapus biar aman 100%
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}