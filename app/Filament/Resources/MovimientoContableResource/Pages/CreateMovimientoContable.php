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

    /**
     * Normalmente "Crear" redirige al listado. Aquí es común registrar varios
     * movimientos seguidos (ofrendas de un mismo culto, por ejemplo), así que
     * al guardar vuelve a este mismo formulario, ya en blanco, listo para el
     * siguiente registro.
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create');
    }
}
