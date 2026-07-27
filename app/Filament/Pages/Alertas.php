<?php

namespace App\Filament\Pages;

use App\Services\AlertasService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class Alertas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'Alertas';

    protected static ?string $title = 'Alertas';

    protected static ?string $slug = 'alertas';

    protected static ?int $navigationSort = -10;

    protected static string $view = 'filament.pages.alertas';

    protected function alcanceIds(): ?array
    {
        return Auth::user()->alcancePersonaIds();
    }

    #[Computed]
    public function personasSinRetomar(): Collection
    {
        return app(AlertasService::class)->personasSinRetomar($this->alcanceIds());
    }

    #[Computed]
    public function puntosSinReportar(): Collection
    {
        return app(AlertasService::class)->puntosSinReportar($this->alcanceIds());
    }

    #[Computed]
    public function cumpleanosDelMes(): Collection
    {
        return app(AlertasService::class)->cumpleanosDelMes($this->alcanceIds());
    }

    public static function getNavigationBadge(): ?string
    {
        $alcanceIds = Auth::user()->alcancePersonaIds();
        $servicio = app(AlertasService::class);

        $total = $servicio->personasSinRetomar($alcanceIds)->count()
            + $servicio->puntosSinReportar($alcanceIds)->count();

        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
