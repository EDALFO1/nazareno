<?php

namespace App\Filament\Resources\PuntoConexionResource\RelationManagers;

use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MiembrosRelationManager extends RelationManager
{
    protected static string $relationship = 'miembros';

    protected static ?string $title = 'Miembros';

    protected static ?string $recordTitleAttribute = 'nombres';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('fecha_ingreso')
                    ->default(now())
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombres')
            ->columns([
                Tables\Columns\TextColumn::make('nombres')
                    ->label('Nombre')
                    ->formatStateUsing(fn (Persona $record) => $record->nombre_completo)
                    ->searchable(['nombres', 'apellidos']),
                Tables\Columns\TextColumn::make('estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('pivot.fecha_ingreso')
                    ->label('Fecha de ingreso')
                    ->date(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelect(function (Forms\Components\Select $select) {
                        return $select
                            ->getOptionLabelFromRecordUsing(fn (Persona $record) => $record->nombre_completo)
                            ->preload();
                    })
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        $alcanceIds = Auth::user()->alcancePersonaIds();

                        if ($alcanceIds !== null) {
                            $query->whereIn('id', $alcanceIds);
                        }

                        return $query;
                    })
                    ->form(fn (array $arguments, Tables\Actions\AttachAction $action) => [
                        $action->getRecordSelect(),
                        Forms\Components\DatePicker::make('fecha_ingreso')
                            ->default(now())
                            ->native(false),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
