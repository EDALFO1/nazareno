<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonacionActivoResource\Pages;
use App\Models\DonacionActivo;
use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DonacionActivoResource extends Resource
{
    protected static ?string $model = DonacionActivo::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Donaciones en especie';

    protected static ?string $slug = 'donaciones-activos';

    protected static ?string $modelLabel = 'donación en especie';

    protected static ?string $pluralModelLabel = 'donaciones en especie';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Donación')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('persona_id')
                            ->label('Donante')
                            ->relationship('persona', 'nombres', fn (Builder $query) => $query->orderBy('nombres'))
                            ->getOptionLabelFromRecordUsing(fn (Persona $record) => $record->nombre_completo)
                            ->searchable(['nombres', 'apellidos'])
                            ->preload()
                            ->native(false)
                            ->helperText('Opcional, si se conoce quién la donó.'),
                        Forms\Components\DatePicker::make('fecha')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\TextInput::make('descripcion')
                            ->label('Descripción del activo')
                            ->placeholder('Ej. Escritorio de oficina, impresora HP, guitarra acústica')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('valor_estimado')
                            ->label('Valor estimado')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Valor aproximado de mercado, para efectos contables.'),
                        Forms\Components\TextInput::make('ubicacion_asignada')
                            ->label('Ubicación / asignado a')
                            ->placeholder('Ej. Oficina pastoral, salón de niños')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notas')
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Activo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.nombres')
                    ->label('Donante')
                    ->formatStateUsing(fn (DonacionActivo $record) => $record->persona?->nombre_completo ?? '—'),
                Tables\Columns\TextColumn::make('valor_estimado')
                    ->label('Valor estimado')
                    ->money('COP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ubicacion_asignada')
                    ->label('Ubicación'),
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
            'index' => Pages\ListDonacionActivos::route('/'),
            'create' => Pages\CreateDonacionActivo::route('/create'),
            'edit' => Pages\EditDonacionActivo::route('/{record}/edit'),
        ];
    }
}
