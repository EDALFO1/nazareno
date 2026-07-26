<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcesoResource\Pages;
use App\Filament\Resources\ProcesoResource\RelationManagers\ParticipantesRelationManager;
use App\Filament\Resources\ProcesoResource\RelationManagers\SesionesRelationManager;
use App\Models\Proceso;
use App\Models\TipoProceso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProcesoResource extends Resource
{
    protected static ?string $model = Proceso::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Procesos de formación';

    protected static ?string $modelLabel = 'proceso';

    protected static ?string $pluralModelLabel = 'procesos (ediciones)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tipo_proceso_id')
                    ->label('Tipo de proceso')
                    ->relationship('tipoProceso', 'nombre', fn ($query) => $query->orderBy('orden'))
                    ->required()
                    ->live()
                    ->native(false),
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre de la edición')
                    ->placeholder('Ej. Encuentro Hombres Julio 2026')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->native(false),
                Forms\Components\Select::make('estado')
                    ->options([
                        'planificado' => 'Planificado',
                        'en_curso' => 'En curso',
                        'finalizado' => 'Finalizado',
                    ])
                    ->default('planificado')
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('cargar_desde_proceso_id')
                    ->label('Cargar participantes que terminaron')
                    ->helperText('Trae automáticamente, como participantes de esta edición, a quienes terminaron la edición que elijas del proceso anterior en la secuencia.')
                    ->options(fn (Forms\Get $get) => static::opcionesProcesoAnterior($get('tipo_proceso_id')))
                    ->visible(fn (Forms\Get $get, string $operation) => $operation === 'create'
                        && static::tipoProcesoAnterior($get('tipo_proceso_id')) !== null)
                    ->native(false),
            ]);
    }

    protected static function tipoProcesoAnterior(?int $tipoProcesoId): ?TipoProceso
    {
        if (! $tipoProcesoId) {
            return null;
        }

        $tipoActual = TipoProceso::find($tipoProcesoId);

        if (! $tipoActual || $tipoActual->orden === null) {
            return null;
        }

        return TipoProceso::where('orden', $tipoActual->orden - 1)->first();
    }

    /**
     * @return array<int, string>
     */
    protected static function opcionesProcesoAnterior(?int $tipoProcesoId): array
    {
        $tipoAnterior = static::tipoProcesoAnterior($tipoProcesoId);

        if (! $tipoAnterior) {
            return [];
        }

        return Proceso::query()
            ->where('tipo_proceso_id', $tipoAnterior->id)
            ->orderByDesc('fecha_inicio')
            ->get()
            ->mapWithKeys(fn (Proceso $proceso) => [
                $proceso->id => "{$proceso->nombre} ({$proceso->participantes()->where('estado_participacion', 'terminado')->count()} terminaron)",
            ])
            ->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipoProceso.nombre')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'planificado' => 'gray',
                        'en_curso' => 'warning',
                        'finalizado' => 'success',
                    }),
                Tables\Columns\TextColumn::make('participantes_count')
                    ->label('Participantes')
                    ->counts('participantes'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_proceso_id')
                    ->label('Tipo de proceso')
                    ->relationship('tipoProceso', 'nombre'),
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'planificado' => 'Planificado',
                        'en_curso' => 'En curso',
                        'finalizado' => 'Finalizado',
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
            SesionesRelationManager::class,
            ParticipantesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProcesos::route('/'),
            'create' => Pages\CreateProceso::route('/create'),
            'edit' => Pages\EditProceso::route('/{record}/edit'),
        ];
    }
}
