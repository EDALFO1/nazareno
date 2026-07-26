<?php

namespace App\Filament\Pages;

use App\Models\Persona;
use App\Models\PuntoConexion;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class EstructuraRed extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = 'Personas y Redes';

    protected static ?string $navigationLabel = 'Estructura de red';

    protected static ?string $title = 'Estructura de red';

    protected static ?string $slug = 'estructura-red';

    protected static string $view = 'filament.pages.estructura-red';

    #[Url(as: 'lider')]
    public ?int $liderId = null;

    public function mount(): void
    {
        $alcanceIds = $this->alcanceIds();

        if ($alcanceIds !== null) {
            // Líder de red: entra directo a ver su propia rama, sin tener que
            // elegirse a sí mismo de una lista.
            $this->liderId = Auth::user()->persona?->id;
        }

        $this->form->fill(['liderId' => $this->liderId]);
    }

    /**
     * Null si el usuario ve toda la red (admins); si no, los IDs a los que
     * está restringido (él mismo + su subárbol de discipulado).
     *
     * @return array<int>|null
     */
    protected function alcanceIds(): ?array
    {
        return Auth::user()->alcancePersonaIds();
    }

    /**
     * True si el usuario actual es un líder de red (solo puede ver su propia
     * rama, sin selector). Usado desde la vista.
     */
    public function esVistaPropia(): bool
    {
        return $this->alcanceIds() !== null;
    }

    public function form(Form $form): Form
    {
        // Un líder de red solo puede ver su propia rama: no tiene sentido
        // mostrarle un selector donde la única opción sería su propio nombre.
        if ($this->alcanceIds() !== null) {
            return $form->schema([]);
        }

        return $form
            ->schema([
                Select::make('liderId')
                    ->label('Líder')
                    ->placeholder('Selecciona un líder…')
                    ->options($this->opcionesLideres())
                    ->searchable()
                    ->native(false)
                    ->live(),
            ]);
    }

    /**
     * Cualquier persona que sea líder (tiene al menos un discípulo directo) o
     * que sea líder principal de una red (aunque todavía no tenga discípulos),
     * visible para el usuario actual. Elegir cualquiera de ellos muestra solo
     * su propia rama hacia abajo, sin mezclarla con el resto de la red.
     *
     * @return array<int, string>
     */
    protected function opcionesLideres(): array
    {
        $idsConDiscipulos = Persona::query()->whereNotNull('lider_id')->pluck('lider_id')->unique();

        $query = Persona::query()
            ->where(function (Builder $query) use ($idsConDiscipulos) {
                $query->whereIn('id', $idsConDiscipulos)
                    ->orWhere(fn (Builder $q) => $q->whereNotNull('red_id')->whereNull('lider_id'));
            })
            ->with('red')
            ->orderBy('nombres');

        $alcanceIds = $this->alcanceIds();

        if ($alcanceIds !== null) {
            $query->whereIn('id', $alcanceIds);
        }

        return $query->get()
            ->mapWithKeys(fn (Persona $persona) => [
                $persona->id => "{$persona->nombre_completo} — {$persona->red?->nombre}",
            ])
            ->all();
    }

    /**
     * Árbol de discipulado, puntos de conexión y resumen numérico de la rama
     * completa (el líder elegido + todos sus descendientes, a cualquier
     * profundidad), sin importar en qué nivel de la red esté ese líder.
     *
     * @return array{lider: Persona, arbol: array, puntos: \Illuminate\Support\Collection, resumen: array{personas: int, lideres: int, puntos: int}}|null
     */
    #[Computed]
    public function estructura(): ?array
    {
        if (! $this->liderId) {
            return null;
        }

        $alcanceIds = $this->alcanceIds();

        if ($alcanceIds !== null && ! in_array($this->liderId, $alcanceIds, true)) {
            // Un líder de red no puede ver la rama de nadie fuera de su propio subárbol,
            // aunque manipule el parámetro ?lider= de la URL.
            return null;
        }

        $lider = Persona::with('red')->find($this->liderId);

        if (! $lider) {
            return null;
        }

        $ids = $lider->subarbolIds();

        $personas = Persona::whereIn('id', $ids)->orderBy('nombres')->get();

        $porLider = $personas->groupBy('lider_id');

        $construir = function (Persona $persona) use (&$construir, $porLider) {
            return [
                'persona' => $persona,
                'hijos' => ($porLider->get($persona->id) ?? collect())
                    ->map($construir)
                    ->values()
                    ->all(),
            ];
        };

        $idsConDiscipulos = $personas->pluck('lider_id')->filter()->unique();
        $totalLideres = $idsConDiscipulos->count() + ($idsConDiscipulos->contains($lider->id) ? 0 : 1);

        $puntos = PuntoConexion::whereIn('lider_id', $ids)
            ->with(['lider', 'anfitrion'])
            ->orderBy('nombre')
            ->get();

        return [
            'lider' => $lider,
            'arbol' => $construir($lider),
            'puntos' => $puntos,
            'resumen' => [
                'personas' => $personas->count(),
                'lideres' => $totalLideres,
                'puntos' => $puntos->count(),
            ],
        ];
    }
}
