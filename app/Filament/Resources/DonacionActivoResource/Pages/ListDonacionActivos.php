<?php

namespace App\Filament\Resources\DonacionActivoResource\Pages;

use App\Filament\Resources\DonacionActivoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDonacionActivos extends ListRecords
{
    protected static string $resource = DonacionActivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
