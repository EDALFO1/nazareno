<?php

namespace App\Filament\Resources\PersonaResource\RelationManagers;

use App\Models\ProcesoParticipante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProcesosRelationManager extends RelationManager
{
    protected static string $relationship = 'procesoParticipaciones';

    protected static ?string $title = 'Procesos de formación';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('proceso_id')
                    ->label('Proceso')
                    ->relationship('proceso', 'nombre')
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('estado_participacion')
                    ->label('Estado')
                    ->options([
                        'en_curso' => 'En curso',
                        'terminado' => 'Terminado',
                        'retirado' => 'Retirado',
                    ])
                    ->default('en_curso')
                    ->required()
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('proceso_id')
            ->columns([
                Tables\Columns\TextColumn::make('proceso.tipoProceso.nombre')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('proceso.nombre')
                    ->label('Edición'),
                Tables\Columns\TextColumn::make('estado_participacion')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'en_curso' => 'warning',
                        'terminado' => 'success',
                        'retirado' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('asistencia')
                    ->label('Clases asistidas')
                    ->getStateUsing(function (ProcesoParticipante $record) {
                        $total = $record->proceso->tipoProceso->numero_sesiones ?? $record->proceso->sesiones()->count();
                        $asistidas = $record->persona->asistencias()
                            ->whereIn('sesion_proceso_id', $record->proceso->sesiones()->pluck('id'))
                            ->where('asistio', true)
                            ->count();

                        return "{$asistidas} / {$total}";
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
