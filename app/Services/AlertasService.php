<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\PuntoConexion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Alertas automáticas que el panel muestra para que nadie se le pierda de
 * vista a la iglesia: personas nuevas/en seguimiento sin retomar contacto,
 * puntos de conexión que llevan mucho sin reportar reunión, y cumpleaños
 * del mes.
 */
class AlertasService
{
    /**
     * Personas en estado "nuevo" o "en seguimiento" (es decir, aún no
     * integradas a una red) cuya última nota de seguimiento (o, si no
     * tienen ninguna, su primera visita) tiene $dias o más de antigüedad.
     *
     * @param  array<int>|null  $alcanceIds  Restringe a estos ids de persona (scoping por rol), o null para ver todas.
     */
    public function personasSinRetomar(?array $alcanceIds = null, int $dias = 30): Collection
    {
        $query = Persona::query()
            ->whereIn('estado', ['nuevo', 'en_seguimiento'])
            ->withMax('notasSeguimiento as ultima_nota_fecha', 'fecha');

        if ($alcanceIds !== null) {
            $query->whereIn('id', $alcanceIds);
        }

        return $query->get()
            ->map(function (Persona $persona) {
                $referencia = $persona->ultima_nota_fecha
                    ? Carbon::parse($persona->ultima_nota_fecha)
                    : ($persona->fecha_primera_visita ?? $persona->created_at);

                $persona->dias_sin_retomar = $referencia ? (int) $referencia->diffInDays(now()) : null;

                return $persona;
            })
            ->filter(fn (Persona $persona) => $persona->dias_sin_retomar === null || $persona->dias_sin_retomar >= $dias)
            ->sortByDesc(fn (Persona $persona) => $persona->dias_sin_retomar ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * Puntos de conexión activos cuya última reunión registrada (o, si nunca
     * han registrado ninguna, siempre) tiene $dias o más de antigüedad.
     *
     * @param  array<int>|null  $liderIds  Restringe a puntos cuyo líder esté en estos ids, o null para ver todos.
     */
    public function puntosSinReportar(?array $liderIds = null, int $dias = 14): Collection
    {
        $query = PuntoConexion::query()
            ->where('activo', true)
            ->with(['lider', 'red']);

        if ($liderIds !== null) {
            $query->whereIn('lider_id', $liderIds);
        }

        return $query->get()
            ->map(function (PuntoConexion $punto) {
                $ultima = $punto->sesiones()->max('fecha');

                $punto->ultima_reunion_fecha = $ultima;
                $punto->dias_sin_reportar = $ultima ? (int) Carbon::parse($ultima)->diffInDays(now()) : null;

                return $punto;
            })
            ->filter(fn (PuntoConexion $punto) => $punto->dias_sin_reportar === null || $punto->dias_sin_reportar >= $dias)
            ->sortByDesc(fn (PuntoConexion $punto) => $punto->dias_sin_reportar ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * Personas que cumplen años este mes, ordenadas por día.
     *
     * @param  array<int>|null  $alcanceIds
     */
    public function cumpleanosDelMes(?array $alcanceIds = null): Collection
    {
        $query = Persona::query()
            ->whereNotNull('fecha_nacimiento')
            ->whereMonth('fecha_nacimiento', now()->month);

        if ($alcanceIds !== null) {
            $query->whereIn('id', $alcanceIds);
        }

        return $query->get()->sortBy(fn (Persona $persona) => $persona->fecha_nacimiento->day)->values();
    }
}
