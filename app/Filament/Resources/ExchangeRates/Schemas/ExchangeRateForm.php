<?php

namespace App\Filament\Resources\ExchangeRates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExchangeRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('currency_code')
                    ->required(),
                TextInput::make('rate_to_idr')
                    ->required()
                    ->numeric(),
            ]);
    }
}
