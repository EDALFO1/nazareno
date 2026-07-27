<?php

namespace App\Filament\Resources\PuntoConexionResource\RelationManagers;

use App\Models\AsistenciaPuntoConexion;
use App\Models\Persona;
use App\Models\SesionPuntoConexion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SesionesRelationManager extends RelationManager
{
    protected static string $relationship = 'sesiones';

    protected static ?string $title = 'Reuniones y asistencia';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('fecha')
                    ->required()
                    ->default(now())
                    ->native(false),
                Forms\Components\Textarea::make('notas')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fecha')
            ->defaultSort('fecha', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notas')
                    ->limit(50),
                Tables\Columns\TextColumn::make('asistencias_count')
                    ->label('Asistieron')
                    ->counts(['asistencias' => fn (Builder $query) => $query->where('asistio', true)]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar reunión'),
            ])
            ->actions([
                Tables\Actions\Action::make('asistencia')
                    ->label('Asistencia')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->form(function (SesionPuntoConexion $record) {
                        $presentes = $record->asistencias()->where('asistio', true)->pluck('persona_id')->all();

                        return [
                            Forms\Components\CheckboxList::make('presentes')
                                ->label('Personas que asistieron')
                                ->options(
                                    $this->getOwnerRecord()->miembros()
                                        ->get()
                                        ->mapWithKeys(fn (Persona $miembro) => [$miembro->id => $miembro->nombre_completo])
                                )
                                ->default($presentes)
                                ->columns(2),
                        ];
                    })
                    ->action(function (SesionPuntoConexion $record, array $data) {
                        $seleccionados = $data['presentes'] ?? [];

                        foreach ($this->getOwnerRecord()->miembros as $miembro) {
                            AsistenciaPuntoConexion::updateOrCreate(
                                [
                                    'sesion_punto_conexion_id' => $record->id,
                                    'persona_id' => $miembro->id,
                                ],
                                [
                                    'asistio' => in_array($miembro->id, $seleccionados),
                                ]
                            );
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
