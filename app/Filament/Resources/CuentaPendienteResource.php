<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaPendienteResource\Pages;
use App\Filament\Resources\CuentaPendienteResource\RelationManagers\MovimientosRelationManager;
use App\Models\CategoriaContable;
use App\Models\CuentaPendiente;
use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CuentaPendienteResource extends Resource
{
    protected static ?string $model = CuentaPendiente::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Cuentas por cobrar/pagar';

    protected static ?string $slug = 'cuentas-pendientes';

    protected static ?string $modelLabel = 'cuenta pendiente';

    protected static ?string $pluralModelLabel = 'cuentas por cobrar/pagar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cuenta pendiente')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('tipo')
                            ->options([
                                'por_cobrar' => 'Por cobrar (nos deben)',
                                'por_pagar' => 'Por pagar (debemos)',
                            ])
                            ->required()
                            ->live()
                            ->native(false),
                        Forms\Components\Select::make('categoria_contable_id')
                            ->label('Categoría')
                            ->options(
                                fn (Forms\Get $get) => CategoriaContable::query()
                                    ->where('activo', true)
                                    ->when(
                                        $get('tipo'),
                                        fn (Builder $q, $tipo) => $q->where('tipo', $tipo === 'por_cobrar' ? 'ingreso' : 'egreso')
                                    )
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                            )
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('persona_id')
                            ->label(fn (Forms\Get $get) => $get('tipo') === 'por_pagar' ? 'Proveedor / a quién le debemos' : 'Persona que debe')
                            ->relationship('persona', 'nombres', fn (Builder $query) => $query->orderBy('nombres'))
                            ->getOptionLabelFromRecordUsing(fn (Persona $record) => $record->nombre_completo)
                            ->searchable(['nombres', 'apellidos'])
                            ->preload()
                            ->native(false)
                            ->helperText('Opcional. Solo aplica si la persona o el proveedor ya está registrado en el sistema como Persona.'),
                        Forms\Components\TextInput::make('descripcion')
                            ->label('Concepto')
                            ->placeholder('Ej. Compromiso ofrenda construcción, Factura reparación techo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('monto_total')
                            ->label('Monto total')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0.01),
                        Forms\Components\DatePicker::make('fecha')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\DatePicker::make('fecha_vencimiento')
                            ->label('Fecha de vencimiento')
                            ->native(false),
                        Forms\Components\Textarea::make('notas')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_vencimiento')
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state) => $state === 'por_cobrar' ? 'info' : 'warning')
                    ->formatStateUsing(fn (string $state) => $state === 'por_cobrar' ? 'Por cobrar' : 'Por pagar'),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Concepto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.nombres')
                    ->label('Persona')
                    ->formatStateUsing(fn (CuentaPendiente $record) => $record->persona?->nombre_completo ?? '—'),
                Tables\Columns\TextColumn::make('monto_total')
                    ->label('Total')
                    ->money('COP'),
                Tables\Columns\TextColumn::make('saldo_pendiente')
                    ->label('Saldo pendiente')
                    ->money('COP')
                    ->weight('bold')
                    ->color(fn (CuentaPendiente $record) => $record->saldo_pendiente > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (CuentaPendiente $record) => match ($record->estado) {
                        'pendiente' => 'gray',
                        'parcial' => 'info',
                        'pagada' => 'success',
                        'vencida' => 'danger',
                    })
                    ->formatStateUsing(fn (CuentaPendiente $record) => match ($record->estado) {
                        'pendiente' => 'Pendiente',
                        'parcial' => 'Pago parcial',
                        'pagada' => 'Pagada',
                        'vencida' => 'Vencida',
                    }),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Vence')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'por_cobrar' => 'Por cobrar',
                        'por_pagar' => 'Por pagar',
                    ]),
                Tables\Filters\Filter::make('con_saldo')
                    ->label('Solo con saldo pendiente')
                    ->query(fn (Builder $query) => $query->whereRaw(
                        'monto_total > (select coalesce(sum(monto), 0) from movimientos_contables where movimientos_contables.cuenta_pendiente_id = cuentas_pendientes.id)'
                    )),
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

    public static function getRelations(): array
    {
        return [
            MovimientosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuentaPendientes::route('/'),
            'create' => Pages\CreateCuentaPendiente::route('/create'),
            'edit' => Pages\EditCuentaPendiente::route('/{record}/edit'),
        ];
    }
}
