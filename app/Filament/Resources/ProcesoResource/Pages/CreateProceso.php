<?php

namespace App\Filament\Resources\ProcesoResource\Pages;

use App\Filament\Resources\ProcesoResource;
use App\Models\Proceso;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProceso extends CreateRecord
{
    protected static string $resource = ProcesoResource::class;

    protected ?int $cargarDesdeProcesoId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->cargarDesdeProcesoId = $data['cargar_desde_proceso_id'] ?? null;
        unset($data['cargar_desde_proceso_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->cargarDesdeProcesoId) {
            return;
        }

        $procesoAnterior = Proceso::find($this->cargarDesdeProcesoId);

        if (! $procesoAnterior) {
            return;
        }

        $terminados = $procesoAnterior->participantes()
            ->where('estado_participacion', 'terminado')
            ->get();

        foreach ($terminados as $participante) {
            $this->record->participantes()->create([
                'persona_id' => $participante->persona_id,
                'red_id' => $participante->red_id,
                'estado_participacion' => 'en_curso',
            ]);
        }

        Notification::make()
            ->title("Se cargaron {$terminados->count()} personas que terminaron el proceso anterior")
            ->success()
            ->send();
    }
}
