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
                            ->native(false),
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario del sistema')
                            ->relationship('user', 'name')
                            ->helperText('Solo si esta persona debe iniciar sesión (p. ej. un líder principal).')
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombres')
                    ->label('Nombre')
                    ->formatStateUsing(fn (Persona $record) => $record->nombre_completo)
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
