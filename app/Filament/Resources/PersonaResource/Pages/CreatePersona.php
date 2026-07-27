<?php

namespace App\Filament\Resources\PersonaResource\Pages;

use App\Filament\Resources\PersonaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePersona extends CreateRecord
{
    protected static string $resource = PersonaResource::class;

    protected function afterCreate(): void
    {
        // La casilla "autorizacion_confirmada" es required() y no se guarda
        // como columna de personas (dehydrated(false)): si llegamos aquí, la
        // validación ya obligó a marcarla. Dejamos la evidencia.
        $this->record->autorizacionesTratamientoDatos()->create([
            'canal' => 'registro_manual',
            'registrado_por_user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }
}
