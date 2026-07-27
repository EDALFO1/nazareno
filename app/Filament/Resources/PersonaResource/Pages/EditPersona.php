<?php

namespace App\Filament\Resources\PersonaResource\Pages;

use App\Filament\Resources\PersonaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPersona extends EditRecord
{
    protected static string $resource = PersonaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // La casilla de autorización solo aparece en el formulario cuando la
        // persona todavía no tenía evidencia registrada; si aparece, es
        // required() y ya se marcó para poder guardar.
        if ($this->record->autorizacionesTratamientoDatos()->doesntExist()) {
            $this->record->autorizacionesTratamientoDatos()->create([
                'canal' => 'registro_manual',
                'registrado_por_user_id' => Auth::id(),
                'ip_address' => request()->ip(),
            ]);
        }
    }
}
