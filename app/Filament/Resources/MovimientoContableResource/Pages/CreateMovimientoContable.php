<?php

namespace App\Filament\Resources\MovimientoContableResource\Pages;

use App\Filament\Resources\MovimientoContableResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMovimientoContable extends CreateRecord
{
    protected static string $resource = MovimientoContableResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['registrado_por_id'] = Auth::id();

        return $data;
    }
}
