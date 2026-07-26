<?php

namespace App\Filament\Pages;

use App\Models\DonacionActivo;
use App\Models\MovimientoContable;
use App\Models\Persona;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class CertificadoDonante extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Certificado de donante';

    protected static ?string $title = 'Certificado de donante';

    protected static ?string $slug = 'certificado-donante';

    protected static string $view = 'filament.pages.certificado-donante';

    public static function canAccess(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'admin_general']);
    }

    public ?int $personaId = null;

    public ?int $anio = null;

    public function mount(): void
    {
        $this->anio = (int) now()->year;
        $this->form->fill([
            'personaId' => $this->personaId,
            'anio' => $this->anio,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('personaId')
                    ->label('Persona')
                    ->placeholder('Selecciona una persona…')
                    ->options(
                        Persona::query()
                            ->orderBy('nombres')
                            ->get()
                            ->mapWithKeys(fn (Persona $persona) => [$persona->id => $persona->nombre_completo])
                    )
                    ->searchable()
                    ->native(false)
                    ->live(),
                Select::make('anio')
                    ->label('Año')
                    ->options(
                        collect(range((int) now()->year, (int) now()->year - 5))
                            ->mapWithKeys(fn (int $anio) => [$anio => (string) $anio])
                    )
                    ->native(false)
                    ->live(),
            ]);
    }

    /**
     * @return array{persona: Persona, movimientos: \Illuminate\Support\Collection, totalEfectivo: float, donacionesActivos: \Illuminate\Support\Collection, totalActivos: float}|null
     */
    #[Computed]
    public function certificado(): ?array
    {
        if (! $this->personaId || ! $this->anio) {
            return null;
        }

        $persona = Persona::find($this->personaId);

        if (! $persona) {
            return null;
        }

        $movimientos = MovimientoContable::query()
            ->where('persona_id', $this->personaId)
            ->where('tipo', 'ingreso')
            ->whereYear('fecha', $this->anio)
            ->with('categoriaContable')
            ->orderBy('fecha')
            ->get();

        $donacionesActivos = DonacionActivo::query()
            ->where('persona_id', $this->personaId)
            ->whereYear('fecha', $this->anio)
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
