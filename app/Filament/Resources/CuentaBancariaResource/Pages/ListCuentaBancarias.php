<?php

namespace App\Filament\Resources\CuentaBancariaResource\Pages;

use App\Filament\Resources\CuentaBancariaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuentaBancarias extends ListRecords
{
    protected static string $resource = CuentaBancariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
