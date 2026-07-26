<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoContableResource\Pages;
use App\Models\CategoriaContable;
use App\Models\MovimientoContable;
use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MovimientoContableResource extends Resource
{
    protected static ?string $model = MovimientoContable::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Movimientos';

    protected static ?string $slug = 'movimientos-contables';

    protected static ?string $modelLabel = 'movimiento contable';

    protected static ?string $pluralModelLabel = 'movimientos contables';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Movimiento')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'ingreso' => 'Ingreso',
                                'egreso' => 'Egreso',
                            ])
                            ->required()
                            ->live()
                            ->native(false),
                        Forms\Components\Select::make('categoria_contable_id')
                            ->label('Categoría')
                            ->options(
                                fn (Forms\Get $get) => CategoriaContable::query()
                                    ->where('activo', true)
                                    ->when($get('tipo'), fn (Builder $q, $tipo) => $q->where('tipo', $tipo))
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                            )
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('fecha')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\TextInput::make('monto')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0.01),
                        Forms\Components\Select::make('metodo_pago')
                            ->label('Método de pago')
                            ->options([
                                'efectivo' => 'Efectivo',
                                'consignacion' => 'Consignación',
                                'transferencia' => 'Transferencia',
                                'cheque' => 'Cheque',
                            ])
                            ->default('efectivo')
                            ->required()
                            ->live()
                            ->native(false),
                        Forms\Components\TextInput::make('referencia')
                            ->label('Número de referencia')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => in_array($get('metodo_pago'), ['consignacion', 'transferencia', 'cheque'])),
                    ]),
                Forms\Components\Section::make('Relacionado con')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('persona_id')
                            ->label('Persona')
                            ->relationship('persona', 'nombres', fn (Builder $query) => $query->orderBy('nombres'))
                            ->getOptionLabelFromRecordUsing(fn (Persona $record) => $record->nombre_completo)
                            ->searchable(['nombres', 'apellidos'])
                            ->preload()
                            ->native(false)
                            ->helperText('Opcional. Déjalo vacío para una ofrenda general sin donante específico.'),
                        Forms\Components\Select::make('red_id')
                            ->label('Red')
                            ->relationship('red', 'nombre')
                            ->native(false),
                        Forms\Components\Select::make('punto_conexion_id')
                            ->label('Punto de conexión')
                            ->relationship('puntoConexion', 'nombre')
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),
                Forms\Components\Section::make('Detalle')
                    ->schema([
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Concepto')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('comprobante')
                            ->label('Comprobante (recibo, voucher, factura)')
                            ->directory('comprobantes')
                            ->image()
                            ->openable()
                            ->downloadable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state) => $state === 'ingreso' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state) => $state === 'ingreso' ? 'Ingreso' : 'Egreso'),
                Tables\Columns\TextColumn::make('categoriaContable.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('monto')
                    ->money('COP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('persona.nombres')
                    ->label('Persona')
                    ->formatStateUsing(fn (MovimientoContable $record) => $record->persona?->nombre_completo ?? '—'),
                Tables\Columns\TextColumn::make('red.nombre')
                    ->label('Red')
                    ->badge(),
                Tables\Columns\TextColumn::make('metodo_pago')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'efectivo' => 'Efectivo',
                        'consignacion' => 'Consignación',
                        'transferencia' => 'Transferencia',
                        'cheque' => 'Cheque',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('comprobante')
                    ->label('Comprobante')
                    ->boolean()
                    ->getStateUsing(fn (MovimientoContable $record) => filled($record->comprobante)),
                Tables\Columns\TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'ingreso' => 'Ingreso',
                        'egreso' => 'Egreso',
                    ]),
                Tables\Filters\SelectFilter::make('categoria_contable_id')
                    ->label('Categoría')
                    ->relationship('categoriaContable', 'nombre'),
                Tables\Filters\SelectFilter::make('red_id')
                    ->label('Red')
                    ->relationship('red', 'nombre'),
                Tables\Filters\SelectFilter::make('metodo_pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'consignacion' => 'Consignación',
                        'transferencia' => 'Transferencia',
                        'cheque' => 'Cheque',
                    ]),
                Tables\Filters\Filter::make('fecha')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->native(false),
                        Forms\Components\DatePicker::make('hasta')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['desde'], fn (Builder $q, $fecha) => $q->whereDate('fecha', '>=', $fecha))
                            ->when($data['hasta'], fn (Builder $q, $fecha) => $q->whereDate('fecha', '<=', $fecha));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovimientoContables::route('/'),
            'create' => Pages\CreateMovimientoContable::route('/create'),
            'edit' => Pages\EditMovimientoContable::route('/{record}/edit'),
        ];
    }
}
