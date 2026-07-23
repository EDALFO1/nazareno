<?php

namespace App\Filament\Resources\ProcesoResource\RelationManagers;

use App\Models\Asistencia;
use App\Models\SesionProceso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SesionesRelationManager extends RelationManager
{
    protected static string $relationship = 'sesiones';

    protected static ?string $title = 'Sesiones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero_sesion')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('nombre')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('fecha')
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_sesion')
            ->defaultSort('numero_sesion')
            ->columns([
                Tables\Columns\TextColumn::make('numero_sesion')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre'),
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('asistencias_count')
                    ->label('Asistieron')
                    ->counts(['asistencias' => fn (Builder $query) => $query->where('asistio', true)]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('generarSesiones')
                    ->label('Generar sesiones del tipo de proceso')
                    ->icon('heroicon-o-bolt')
                    ->requiresConfirmation()
                    ->action(function () {
                        $proceso = $this->getOwnerRecord();
                        $total = $proceso->tipoProceso->numero_sesiones ?? 0;
                        $existentes = $proceso->sesiones()->pluck('numero_sesion')->all();

                        for ($i = 1; $i <= $total; $i++) {
                            if (! in_array($i, $existentes, true)) {
                                $proceso->sesiones()->create(['numero_sesion' => $i]);
                            }
                        }
                    })
                    ->visible(fn () => filled($this->getOwnerRecord()->tipoProceso->numero_sesiones)),
            ])
            ->actions([
                Tables\Actions\Action::make('asistencia')
                    ->label('Asistencia')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->form(function (SesionProceso $record) {
                        $presentes = $record->asistencias()->where('asistio', true)->pluck('persona_id')->all();

                        return [
                            Forms\Components\CheckboxList::make('presentes')
                                ->label('Personas que asistieron')
                                ->options(
                                    $this->getOwnerRecord()->participantes()
                                        ->with('persona')
                                        ->get()
                                        ->mapWithKeys(fn ($p) => [$p->persona_id => $p->persona?->nombre_completo])
                                )
                                ->default($presentes)
                                ->columns(2),
                        ];
                    })
                    ->action(function (SesionProceso $record, array $data) {
                        $seleccionados = $data['presentes'] ?? [];

                        foreach ($this->getOwnerRecord()->participantes as $participante) {
                            Asistencia::updateOrCreate(
                                [
                                    'sesion_proceso_id' => $record->id,
                                    'persona_id' => $participante->persona_id,
                                ],
                                [
                                    'asistio' => in_array($participante->persona_id, $seleccionados),
                                ]
                            );
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
