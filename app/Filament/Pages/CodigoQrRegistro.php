<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CodigoQrRegistro extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'QR de registro';

    protected static ?string $navigationGroup = 'Personas y Redes';

    protected static ?string $title = 'Código QR para registro de personas';

    protected static ?string $slug = 'qr-registro';

    protected static string $view = 'filament.pages.codigo-qr-registro';

    public function getUrlRegistro(): string
    {
        return url('/registro');
    }
}
