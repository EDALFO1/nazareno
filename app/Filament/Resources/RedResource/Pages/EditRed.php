<?php

namespace App\Filament\Resources\RedResource\Pages;

use App\Filament\Resources\RedResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRed extends EditRecord
{
    protected static string $resource = RedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
