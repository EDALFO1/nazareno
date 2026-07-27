<?php

namespace App\Filament\Resources\CuentaPendienteResource\Pages;

use App\Filament\Resources\CuentaPendienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuentaPendiente extends EditRecord
{
    protected static string $resource = CuentaPendienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
