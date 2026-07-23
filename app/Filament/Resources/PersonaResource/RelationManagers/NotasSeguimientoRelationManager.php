<?php

namespace App\Filament\Resources\PersonaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class NotasSeguimientoRelationManager extends RelationManager
{
    protected static string $relationship = 'notasSeguimiento';

    protected static ?string $title = 'Notas de seguimiento';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('fecha')
                    ->required()
                    ->default(now())
                    ->native(false),
                Forms\Components\Textarea::make('nota')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nota')
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nota')
                    ->wrap()
                    ->limit(80),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado por'),
            ])
            ->defaultSort('fecha', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
