<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonaResource\Pages;
use App\Filament\Resources\PersonaResource\RelationManagers\NotasSeguimientoRelationManager;
use App\Filament\Resources\PersonaResource\RelationManagers\ProcesosRelationManager;
use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PersonaResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Personas y Redes';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $alcanceIds = Auth::user()->alcancePersonaIds();

        if ($alcanceIds !== null) {
            $query->whereIn('id', $alcanceIds);
        }

        return $query;
    }

    protected static ?string $modelLabel = 'persona';

    protected static ?string $pluralModelLabel = 'personas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos personales')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nombres')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('apellidos')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telefono')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('correo')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('direccion')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('genero')
                            ->options([
                                'masculino' => 'Masculino',
                                'femenino' => 'Femenino',
                            ]),
                        Forms\Components\DatePicker::make('fecha_nacimiento'),
                        Forms\Components\DatePicker::make('fecha_primera_visita')
                            ->label('Fecha de primera visita'),
                    ]),
                Forms\Components\Section::make('Petición de oración')
                    ->schema([
                        Forms\Components\Textarea::make('peticion_oracion')
                            ->label('Petición de oración')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Acudiente')
                    ->description('Si la persona es menor de edad.')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('acudiente')
                            ->label('Nombre del acudiente')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telefono_acudiente')
                            ->label('Teléfono del acudiente')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Select::make('parentesco')
                            ->options([
                                'padre' => 'Padre',
                                'madre' => 'Madre',
                                'abuelo_a' => 'Abuelo/a',
                                'tio_a' => 'Tío/a',
                                'hermano_a' => 'Hermano/a',
                                'tutor_legal' => 'Tutor legal',
                                'otro' => 'Otro',
                            ])
                            ->native(false),
                    ]),
                Forms\Components\Section::make('Red y liderazgo')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('estado')
                            ->options([
                                'nuevo' => 'Nuevo',
                                'en_seguimiento' => 'En seguimiento',
                                'en_red' => 'En red',
                                'inactivo' => 'Inactivo',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('red_id')
                            ->label('Red')
                            ->relationship('red', 'nombre')
                            ->native(false),
                        Forms\Components\Select::make('lider_id')
                            ->label('Líder')
                            ->relationship(
                                'lider',
                                'nombres',
                                fn ($query) => $query->orderBy('nombres')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Persona $record) => $record->nombre_completo)
                            ->searchable(['nombres', 'apellidos'])
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario del sistema')
                            ->relationship('user', 'name')
                            ->helperText('Solo si esta persona debe iniciar sesión (p. ej. un líder principal).')
                            ->visible(fn () => ! Auth::user()->hasRole('lider_red'))
                            ->native(false),
                    ]),
                Forms\Components\Section::make('Protección de datos')
                    ->description(
                        config('app.name').' es el Responsable del tratamiento de sus datos. Al marcar la casilla de aceptación, '
                        .'la persona (o su acudiente, si es menor de edad) autoriza de manera libre, voluntaria y expresa el uso de los '
                        .'datos aquí registrados con la finalidad exclusiva de gestionar el registro de asistencia, brindar acompañamiento '
                        .'pastoral y enviar invitaciones a nuestros cultos o actividades comunitarias. Se le informa que este registro puede '
                        .'revelar de forma indirecta su afiliación religiosa (considerado un dato sensible bajo la Ley 1581 de 2012) y que su '
                        .'aceptación es totalmente facultativa. Como titular, puede solicitar en cualquier momento la consulta, corrección o '
                        .'eliminación de sus datos enviando una solicitud al correo electrónico: '.config('app.correo_datos_personales').'.'
                    )
                    ->schema([
                        Forms\Components\Checkbox::make('autorizacion_confirmada')
                            ->label('La persona (o su acudiente) leyó el texto anterior y autorizó el tratamiento de sus datos.')
                            ->required()
                            // required() no basta: en Laravel `false` no cuenta como
                            // "vacío", así que una casilla sin marcar pasaría la
                            // validación. `accepted` sí exige que sea true.
                            ->rules(['accepted'])
                            ->dehydrated(false),
                    ])
                    ->visible(fn (string $operation, ?Persona $record) => $operation === 'create' || ($record && ! $record->tiene_autorizacion_datos)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort(function (Builder $query) {
                $ids = Persona::idsEnOrdenJerarquico();

                if ($ids) {
                    // CASE en vez de FIELD(): FIELD() es exclusivo de MySQL y
                    // los tests corren sobre SQLite.
                    $casos = collect($ids)
                        ->map(fn (int $id, int $posicion) => "WHEN {$id} THEN {$posicion}")
                        ->implode(' ');

                    $query->orderByRaw("CASE id {$casos} ELSE " . count($ids) . ' END');
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('nombres')
                    ->label('Nombre')
                    ->formatStateUsing(fn (Persona $record) => $record->nombre_completo)
                    ->icon(fn (Persona $record) => $record->es_lider_principal ? 'heroicon-s-star' : null)
                    ->iconColor('warning')
                    ->tooltip(fn (Persona $record) => $record->es_lider_principal ? 'Líder principal' : null)
                    ->searchable(['nombres', 'apellidos']),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('correo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('red.nombre')
                    ->label('Red')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lider.nombres')
                    ->label('Líder')
                    ->formatStateUsing(fn (Persona $record) => $record->lider?->nombre_completo)
                    ->sortable(),
                Tables\Columns\TextColumn::make('linea_liderazgo')
                    ->label('Línea')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (Persona $record) => $record->etiqueta_linea),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'nuevo' => 'info',
                        'en_seguimiento' => 'warning',
                        'en_red' => 'success',
                        'inactivo' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('fecha_primera_visita')
                    ->label('Primera visita')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('tiene_autorizacion_datos')
                    ->label('Autorización datos')
                    ->boolean()
                    ->tooltip(fn (Persona $record) => $record->tiene_autorizacion_datos ? 'Autorización de tratamiento de datos registrada.' : 'Falta registrar la autorización de tratamiento de datos.'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('red_id')
                    ->label('Red')
                    ->relationship('red', 'nombre'),
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'nuevo' => 'Nuevo',
                        'en_seguimiento' => 'En seguimiento',
                        'en_red' => 'En red',
                        'inactivo' => 'Inactivo',
                    ]),
                Tables\Filters\Filter::make('lideres_principales')
                    ->label('Solo líderes principales')
                    ->query(fn (Builder $query) => $query->whereNotNull('red_id')->whereNull('lider_id')),
                Tables\Filters\Filter::make('sin_autorizacion_datos')
                    ->label('Sin autorización de datos')
                    ->query(fn (Builder $query) => $query->whereDoesntHave('autorizacionesTratamientoDatos')),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_rama')
                    ->label('Ver rama')
                    ->icon('heroicon-o-share')
                    ->color('gray')
                    ->url(fn (Persona $record) => route('filament.admin.pages.estructura-red', ['lider' => $record->id]))
                    ->visible(fn (Persona $record) => $record->discipulos()->exists()),
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
            NotasSeguimientoRelationManager::class,
            ProcesosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonas::route('/'),
            'create' => Pages\CreatePersona::route('/create'),
            'edit' => Pages\EditPersona::route('/{record}/edit'),
        ];
    }
}
