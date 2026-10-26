<?php

namespace App\Filament\Resources\ProductDetailSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductDetailSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('description')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Repeater::make('description_highlights')
                    ->label('Description Highlights')
                    ->simple(
                        TextInput::make('highlight')
                            ->required()
                            ->maxLength(255)
                    )
                    ->defaultItems(3)
                    ->minItems(1)
                    ->columnSpanFull(),
                FileUpload::make('size_fit_image')
                    ->label('Size & Fit Image')
                    ->image()
                    ->directory('products/size-fit')
                    ->columnSpanFull(),
            ]);
    }
}
