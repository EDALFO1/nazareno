<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\ArbolDiscipuladoService;
use App\Services\OrdenJerarquicoService;

#[Fillable([
    'nombres', 'apellidos', 'tipo_documento', 'numero_documento', 'telefono', 'direccion', 'correo', 'genero',
    'fecha_nacimiento', 'fecha_primera_visita', 'peticion_oracion', 'estado',
    'acudiente', 'telefono_acudiente', 'parentesco',
    'red_id', 'lider_id', 'user_id',
])]
class Persona extends Model
{
    public const TIPOS_DOCUMENTO = [
        'cedula_ciudadania' => 'Cédula de ciudadanía',
        'tarjeta_identidad' => 'Tarjeta de identidad',
        'registro_civil' => 'Registro civil',
        'cedula_extranjeria' => 'Cédula de extranjería',
        'permiso_proteccion_temporal' => 'Permiso por Protección Temporal (PPT)',
        'permiso_especial_permanencia' => 'Permiso Especial de Permanencia (PEP)',
        'pasaporte' => 'Pasaporte',
        'otro' => 'Otro',
    ];

    public const SIGLAS_DOCUMENTO = [
        'cedula_ciudadania' => 'CC',
        'tarjeta_identidad' => 'TI',
        'registro_civil' => 'RC',
        'cedula_extranjeria' => 'CE',
        'permiso_proteccion_temporal' => 'PPT',
        'permiso_especial_permanencia' => 'PEP',
        'pasaporte' => 'PA',
        'otro' => 'Doc.',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_primera_visita' => 'date',
        ];
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => "{$this->nombres} {$this->apellidos}");
    }

    /**
     * Documento formateado con su sigla (ej. "CC 1234567890"), para mostrar
     * en tablas y reportes. Null si la persona aún no tiene documento registrado.
     */
    protected function documento(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->numero_documento) {
                return null;
            }

            $sigla = self::SIGLAS_DOCUMENTO[$this->tipo_documento] ?? '';

            return trim("{$sigla} {$this->numero_documento}");
        });
    }

    /**
     * Líder principal = cabeza de una red (pertenece a una red y no reporta a nadie).
     */
    protected function esLiderPrincipal(): Attribute
    {
        return Attribute::get(fn () => $this->red_id !== null && $this->lider_id === null);
    }

    /**
     * Línea de liderazgo: 0 = líder principal, 1 = primera línea (reporta
     * directo al principal y a su vez lidera gente), 2 = segunda línea, etc.
     * Null si la persona no es líder de nadie (no lleva marca de línea).
     */
    protected function lineaLiderazgo(): Attribute
    {
        return Attribute::get(function () {
            if ($this->red_id === null) {
                return null;
            }

            if ($this->lider_id === null) {
                return 0;
            }

            if (! $this->discipulos()->exists()) {
                return null;
            }

            $profundidad = 0;
            $actual = $this;

            while ($actual !== null && $actual->lider_id !== null) {
                $profundidad++;
                $actual = $actual->lider;
            }

            return $profundidad;
        });
    }

    /**
     * Etiqueta legible de la línea de liderazgo (solo para líderes que no son
     * el principal — ese ya se distingue con su propia marca de estrella).
     */
    protected function etiquetaLinea(): Attribute
    {
        return Attribute::get(function () {
            $linea = $this->linea_liderazgo;

            return $linea !== null && $linea > 0 ? "{$linea}ª línea" : null;
        });
    }

    public function red(): BelongsTo
    {
        return $this->belongsTo(Red::class);
    }

    public function lider(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'lider_id');
    }

    public function discipulos(): HasMany
    {
        return $this->hasMany(Persona::class, 'lider_id');
    }

    public function movimientosContables(): HasMany
    {
        return $this->hasMany(MovimientoContable::class);
    }

    public function donacionesActivos(): HasMany
    {
        return $this->hasMany(DonacionActivo::class);
    }

    public function cuentasPendientes(): HasMany
    {
        return $this->hasMany(CuentaPendiente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notasSeguimiento(): HasMany
    {
        return $this->hasMany(NotaSeguimiento::class)->latest('fecha');
    }

    public function puntosConexionLiderados(): HasMany
    {
        return $this->hasMany(PuntoConexion::class, 'lider_id');
    }

    public function puntosConexion(): BelongsToMany
    {
        return $this->belongsToMany(PuntoConexion::class, 'punto_conexion_persona')
            ->withPivot('fecha_ingreso')
            ->withTimestamps();
    }

    public function procesoParticipaciones(): HasMany
    {
        return $this->hasMany(ProcesoParticipante::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    public function asistenciasPuntoConexion(): HasMany
    {
        return $this->hasMany(AsistenciaPuntoConexion::class);
    }

    public function autorizacionesTratamientoDatos(): HasMany
    {
        return $this->hasMany(AutorizacionTratamientoDatos::class);
    }

    protected function tieneAutorizacionDatos(): Attribute
    {
        return Attribute::get(fn () => $this->autorizacionesTratamientoDatos()->exists());
    }

    /**
     * IDs de todos los descendientes en el árbol de discipulado (cualquier profundidad).
     * Cálculo real en App\Services\ArbolDiscipuladoService.
     *
     * @return array<int>
     */
    public function descendientesIds(): array
    {
        return app(ArbolDiscipuladoService::class)->descendientesIds($this);
    }

    /**
     * IDs de la persona más todos sus descendientes — útil para scoping de acceso.
     *
     * @return array<int>
     */
    public function subarbolIds(): array
    {
        return app(ArbolDiscipuladoService::class)->subarbolIds($this);
    }

    /**
     * IDs de todas las personas en orden jerárquico: agrupadas por red (en
     * orden alfabético de red), y dentro de cada red, primero el líder
     * principal, luego sus líderes de primera línea, luego los de segunda,
     * etc. (recorrido en profundidad del árbol de discipulado). Los hermanos
     * de un mismo nivel quedan en orden alfabético entre sí. Las personas sin
     * red asignada quedan al final, en orden alfabético.
     *
     * Cálculo real en App\Services\OrdenJerarquicoService.
     *
     * @return array<int>
     */
    public static function idsEnOrdenJerarquico(): array
    {
        return app(OrdenJerarquicoService::class)->calcular();
    }

    public static function rules($id = null): array
    {
        return [
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['nullable', 'in:'.implode(',', array_keys(self::TIPOS_DOCUMENTO))],
            'numero_documento' => ['nullable', 'string', 'max:255', 'unique:personas,numero_documento,'.$id],
            'telefono' => ['nullable', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'genero' => ['nullable', 'in:masculino,femenino'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'fecha_primera_visita' => ['nullable', 'date'],
            'peticion_oracion' => ['nullable', 'string'],
            'acudiente' => ['nullable', 'string', 'max:255'],
            'telefono_acudiente' => ['nullable', 'string', 'max:255'],
            'parentesco' => ['nullable', 'in:padre,madre,conyuge,abuelo_a,tio_a,hermano_a,tutor_legal,otro'],
            'estado' => ['required', 'in:nuevo,en_seguimiento,en_red,inactivo'],
            'red_id' => ['nullable', 'exists:redes,id'],
            'lider_id' => ['nullable', 'exists:personas,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
