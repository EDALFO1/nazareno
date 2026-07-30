<?php

namespace App\View\Composers;

use App\Services\ModuloService;
use Illuminate\View\View;

class SidebarComposer
{
    public function __construct(private ModuloService $service) {}

    public function compose(View $view): void
    {
        $view->with('modulosPermitidos', $this->service->modulosPermitidos());
    }
}
