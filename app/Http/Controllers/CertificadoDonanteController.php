<?php

namespace App\Http\Controllers;

use App\Models\DonacionActivo;
use App\Models\MovimientoContable;
use App\Models\Persona;
use Illuminate\Http\Request;

class CertificadoDonanteController extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Certificado de donante';
        $personaId = $request->integer('persona_id') ?: null;
        $anio = $request->integer('anio') ?: (int) now()->year;

        $personas = Persona::orderBy('nombres')->get();
        $anios = collect(range((int) now()->year, (int) now()->year - 5));
        $certificado = $this->certificado($personaId, $anio);

        return view('modules.certificado_donante.index', compact('titulo', 'personas', 'anios', 'personaId', 'anio', 'certificado'));
    }

    /**
     * @return array{persona: Persona, movimientos: \Illuminate\Support\Collection, totalEfectivo: float, donacionesActivos: \Illuminate\Support\Collection, totalActivos: float}|null
     */
    private function certificado(?int $personaId, int $anio): ?array
    {
        if (! $personaId) {
            return null;
        }

        $persona = Persona::find($personaId);

        if (! $persona) {
            return null;
        }

        $movimientos = MovimientoContable::query()
            ->where('persona_id', $personaId)
            ->where('tipo', 'ingreso')
            ->whereYear('fecha', $anio)
            ->with('categoriaContable')
            ->orderBy('fecha')
            ->get();

        $donacionesActivos = DonacionActivo::query()
            ->where('persona_id', $personaId)
            ->whereYear('fecha', $anio)
            ->orderBy('fecha')
            ->get();

        return [
            'persona' => $persona,
            'movimientos' => $movimientos,
            'totalEfectivo' => (float) $movimientos->sum('monto'),
            'donacionesActivos' => $donacionesActivos,
            'totalActivos' => (float) $donacionesActivos->sum('valor_estimado'),
        ];
    }
}
