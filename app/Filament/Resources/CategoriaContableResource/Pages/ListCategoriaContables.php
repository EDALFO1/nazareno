<?php

namespace App\Filament\Resources\CategoriaContableResource\Pages;

use App\Filament\Resources\CategoriaContableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaContables extends ListRecords
{
    protected static string $resource = CategoriaContableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
