<?php

namespace App\Filament\Widgets;

use App\Models\Red;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RedesStatsWidget extends BaseWidget
{
    protected static ?string $heading = 'Resumen por red';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'admin_general']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Red::query()->withCount([
                    'personas',
                    'personas as lideres_count' => fn (Builder $query) => $query->whereHas('discipulos'),
                    'personas as nuevos_count' => fn (Builder $query) => $query->where('estado', 'nuevo'),
                    'personas as en_seguimiento_count' => fn (Builder $query) => $query->where('estado', 'en_seguimiento'),
                    'personas as en_red_count' => fn (Builder $query) => $query->where('estado', 'en_red'),
                    'puntosConexion',
                ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Red')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('personas_count')
                    ->label('Personas')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('lideres_count')
                    ->label('Líderes')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('puntos_conexion_count')
                    ->label('Puntos de conexión')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('nuevos_count')
                    ->label('Nuevos')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('en_seguimiento_count')
                    ->label('En seguimiento')
                    ->badge()
                    ->color('warning')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('en_red_count')
                    ->label('En red')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
