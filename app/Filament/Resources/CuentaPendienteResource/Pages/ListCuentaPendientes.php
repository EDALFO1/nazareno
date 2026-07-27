<?php

namespace App\Filament\Resources\CuentaPendienteResource\Pages;

use App\Filament\Resources\CuentaPendienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuentaPendientes extends ListRecords
{
    protected static string $resource = CuentaPendienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
