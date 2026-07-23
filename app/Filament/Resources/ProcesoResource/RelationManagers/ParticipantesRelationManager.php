<?php

namespace App\Filament\Resources\ProcesoResource\RelationManagers;

use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ParticipantesRelationManager extends RelationManager
{
    protected static string $relationship = 'participantes';

    protected static ?string $title = 'Participantes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('persona_id')
                    ->label('Persona')
                    ->relationship(
                        'persona',
                        'nombres',
                        function (Builder $query) {
                            $alcanceIds = Auth::user()->alcancePersonaIds();

                            if ($alcanceIds !== null) {
                                $query->whereIn('id', $alcanceIds);
                            }

                            return $query->orderBy('nombres');
                        }
                    )
                    ->getOptionLabelFromRecordUsing(fn (Persona $record) => $record->nombre_completo)
                    ->searchable(['nombres', 'apellidos'])
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('red_id')
                    ->label('Red')
                    ->relationship('red', 'nombre')
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
                    ->live()
                    ->native(false),
                Forms\Components\Select::make('sesion_retiro_id')
                    ->label('Sesión en la que se retiró')
                    ->relationship('sesionRetiro', 'numero_sesion', fn (Builder $query) => $query->where('proceso_id', $this->getOwnerRecord()->id))
                    ->visible(fn (Forms\Get $get) => $get('estado_participacion') === 'retirado')
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('persona_id')
            ->columns([
                Tables\Columns\TextColumn::make('persona.nombres')
                    ->label('Persona')
                    ->formatStateUsing(fn ($record) => $record->persona?->nombre_completo)
                    ->searchable(),
                Tables\Columns\TextColumn::make('red.nombre')
                    ->label('Red')
                    ->badge(),
                Tables\Columns\TextColumn::make('estado_participacion')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'en_curso' => 'warning',
                        'terminado' => 'success',
                        'retirado' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('sesionRetiro.numero_sesion')
                    ->label('Sesión de retiro'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_participacion')
                    ->options([
                        'en_curso' => 'En curso',
                        'terminado' => 'Terminado',
                        'retirado' => 'Retirado',
                    ]),
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
