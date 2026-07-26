<?php

namespace App\Filament\Resources\MovimientoContableResource\Pages;

use App\Filament\Resources\MovimientoContableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMovimientoContable extends EditRecord
{
    protected static string $resource = MovimientoContableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
