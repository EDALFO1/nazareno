<?php

namespace App\Filament\Resources\PuntoConexionResource\Pages;

use App\Filament\Resources\PuntoConexionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPuntoConexion extends EditRecord
{
    protected static string $resource = PuntoConexionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
