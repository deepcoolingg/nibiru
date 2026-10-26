<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('phone_number')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->hidden(fn (string $operation) => $operation === 'edit'),
                Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'Customer',
                    ])
                    ->required(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
