<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Information')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->columnSpan(2)
                            ->placeholder('ex: Hijab Katun Premium'),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpan(2)
                            ->helperText('Kosongkan untuk memakai default Description global.'),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique('products', 'sku', ignoreRecord: true)
                            ->placeholder('ex: HIJAB-001'),
                    ]),

                Section::make('Media')
                    ->collapsible()
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->directory('products')
                            ->columnSpanFull()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1'),
                        FileUpload::make('size_fit_image')
                            ->label('Size & Fit Image')
                            ->image()
                            ->directory('products/size-fit')
                            ->helperText('Kosongkan untuk memakai default Size & Fit image global.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Color')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('color_name')
                            ->label('Color Name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('ex: Blackberry'),
                        TextInput::make('color_hex')
                            ->label('Hex Code')
                            ->required()
                            ->maxLength(7)
                            ->placeholder('#1F2937')
                            ->rule('regex:/^#[A-Fa-f0-9]{6}$/'),
                    ]),

                Section::make('Pricing')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('price')
                            ->label('Selling Price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->hint('Price for customers'),
                        TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->hint('Your cost'),
                    ]),

                Section::make('Inventory')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('stock')
                            ->label('Stock Quantity')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('weight')
                            ->label('Weight (kg)')
                            ->required()
                            ->numeric()
                            ->step(0.1),
                    ]),

                Section::make('Status')
                    ->collapsible()
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->hint('Disable to hide from store'),
                    ]),
            ]);
    }
}
