<header id="header" class="header fixed-top d-flex align-items-center gap-3">

    @php
        $iniciales = strtoupper(substr(auth()->user()->name, 0, 2));
        $rolNombre = match(auth()->user()->rol?->nombre) {
            'super_admin' => 'Admin Principal',
            'admin_general' => 'Admin General',
            'lider_red' => 'Líder de Red',
            default => 'Usuario',
        };
        $alertasCount = Route::has('alertas.index') ? app(\App\Services\AlertasService::class)->totalAlertas(auth()->user()->alcancePersonaIds()) : 0;
    @endphp

    {{-- LOGO + TOGGLE --}}
    <div class="d-flex align-items-center flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="logo">
            <div class="logo-icon">INP</div>
            <div>
                <div class="logo-text">{{ config('app.name') }}</div>
                <div class="logo-sub">Sistema de Gestión</div>
            </div>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    {{-- ACCIONES + PERFIL --}}
    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center gap-2 mb-0 list-unstyled flex-wrap">

            {{-- ALERTAS --}}
            @if($modulosPermitidos->contains('alertas') && Route::has('alertas.index'))
            <li>
                <a href="{{ route('alertas.index') }}" class="st-pill alertas">
                    <i class="bi bi-bell-fill"></i>
                    <span class="d-none d-md-inline">Alertas</span>
                    @if($alertasCount > 0)
                        <span class="badge-count">{{ $alertasCount > 99 ? '99+' : $alertasCount }}</span>
                    @endif
                </a>
            </li>
            @endif

            {{-- SEPARADOR --}}
            <li class="d-none d-lg-flex"><div class="st-sep mx-1"></div></li>

            {{-- TOGGLE DARK MODE --}}
            <li>
                <button id="theme-toggle" class="st-pill theme-toggle" title="Cambiar tema">
                    <i class="bi bi-moon-fill"></i>
                </button>
            </li>

            {{-- SEPARADOR --}}
            <li class="d-none d-lg-flex"><div class="st-sep mx-1"></div></li>

            {{-- PERFIL --}}
            <li class="nav-item dropdown">
                <a href="#"
                   data-bs-toggle="dropdown"
                   class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="st-avatar">{{ $iniciales }}</div>
                    <div class="st-user-info d-none d-lg-block">
                        <div class="uname">{{ auth()->user()->name }}</div>
                        <div class="urole">{{ $rolNombre }}</div>
                    </div>
                    <i class="bi bi-chevron-down st-chevron d-none d-lg-inline"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end st-profile mt-2">
                    <li class="dh">
                        <div class="dh-name">{{ auth()->user()->name }}</div>
                        <div class="dh-email">{{ auth()->user()->email }}</div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>

                    <li>
                        <a class="dropdown-item text-danger" href="#"
                           onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            Cerrar sesión
                        </a>
                    </li>
                </ul>

                <form id="logout-form"
                      action="{{ route('logout') }}"
                      method="POST"
                      class="d-none">
                    @csrf
                </form>
            </li>

        </ul>
    </nav>

</header>
