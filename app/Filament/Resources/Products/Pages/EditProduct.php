<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $previousStock = (int) ($this->record->stock ?? 0);
        $nextStock = (int) ($data['stock'] ?? $previousStock);

        if ($previousStock <= 0 && $nextStock > 0) {
            $data['restocked_at'] = now();
            return $data;
        }

        if ($nextStock <= 0) {
            $data['restocked_at'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
