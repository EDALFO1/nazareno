<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RedResource\Pages;
use App\Filament\Resources\RedResource\RelationManagers\PersonasRelationManager;
use App\Models\Red;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RedResource extends Resource
{
    protected static ?string $model = Red::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Personas y Redes';

    protected static ?string $navigationLabel = 'Redes';

    protected static ?string $slug = 'redes';

    protected static ?string $modelLabel = 'red';

    protected static ?string $pluralModelLabel = 'redes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Red')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('personas_count')
                    ->label('Personas')
                    ->counts('personas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lideres_principales_count')
                    ->label('Líderes principales')
                    ->counts('lideresPrincipales')
                    ->sortable(),
                Tables\Columns\TextColumn::make('puntos_conexion_count')
                    ->label('Puntos de conexión')
                    ->counts('puntosConexion')
                    ->sortable(),
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
            PersonasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReds::route('/'),
            'create' => Pages\CreateRed::route('/create'),
            'edit' => Pages\EditRed::route('/{record}/edit'),
        ];
    }
}
