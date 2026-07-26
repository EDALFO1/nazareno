<?php

namespace App\Filament\Resources\MovimientoContableResource\Pages;

use App\Filament\Resources\MovimientoContableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovimientoContables extends ListRecords
{
    protected static string $resource = MovimientoContableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
