<?php

namespace App\Filament\Resources\RedResource\RelationManagers;

use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PersonasRelationManager extends RelationManager
{
    protected static string $relationship = 'personas';

    protected static ?string $title = 'Personas';

    protected static ?string $recordTitleAttribute = 'nombres';

    /**
     * Ruta de navegación por la rama: cada entrada es el líder cuyos
     * discípulos directos se están viendo. Vacío = líderes principales de
     * la red (el nivel más alto).
     *
     * @var array<int, array{id: int, nombre: string}>
     */
    public array $rama = [];

    /**
     * @return int|null Id del líder cuyos discípulos directos se muestran ahora mismo, o null en la raíz.
     */
    protected function liderActualId(): ?int
    {
        return empty($this->rama) ? null : end($this->rama)['id'];
    }

    public function verRama(Persona $persona): void
    {
        $this->rama[] = ['id' => $persona->id, 'nombre' => $persona->nombre_completo];
    }

    public function irANivel(int $indice): void
    {
        $this->rama = array_slice($this->rama, 0, $indice);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombres')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('apellidos')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('genero')
                    ->options([
                        'masculino' => 'Masculino',
                        'femenino' => 'Femenino',
                    ])
                    ->native(false),
                Forms\Components\Select::make('estado')
                    ->options([
                        'nuevo' => 'Nuevo',
                        'en_seguimiento' => 'En seguimiento',
                        'en_red' => 'En red',
                        'inactivo' => 'Inactivo',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('lider_id')
                    ->label('Líder')
                    ->relationship(
                        'lider',
                        'nombres',
                        fn (Builder $query) => $query
                            ->where('red_id', $this->getOwnerRecord()->getKey())
                            ->orderBy('nombres')
                    )
                    ->getOptionLabelFromRecordUsing(fn (Persona $record) => $record->nombre_completo)
                    ->searchable(['nombres', 'apellidos'])
                    ->preload()
                    ->default(fn () => $this->liderActualId())
                    ->helperText('Déjalo vacío si esta persona es un líder principal de la red.')
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading($this->encabezadoRama())
            ->description($this->descripcionRama())
            ->recordTitleAttribute('nombres')
            ->modifyQueryUsing(function (Builder $query) {
                $liderId = $this->liderActualId();

                return $liderId === null
                    ? $query->whereNull('lider_id')
                    : $query->where('lider_id', $liderId);
            })
            ->columns([
                Tables\Columns\TextColumn::make('nombres')
                    ->label('Nombre')
                    ->formatStateUsing(fn (Persona $record) => $record->nombre_completo)
                    ->icon(fn (Persona $record) => $record->es_lider_principal ? 'heroicon-s-star' : null)
                    ->iconColor('warning')
                    ->tooltip(fn (Persona $record) => $record->es_lider_principal ? 'Líder principal' : null)
                    ->searchable(['nombres', 'apellidos']),
                Tables\Columns\TextColumn::make('linea_liderazgo')
                    ->label('Línea')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (Persona $record) => $record->etiqueta_linea),
                Tables\Columns\TextColumn::make('genero')
                    ->badge(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'nuevo' => 'info',
                        'en_seguimiento' => 'warning',
                        'en_red' => 'success',
                        'inactivo' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('discipulos_count')
                    ->label('Directos')
                    ->counts('discipulos')
                    ->badge()
                    ->color('gray'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('subir_nivel')
                    ->label(fn () => count($this->rama) > 1
                        ? '⬅ ' . $this->rama[count($this->rama) - 2]['nombre']
                        : '⬅ Líderes principales')
                    ->color('gray')
                    ->visible(fn () => ! empty($this->rama))
                    ->action(fn () => $this->irANivel(count($this->rama) - 1)),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_rama')
                    ->label('Ver rama')
                    ->icon('heroicon-o-share')
                    ->color('gray')
                    ->action(fn (Persona $record) => $this->verRama($record))
                    ->visible(fn (Persona $record) => $record->discipulos()->exists()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function encabezadoRama(): string
    {
        if (empty($this->rama)) {
            return 'Líderes principales';
        }

        return 'Rama de ' . end($this->rama)['nombre'];
    }

    protected function descripcionRama(): ?string
    {
        if (empty($this->rama)) {
            return null;
        }

        $migas = collect($this->rama)->pluck('nombre')->implode(' › ');

        return "Viendo: {$migas}. Usa los enlaces de abajo para subir de nivel.";
    }
}
