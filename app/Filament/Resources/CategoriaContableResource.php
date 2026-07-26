<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaContableResource\Pages;
use App\Models\CategoriaContable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoriaContableResource extends Resource
{
    protected static ?string $model = CategoriaContable::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Categorías contables';

    protected static ?string $slug = 'categorias-contables';

    protected static ?string $modelLabel = 'categoría contable';

    protected static ?string $pluralModelLabel = 'categorías contables';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Categoría')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('tipo')
                            ->options([
                                'ingreso' => 'Ingreso',
                                'egreso' => 'Egreso',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('descripcion')
                            ->label('Descripción')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('activo')
                            ->default(true)
                            ->helperText('Las categorías inactivas ya no aparecen para elegir en nuevos movimientos.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tipo')
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state) => $state === 'ingreso' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state) => $state === 'ingreso' ? 'Ingreso' : 'Egreso')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50),
                Tables\Columns\TextColumn::make('movimientos_count')
                    ->label('Movimientos')
                    ->counts('movimientos'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'ingreso' => 'Ingreso',
                        'egreso' => 'Egreso',
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaContables::route('/'),
            'create' => Pages\CreateCategoriaContable::route('/create'),
            'edit' => Pages\EditCategoriaContable::route('/{record}/edit'),
        ];
    }
}
