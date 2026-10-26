<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('discount_type')
                    ->required(),
                TextInput::make('discount_value')
                    ->required()
                    ->numeric(),
                TextInput::make('min_qty')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('max_uses')
                    ->numeric(),
                TextInput::make('used_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('valid_until'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
