<?php

namespace App\Filament\Resources\Payments;

// Balikin import EditPayment karena rutenya bakal kita pake buat nampilin form View Only
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Schemas\PaymentForm;
use App\Filament\Resources\Payments\Tables\PaymentsTable;
use App\Models\Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Transaksi';
    }

    public static function getNavigationSort(): ?int
    {
        return 12;
    }

    protected static ?string $recordTitleAttribute = 'gateway_transaction_id';

    // ========================================================
    // CUMA KUNCI NEW PAYMENT (Tombol Kuning Lenyap)
    // ========================================================
    public static function canCreate(): bool
    {
        return false;
    }

    // Fungsi canEdit() sengaja kita hapus dari sini!

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
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
            'index' => ListPayments::route('/'),
            // Balikin rute edit buat dipake sebagai halaman "Lihat Detail"
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }
}