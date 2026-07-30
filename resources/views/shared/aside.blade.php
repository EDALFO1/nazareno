<aside id="sidebar" class="sidebar">

<ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-heading">Principal</li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}"
           href="{{ route('dashboard') }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>
    </li>

    @if($modulosPermitidos->contains('alertas') && Route::has('alertas.index'))
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('alertas.*') ? '' : 'collapsed' }}"
           href="{{ route('alertas.index') }}">
            <i class="bi bi-bell-fill"></i>
            <span>Alertas</span>
        </a>
    </li>
    @endif

    {{-- PERSONAS Y REDES --}}
    @php
        $tienePersonas = $modulosPermitidos->contains('personas') && Route::has('personas.index');
        $tieneRedes = $modulosPermitidos->contains('redes') && Route::has('redes.index');
        $tienePuntos = $modulosPermitidos->contains('puntos_conexion') && Route::has('puntos_conexion.index');
        $tieneEstructura = $modulosPermitidos->contains('estructura_red') && Route::has('estructura-red.index');
        $tieneQr = $modulosPermitidos->contains('codigo_qr_registro') && Route::has('qr-registro.index');
        $hayPersonasYRedes = $tienePersonas || $tieneRedes || $tienePuntos || $tieneEstructura || $tieneQr;
    @endphp

    @if($hayPersonasYRedes)
    <li class="nav-heading">Personas y Redes</li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('personas.*','redes.*','puntos_conexion.*','estructura-red.*','qr-registro.*') ? '' : 'collapsed' }}"
           data-bs-target="#nav-personas"
           data-bs-toggle="collapse"
           href="#">
            <i class="bi bi-people-fill"></i>
            <span>Personas y Redes</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nav-personas"
            class="nav-content collapse {{ request()->routeIs('personas.*','redes.*','puntos_conexion.*','estructura-red.*','qr-registro.*') ? 'show' : '' }}"
            data-bs-parent="#sidebar-nav">
            @if($tienePersonas)
            <li><a href="{{ route('personas.index') }}" class="{{ request()->routeIs('personas.*') ? 'active' : '' }}"><span>Personas</span></a></li>
            @endif
            @if($tieneRedes)
            <li><a href="{{ route('redes.index') }}" class="{{ request()->routeIs('redes.*') ? 'active' : '' }}"><span>Redes</span></a></li>
            @endif
            @if($tienePuntos)
            <li><a href="{{ route('puntos_conexion.index') }}" class="{{ request()->routeIs('puntos_conexion.*') ? 'active' : '' }}"><span>Puntos de conexión</span></a></li>
            @endif
            @if($tieneEstructura)
            <li><a href="{{ route('estructura-red.index') }}" class="{{ request()->routeIs('estructura-red.*') ? 'active' : '' }}"><span>Estructura de red</span></a></li>
            @endif
            @if($tieneQr)
            <li><a href="{{ route('qr-registro.index') }}" class="{{ request()->routeIs('qr-registro.*') ? 'active' : '' }}"><span>QR de registro</span></a></li>
            @endif
        </ul>
    </li>
    @endif

    {{-- PROCESOS --}}
    @if($modulosPermitidos->contains('procesos') && Route::has('procesos.index'))
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('procesos.*') ? '' : 'collapsed' }}"
           href="{{ route('procesos.index') }}">
            <i class="bi bi-mortarboard-fill"></i>
            <span>Procesos de formación</span>
        </a>
    </li>
    @endif

    {{-- FINANZAS --}}
    @php
        $tieneCategorias = $modulosPermitidos->contains('categorias_contables') && Route::has('categorias_contables.index');
        $tieneCuentasBancarias = $modulosPermitidos->contains('cuentas_bancarias') && Route::has('cuentas_bancarias.index');
        $tieneCuentasPendientes = $modulosPermitidos->contains('cuentas_pendientes') && Route::has('cuentas_pendientes.index');
        $tieneMovimientos = $modulosPermitidos->contains('movimientos_contables') && Route::has('movimientos_contables.index');
        $tieneDonaciones = $modulosPermitidos->contains('donaciones_activos') && Route::has('donaciones_activos.index');
        $tieneProveedores = $modulosPermitidos->contains('proveedores') && Route::has('proveedores.index');
        $tieneCertificado = $modulosPermitidos->contains('certificado_donante') && Route::has('certificado-donante.index');
        $tieneReportes = $modulosPermitidos->contains('reportes') && Route::has('reportes.index');
        $hayFinanzas = $tieneCategorias || $tieneCuentasBancarias || $tieneCuentasPendientes || $tieneMovimientos || $tieneDonaciones || $tieneProveedores || $tieneCertificado || $tieneReportes;
    @endphp

    @if($hayFinanzas)
    <li class="nav-divider"></li>
    <li class="nav-heading">Finanzas</li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('categorias_contables.*','cuentas_bancarias.*','cuentas_pendientes.*','movimientos_contables.*','donaciones_activos.*','proveedores.*','certificado-donante.*','reportes.*') ? '' : 'collapsed' }}"
           data-bs-target="#nav-finanzas"
           data-bs-toggle="collapse"
           href="#">
            <i class="bi bi-cash-coin"></i>
            <span>Finanzas</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nav-finanzas"
            class="nav-content collapse {{ request()->routeIs('categorias_contables.*','cuentas_bancarias.*','cuentas_pendientes.*','movimientos_contables.*','donaciones_activos.*','proveedores.*','certificado-donante.*','reportes.*') ? 'show' : '' }}"
            data-bs-parent="#sidebar-nav">
            @if($tieneCategorias)
            <li><a href="{{ route('categorias_contables.index') }}" class="{{ request()->routeIs('categorias_contables.*') ? 'active' : '' }}"><span>Categorías contables</span></a></li>
            @endif
            @if($tieneCuentasBancarias)
            <li><a href="{{ route('cuentas_bancarias.index') }}" class="{{ request()->routeIs('cuentas_bancarias.*') ? 'active' : '' }}"><span>Cuentas bancarias</span></a></li>
            @endif
            @if($tieneCuentasPendientes)
            <li><a href="{{ route('cuentas_pendientes.index') }}" class="{{ request()->routeIs('cuentas_pendientes.*') ? 'active' : '' }}"><span>Cuentas pendientes</span></a></li>
            @endif
            @if($tieneMovimientos)
            <li><a href="{{ route('movimientos_contables.index') }}" class="{{ request()->routeIs('movimientos_contables.*') ? 'active' : '' }}"><span>Movimientos contables</span></a></li>
            @endif
            @if($tieneProveedores)
            <li><a href="{{ route('proveedores.index') }}" class="{{ request()->routeIs('proveedores.*') ? 'active' : '' }}"><span>Proveedores</span></a></li>
            @endif
            @if($tieneDonaciones)
            <li><a href="{{ route('donaciones_activos.index') }}" class="{{ request()->routeIs('donaciones_activos.*') ? 'active' : '' }}"><span>Donaciones de activos</span></a></li>
            @endif
            @if($tieneCertificado)
            <li><a href="{{ route('certificado-donante.index') }}" class="{{ request()->routeIs('certificado-donante.*') ? 'active' : '' }}"><span>Certificado de donante</span></a></li>
            @endif
            @if($tieneReportes)
            <li><a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}"><span>Reporte financiero</span></a></li>
            @endif
        </ul>
    </li>
    @endif

    {{-- SISTEMA --}}
    @php
        $tieneUsuarios = $modulosPermitidos->contains('usuarios') && Route::has('usuarios.index');
        $tieneRoles = $modulosPermitidos->contains('roles') && Route::has('roles.index');
        $tieneModulosRol = $modulosPermitidos->contains('modulos_rol') && Route::has('modulos-rol.index');
        $haySistema = $tieneUsuarios || $tieneRoles || $tieneModulosRol;
    @endphp

    @if($haySistema)
    <li class="nav-divider"></li>
    <li class="nav-heading">Sistema</li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('usuarios.*','roles.*','modulos-rol.*') ? '' : 'collapsed' }}"
           data-bs-target="#nav-sistema"
           data-bs-toggle="collapse"
           href="#">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Sistema</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nav-sistema"
            class="nav-content collapse {{ request()->routeIs('usuarios.*','roles.*','modulos-rol.*') ? 'show' : '' }}"
            data-bs-parent="#sidebar-nav">
            @if($tieneUsuarios)
            <li><a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}"><span>Usuarios</span></a></li>
            @endif
            @if($tieneRoles)
            <li><a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}"><span>Roles</span></a></li>
            @endif
            @if($tieneModulosRol)
            <li><a href="{{ route('modulos-rol.index') }}" class="{{ request()->routeIs('modulos-rol.*') ? 'active' : '' }}"><span>Módulos por rol</span></a></li>
            @endif
        </ul>
    </li>
    @endif

</ul>

</aside>
