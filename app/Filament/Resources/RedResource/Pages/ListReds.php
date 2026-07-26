<?php

namespace App\Filament\Resources\RedResource\Pages;

use App\Filament\Resources\RedResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReds extends ListRecords
{
    protected static string $resource = RedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
