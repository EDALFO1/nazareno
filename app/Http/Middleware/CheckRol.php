<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRol
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (! in_array($request->user()?->rol?->nombre, $roles, true)) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permiso para acceder a esa sección.');
        }

        return $next($request);
    }
}
