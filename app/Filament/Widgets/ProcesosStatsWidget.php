<?php

namespace App\Filament\Widgets;

use App\Models\TipoProceso;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProcesosStatsWidget extends BaseWidget
{
    protected static ?string $heading = 'Pipeline de procesos de formación';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'admin_general']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TipoProceso::query()
                    ->orderBy('orden')
                    ->withCount(['procesos as ediciones_count'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Proceso')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ediciones_count')
                    ->label('Ediciones creadas')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('participantes_en_curso')
                    ->label('Personas en curso')
                    ->alignCenter()
                    ->getStateUsing(fn (TipoProceso $record) => $record->procesos()
                        ->withCount(['participantes' => fn (Builder $q) => $q->where('estado_participacion', 'en_curso')])
                        ->get()
                        ->sum('participantes_count')),
                Tables\Columns\TextColumn::make('participantes_terminados')
                    ->label('Terminaron')
                    ->alignCenter()
                    ->getStateUsing(fn (TipoProceso $record) => $record->procesos()
                        ->withCount(['participantes' => fn (Builder $q) => $q->where('estado_participacion', 'terminado')])
                        ->get()
                        ->sum('participantes_count')),
                Tables\Columns\TextColumn::make('participantes_retirados')
                    ->label('Retirados')
                    ->alignCenter()
                    ->getStateUsing(fn (TipoProceso $record) => $record->procesos()
                        ->withCount(['participantes' => fn (Builder $q) => $q->where('estado_participacion', 'retirado')])
                        ->get()
                        ->sum('participantes_count')),
            ])
            ->paginated(false);
    }
}
