<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PuntoConexionResource\Pages;
use App\Filament\Resources\PuntoConexionResource\RelationManagers\MiembrosRelationManager;
use App\Models\Persona;
use App\Models\PuntoConexion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PuntoConexionResource extends Resource
{
    protected static ?string $model = PuntoConexion::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Personas y Redes';

    protected static ?string $slug = 'puntos-conexion';

    protected static ?string $modelLabel = 'punto de conexión';

    protected static ?string $pluralModelLabel = 'puntos de conexión';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $alcanceIds = Auth::user()->alcancePersonaIds();

        if ($alcanceIds !== null) {
            $query->whereIn('lider_id', $alcanceIds);
        }

        return $query;
    }

    protected static function selectorDePersona(string $name, string $label): Forms\Components\Select
    {
        return Forms\Components\Select::make($name)
            ->label($label)
            ->relationship(
                str($name)->replace('_persona_id', '')->replace('_id', ''),
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
            ->preload()
            ->native(false);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Punto de conexión')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('red_id')
                            ->label('Red')
                            ->relationship('red', 'nombre')
                            ->required()
                            ->native(false),
                        static::selectorDePersona('lider_id', 'Líder')
                            ->required(),
                        static::selectorDePersona('anfitrion_persona_id', 'Anfitrión'),
                        Forms\Components\Select::make('dia_semana')
                            ->label('Día')
                            ->options([
                                'lunes' => 'Lunes',
                                'martes' => 'Martes',
                                'miercoles' => 'Miércoles',
                                'jueves' => 'Jueves',
                                'viernes' => 'Viernes',
                                'sabado' => 'Sábado',
                                'domingo' => 'Domingo',
                            ])
                            ->native(false),
                        Forms\Components\TimePicker::make('hora')
                            ->seconds(false),
                        Forms\Components\TextInput::make('direccion')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('activo')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('red.nombre')
                    ->label('Red')
                    ->badge(),
                Tables\Columns\TextColumn::make('lider.nombres')
                    ->label('Líder')
                    ->formatStateUsing(fn (PuntoConexion $record) => $record->lider?->nombre_completo)
                    ->icon(fn (PuntoConexion $record) => $record->lider?->es_lider_principal ? 'heroicon-s-star' : null)
                    ->iconColor('warning'),
                Tables\Columns\TextColumn::make('anfitrion.nombres')
                    ->label('Anfitrión')
                    ->formatStateUsing(fn (PuntoConexion $record) => $record->anfitrion?->nombre_completo),
                Tables\Columns\TextColumn::make('dia_semana')
                    ->label('Día')
                    ->badge(),
                Tables\Columns\TextColumn::make('hora')
                    ->time('h:i A'),
                Tables\Columns\TextColumn::make('miembros_count')
                    ->label('Miembros')
                    ->counts('miembros'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('red_id')
                    ->label('Red')
                    ->relationship('red', 'nombre'),
                Tables\Filters\TernaryFilter::make('activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MiembrosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPuntoConexions::route('/'),
            'create' => Pages\CreatePuntoConexion::route('/create'),
            'edit' => Pages\EditPuntoConexion::route('/{record}/edit'),
        ];
    }
}
