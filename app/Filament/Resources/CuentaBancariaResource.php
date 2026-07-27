<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaBancariaResource\Pages;
use App\Models\CuentaBancaria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CuentaBancariaResource extends Resource
{
    protected static ?string $model = CuentaBancaria::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Cuentas bancarias';

    protected static ?string $slug = 'cuentas-bancarias';

    protected static ?string $modelLabel = 'cuenta bancaria';

    protected static ?string $pluralModelLabel = 'cuentas bancarias';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cuenta')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->placeholder('Ej. Cuenta de Ahorros Bancolombia')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('banco')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('numero_cuenta')
                            ->label('Número de cuenta')
                            ->maxLength(255),
                        Forms\Components\Select::make('tipo_cuenta')
                            ->label('Tipo de cuenta')
                            ->options([
                                'ahorros' => 'Ahorros',
                                'corriente' => 'Corriente',
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('saldo_inicial')
                            ->label('Saldo inicial')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required()
                            ->helperText('El saldo con el que arrancó esta cuenta antes de registrar movimientos aquí.'),
                        Forms\Components\Toggle::make('activa')
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
                Tables\Columns\TextColumn::make('banco')
                    ->searchable(),
                Tables\Columns\TextColumn::make('numero_cuenta')
                    ->label('Número'),
                Tables\Columns\TextColumn::make('tipo_cuenta')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'ahorros' => 'Ahorros',
                        'corriente' => 'Corriente',
                        default => '—',
                    }),
                Tables\Columns\TextColumn::make('saldo_actual')
                    ->label('Saldo actual')
                    ->money('COP')
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('activa')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activa'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuentaBancarias::route('/'),
            'create' => Pages\CreateCuentaBancaria::route('/create'),
            'edit' => Pages\EditCuentaBancaria::route('/{record}/edit'),
        ];
    }
}
