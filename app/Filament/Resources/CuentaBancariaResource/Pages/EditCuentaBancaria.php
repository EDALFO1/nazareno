<?php

namespace App\Filament\Resources\CuentaBancariaResource\Pages;

use App\Filament\Resources\CuentaBancariaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuentaBancaria extends EditRecord
{
    protected static string $resource = CuentaBancariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
