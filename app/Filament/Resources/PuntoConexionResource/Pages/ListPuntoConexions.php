<?php

namespace App\Filament\Resources\PuntoConexionResource\Pages;

use App\Filament\Resources\PuntoConexionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPuntoConexions extends ListRecords
{
    protected static string $resource = PuntoConexionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
