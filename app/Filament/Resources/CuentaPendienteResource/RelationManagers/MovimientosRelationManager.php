<?php

namespace App\Filament\Resources\CuentaPendienteResource\RelationManagers;

use App\Models\CuentaPendiente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MovimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'movimientos';

    protected static ?string $title = 'Abonos / pagos registrados';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->native(false),
                Forms\Components\Select::make('cuenta_bancaria_id')
                    ->label('Cuenta bancaria')
                    ->relationship('cuentaBancaria', 'nombre')
                    ->native(false),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Concepto')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descripcion')
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto')
                    ->money('COP'),
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
                Tables\Columns\TextColumn::make('cuentaBancaria.nombre')
                    ->label('Cuenta bancaria'),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Concepto'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        /** @var CuentaPendiente $cuenta */
                        $cuenta = $this->getOwnerRecord();

                        $data['tipo'] = $cuenta->tipo === 'por_cobrar' ? 'ingreso' : 'egreso';
                        $data['categoria_contable_id'] = $cuenta->categoria_contable_id;
                        $data['persona_id'] = $cuenta->persona_id;
                        $data['registrado_por_id'] = Auth::id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
