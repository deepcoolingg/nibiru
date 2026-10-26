<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 6,
            'xl' => 6,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        $months = collect(range(1, 12))->mapWithKeys(fn (int $m): array => [
            $m => \Carbon\Carbon::create()->month($m)->translatedFormat('F'),
        ])->toArray();

        $currentYear = (int) date('Y');
        $years = collect(range(2026, 2030))->mapWithKeys(
            fn (int $y): array => [$y => (string) $y],
        )->toArray();

        return $schema->components([
            Select::make('month')
                ->label('Bulan')
                ->options($months)
                ->default((int) date('m')),
            Select::make('year')
                ->label('Tahun')
                ->options($years)
                ->default(($currentYear >= 2026 && $currentYear <= 2030) ? $currentYear : 2026),
        ]);
    }
}