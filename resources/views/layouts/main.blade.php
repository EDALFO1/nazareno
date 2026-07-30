<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">

<title>@yield('titulo')</title>

<link href="{{ asset('favicon.ico') }}" rel="icon" type="image/x-icon">

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

<!-- Bootstrap -->
<link href="{{ asset('NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="{{ asset('NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.dataTables.css">

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Template CSS -->
<link href="{{ asset('NiceAdmin/assets/css/style.css') }}" rel="stylesheet">

<!-- Mobile Responsive CSS -->
<link href="{{ asset('css/responsive-mobile.css') }}" rel="stylesheet">

<style>
body { overflow-x: hidden; }

/* ── HEADER — Blanco ── */
#header {
    background: #ffffff !important;
    box-shadow: 0 1px 0 #e2e8f0 !important;
    border-bottom: none !important;
    height: 60px !important;
    padding: 0 1.25rem !important;
}

#header .logo {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; flex-shrink: 0;
}
#header .logo .logo-icon {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, #1F2ED1, #6366f1);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.78rem; font-weight: 800;
    color: #fff; letter-spacing: -0.5px; flex-shrink: 0;
}
#header .logo .logo-text {
    font-size: 1rem; font-weight: 700;
    color: #0f172a; letter-spacing: -0.3px; line-height: 1.2;
    display: none;
}
#header .logo .logo-sub {
    font-size: 0.65rem; color: #94a3b8;
    font-weight: 400; margin-top: 1px; display: none;
}
@media (min-width: 768px) {
    #header .logo .logo-text { display: block; }
    #header .logo .logo-sub  { display: block; }
}

.toggle-sidebar-btn {
    color: #64748b !important;
    font-size: 1.4rem !important;
    margin-left: 0; padding: 6px;
    border-radius: 6px; transition: all 0.15s; line-height: 1;
}
.toggle-sidebar-btn:hover { color: #0f172a !important; background: #f1f5f9; }

/* Pills */
.st-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 15px; border-radius: 99px;
    font-size: 0.8rem; font-weight: 600;
    text-decoration: none; transition: all 0.15s ease;
    border: 1px solid; white-space: nowrap;
    position: relative; cursor: pointer;
}
.st-pill.alertas { color: #dc2626; border-color: #fecaca; background: #fef2f2; }
.st-pill.alertas:hover { background: #dc2626; border-color: #dc2626; color: #fff !important; text-decoration: none; }
.st-pill.disabled { opacity: 0.4; pointer-events: none; }
.st-pill .badge-count {
    position: absolute; top: -5px; right: -5px;
    min-width: 16px; height: 16px; padding: 0 4px;
    border-radius: 99px; background: #ef4444; color: #fff;
    font-size: 0.58rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center; line-height: 1;
}

.st-sep { width: 1px; height: 26px; background: #e2e8f0; flex-shrink: 0; }

/* Avatar */
.st-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: 2px solid #e0e7ff;
    color: #fff; font-size: 0.75rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; flex-shrink: 0; transition: box-shadow 0.15s;
}
.st-avatar:hover { box-shadow: 0 0 0 3px #c7d2fe; }

.st-user-info { line-height: 1.25; }
.st-user-info .uname { font-size: 0.85rem; font-weight: 600; color: #0f172a; }
.st-user-info .urole { font-size: 0.7rem; color: #94a3b8; }
.st-chevron { font-size: 0.7rem; color: #94a3b8; }

/* Dropdown perfil */
.dropdown-menu.st-profile {
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 8px 24px rgba(15,23,42,0.12) !important;
    border-radius: 12px !important; min-width: 200px; padding: 6px;
}
.dropdown-menu.st-profile .dh { padding: 8px 10px 6px; }
.dropdown-menu.st-profile .dh .dh-name { font-size: 0.83rem; font-weight: 700; color: #0f172a; margin: 0; }
.dropdown-menu.st-profile .dh .dh-email { font-size: 0.7rem; color: #94a3b8; }
.dropdown-menu.st-profile .dropdown-item {
    border-radius: 7px; padding: 7px 10px; font-size: 0.8rem; font-weight: 500;
    color: #334155; display: flex; align-items: center; gap: 8px; transition: background 0.12s;
}
.dropdown-menu.st-profile .dropdown-item:hover { background: #f1f5f9; color: #0f172a; }
.dropdown-menu.st-profile .dropdown-item.text-danger:hover { background: #fef2f2; color: #dc2626; }

/* ── SIDEBAR — Blanco ── */
#sidebar {
    background: #ffffff !important;
    width: 240px !important;
    padding: 0 !important;
    border-right: none !important;
    overflow-y: auto;
    overflow-x: hidden;
    box-shadow: 1px 0 0 #e2e8f0 !important;
}

#sidebar::-webkit-scrollbar { width: 3px; }
#sidebar::-webkit-scrollbar-track { background: transparent; }
#sidebar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

@media (min-width: 1200px) {
    #main, #footer { margin-left: 240px !important; }
    .toggle-sidebar #main, .toggle-sidebar #footer { margin-left: 0 !important; }
    .toggle-sidebar #sidebar { left: -240px !important; }
}

#sidebar .sidebar-nav .nav-heading {
    font-size: 0.66rem !important; font-weight: 700 !important;
    letter-spacing: 0.12em !important; text-transform: uppercase !important;
    color: #94a3b8 !important;
    padding: 16px 18px 6px !important; margin: 0 !important;
}

#sidebar .sidebar-nav > .nav-item { padding: 3px 10px; }

#sidebar .sidebar-nav .nav-link {
    display: flex !important; align-items: center;
    padding: 8px 12px !important; border-radius: 9px !important;
    color: #334155 !important;
    font-size: 0.92rem !important; font-weight: 500;
    transition: all 0.15s ease; background: transparent !important;
    gap: 12px; margin: 0;
}

#sidebar .sidebar-nav .nav-link i:not(.bi-chevron-down) {
    font-size: 1.1rem !important;
    width: 36px; height: 36px;
    display: flex !important; align-items: center; justify-content: center;
    border-radius: 9px;
    flex-shrink: 0; margin: 0 !important;
    transition: all 0.15s;
}

#sidebar .sidebar-nav .nav-link .bi-grid-1x2-fill  { background: #eef2ff; color: #6366f1 !important; }
#sidebar .sidebar-nav .nav-link .bi-people-fill     { background: #ecfdf5; color: #059669 !important; }
#sidebar .sidebar-nav .nav-link .bi-share            { background: #eef2ff; color: #6366f1 !important; }
#sidebar .sidebar-nav .nav-link .bi-academic-cap    { background: #eff6ff; color: #2563eb !important; }
#sidebar .sidebar-nav .nav-link .bi-cash-coin        { background: #f0fdf4; color: #16a34a !important; }
#sidebar .sidebar-nav .nav-link .bi-bell-fill        { background: #fef2f2; color: #dc2626 !important; }
#sidebar .sidebar-nav .nav-link .bi-shield-lock-fill { background: #f1f5f9; color: #334155 !important; }

#sidebar .sidebar-nav .nav-link span { flex: 1; line-height: 1.3; color: inherit; }

#sidebar .sidebar-nav .nav-link .bi-chevron-down {
    font-size: 0.6rem !important; color: #94a3b8 !important;
    transition: transform 0.2s, color 0.2s;
    width: auto !important; height: auto !important;
    background: none !important; border-radius: 0 !important;
    flex: 0 !important;
}

#sidebar .sidebar-nav .nav-link:hover { background: #f8fafc !important; color: #0f172a !important; }

#sidebar .sidebar-nav .nav-link:not(.collapsed) { background: #f0f9ff !important; color: #0369a1 !important; }
#sidebar .sidebar-nav .nav-link:not(.collapsed) .bi-chevron-down { transform: rotate(180deg); color: #0369a1 !important; }

#sidebar .sidebar-nav .nav-content { background: transparent !important; padding: 2px 0 4px !important; }
#sidebar .sidebar-nav .nav-content li { padding: 1px 10px !important; }
#sidebar .sidebar-nav .nav-content li a {
    display: flex !important; align-items: center; gap: 8px;
    padding: 9px 12px 9px 56px !important;
    font-size: 0.86rem !important; font-weight: 500;
    color: #64748b !important; border-radius: 7px !important;
    margin: 0 !important; transition: all 0.13s ease;
    text-decoration: none !important; background: transparent !important;
}
#sidebar .sidebar-nav .nav-content li a i { display: none !important; }
#sidebar .sidebar-nav .nav-content li a:before {
    content: ''; width: 4px; height: 4px; border-radius: 50%;
    background: #cbd5e1; flex-shrink: 0; transition: background 0.13s;
    margin-left: -12px;
}
#sidebar .sidebar-nav .nav-content li a:hover { color: #0f172a !important; background: #f1f5f9 !important; }
#sidebar .sidebar-nav .nav-content li a:hover:before { background: #1F2ED1; }
#sidebar .sidebar-nav .nav-content li a.active { color: #1d4ed8 !important; background: #eff6ff !important; font-weight: 600; }
#sidebar .sidebar-nav .nav-content li a.active:before { background: #1F2ED1; }

#sidebar .sidebar-nav .nav-divider { height: 1px; background: #e2e8f0; margin: 6px 16px; }

/* Theme Toggle */
.st-pill.theme-toggle {
    color: #f59e0b; border-color: #fcd34d; background: #fffbeb;
    padding: 7px 10px !important;
}
.st-pill.theme-toggle:hover { background: #f59e0b; border-color: #f59e0b; color: #fff !important; }

/* Dark Mode Styles */
html.dark-mode { color-scheme: dark; }
html.dark-mode body { background: #0f172a !important; color: #e2e8f0 !important; }
html.dark-mode #header { background: #1e293b !important; box-shadow: 0 1px 0 #334155 !important; }
html.dark-mode #header .logo .logo-text { color: #f1f5f9 !important; }
html.dark-mode #header .logo .logo-sub { color: #64748b !important; }
html.dark-mode .toggle-sidebar-btn { color: #94a3b8 !important; }
html.dark-mode .toggle-sidebar-btn:hover { color: #f1f5f9 !important; background: #334155; }
html.dark-mode .st-pill.alertas { color: #fca5a5; border-color: #dc2626; background: #3f1414; }
html.dark-mode .st-pill.alertas:hover { background: #dc2626; border-color: #dc2626; color: #fff !important; }
html.dark-mode .st-sep { background: #334155; }
html.dark-mode .st-avatar { border-color: #312e81; }
html.dark-mode .st-avatar:hover { box-shadow: 0 0 0 3px #4f46e5; }
html.dark-mode .st-user-info .uname { color: #f1f5f9; }
html.dark-mode .st-user-info .urole { color: #94a3b8; }
html.dark-mode .st-chevron { color: #94a3b8; }
html.dark-mode .dropdown-menu.st-profile { border-color: #334155 !important; background: #1e293b !important; box-shadow: 0 8px 24px rgba(0,0,0,0.5) !important; }
html.dark-mode .dropdown-menu.st-profile .dh .dh-name { color: #f1f5f9; }
html.dark-mode .dropdown-menu.st-profile .dh .dh-email { color: #94a3b8; }
html.dark-mode .dropdown-menu.st-profile .dropdown-item { color: #cbd5e1; }
html.dark-mode .dropdown-menu.st-profile .dropdown-item:hover { background: #334155; color: #f1f5f9; }
html.dark-mode .dropdown-menu.st-profile .dropdown-item.text-danger { color: #fca5a5; }
html.dark-mode .dropdown-menu.st-profile .dropdown-item.text-danger:hover { background: #7f1d1d; color: #fca5a5; }
html.dark-mode .dropdown-divider { border-color: #334155; }
html.dark-mode #sidebar { background: #1e293b !important; box-shadow: 1px 0 0 #334155 !important; }
html.dark-mode #sidebar::-webkit-scrollbar-thumb { background: #475569; }
html.dark-mode #sidebar .sidebar-nav .nav-heading { color: #64748b !important; }
html.dark-mode #sidebar .sidebar-nav .nav-link { color: #cbd5e1 !important; }
html.dark-mode #sidebar .sidebar-nav .nav-link:hover { background: #334155 !important; color: #f1f5f9 !important; }
html.dark-mode #sidebar .sidebar-nav .nav-link:not(.collapsed) { background: #0c2d4b !important; color: #0ea5e9 !important; }
html.dark-mode #sidebar .sidebar-nav .nav-link:not(.collapsed) .bi-chevron-down { color: #0ea5e9 !important; }
html.dark-mode #sidebar .sidebar-nav .nav-content li a { color: #94a3b8 !important; }
html.dark-mode #sidebar .sidebar-nav .nav-content li a:before { background: #475569; }
html.dark-mode #sidebar .sidebar-nav .nav-content li a:hover { color: #f1f5f9 !important; background: #334155 !important; }
html.dark-mode #sidebar .sidebar-nav .nav-content li a:hover:before { background: #3b82f6; }
html.dark-mode #sidebar .sidebar-nav .nav-content li a.active { color: #0ea5e9 !important; background: #0c2d4b !important; }
html.dark-mode #sidebar .sidebar-nav .nav-content li a.active:before { background: #3b82f6; }
html.dark-mode #sidebar .sidebar-nav .nav-divider { background: #334155; }
html.dark-mode main.main { background: #0f172a; }
html.dark-mode .section { background: #0f172a; }
html.dark-mode .st-pill.theme-toggle { color: #fbbf24; border-color: #fcd34d; background: #78350f; }
html.dark-mode .st-pill.theme-toggle:hover { background: #d97706; border-color: #d97706; color: #fff !important; }
html.dark-mode .st-pill.theme-toggle i::before { content: '\f186'; }
html.dark-mode h1, html.dark-mode h2, html.dark-mode h3, html.dark-mode h4, html.dark-mode h5, html.dark-mode h6 { color: #f1f5f9; }
html.dark-mode .card, html.dark-mode .card-body { background: #1e293b; border-color: #334155; }
html.dark-mode .card-header { background: #334155; border-color: #475569; }
/* Bootstrap 5 pinta el fondo real de cada celda con estas variables, no con
   el fondo del <thead>/<tr> en sí — hay que redefinirlas, no solo poner
   background en el elemento, o el thead/tbody se quedan con el color claro
   de Bootstrap pase lo que pase. */
html.dark-mode .table {
    --bs-table-bg: transparent;
    --bs-table-color: #e2e8f0;
    --bs-table-border-color: #334155;
    --bs-table-striped-bg: #1e293b;
    --bs-table-striped-color: #e2e8f0;
    --bs-table-active-bg: #334155;
    --bs-table-active-color: #f1f5f9;
    --bs-table-hover-bg: #0f2a44;
    --bs-table-hover-color: #f1f5f9;
    color: #e2e8f0;
    border-color: #334155;
}
html.dark-mode .table-light {
    --bs-table-bg: #334155;
    --bs-table-color: #f1f5f9;
    --bs-table-border-color: #475569;
}
html.dark-mode .table tbody td { border-color: #334155; }
html.dark-mode .form-control, html.dark-mode .form-select, html.dark-mode .input-group { background: #1e293b; border-color: #475569; color: #f1f5f9; }
html.dark-mode .form-control:focus, html.dark-mode .form-select:focus { background: #1e293b; border-color: #1F2ED1; color: #f1f5f9; }
html.dark-mode .form-control::placeholder { color: #64748b; }
html.dark-mode .input-group-text { background: #334155; border-color: #475569; color: #cbd5e1; }
html.dark-mode .btn { border-color: #475569; }
html.dark-mode .btn-primary { background: #1F2ED1; border-color: #1F2ED1; }
html.dark-mode .btn-primary:hover { background: #1a26ad; border-color: #1a26ad; }
html.dark-mode .btn-secondary { background: #475569; border-color: #475569; color: #f1f5f9; }
html.dark-mode .btn-secondary:hover { background: #64748b; border-color: #64748b; }
html.dark-mode .badge { background: #1F2ED1; color: #fff; }
html.dark-mode .badge.bg-success { background: #10b981; }
html.dark-mode .badge.bg-danger { background: #ef4444; }
html.dark-mode .badge.bg-warning { background: #f59e0b; color: #000; }
html.dark-mode .badge.bg-info { background: #06b6d4; }
html.dark-mode .alert-success { background: #064e3b; border-color: #047857; color: #86efac; }
html.dark-mode .alert-danger { background: #7f1d1d; border-color: #dc2626; color: #fca5a5; }
html.dark-mode .alert-warning { background: #78350f; border-color: #b45309; color: #fcd34d; }
html.dark-mode .alert-info { background: #0c2d4b; border-color: #0284c7; color: #93c5fd; }
html.dark-mode .modal-content { background: #1e293b; border-color: #334155; }
html.dark-mode .modal-header { background: #334155; border-color: #475569; color: #f1f5f9; }
html.dark-mode .modal-footer { background: #334155; border-color: #475569; }
html.dark-mode .dropdown-menu { background: #1e293b; border-color: #334155; }
html.dark-mode .dropdown-item { color: #cbd5e1; }
html.dark-mode .dropdown-item:hover, html.dark-mode .dropdown-item:focus { background: #334155; color: #f1f5f9; }
html.dark-mode hr { border-color: #334155; }
html.dark-mode label { color: #e2e8f0; }
html.dark-mode .text-muted { color: #94a3b8 !important; }
html.dark-mode .bg-light { background: #334155 !important; }
html.dark-mode .bg-white { background: #1e293b !important; }

/* Select2 (usado en casi todos los formularios) */
html.dark-mode .select2-container--default .select2-selection--single,
html.dark-mode .select2-container--default .select2-selection--multiple {
    background: #1e293b; border-color: #475569;
}
html.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered { color: #f1f5f9; }
html.dark-mode .select2-container--default .select2-selection__placeholder { color: #64748b; }
html.dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: #334155; border-color: #475569; color: #f1f5f9;
}
html.dark-mode .select2-dropdown { background: #1e293b; border-color: #475569; }
html.dark-mode .select2-search--dropdown .select2-search__field { background: #0f172a; border-color: #475569; color: #f1f5f9; }
html.dark-mode .select2-results__option { color: #e2e8f0; }
html.dark-mode .select2-results__option--highlighted[aria-selected] { background: #1F2ED1 !important; color: #fff !important; }
html.dark-mode .select2-container--default .select2-results__option[aria-selected=true] { background: #334155; }

/* Paginación de Laravel (Bootstrap 5) */
html.dark-mode .pagination .page-link { background: #1e293b; border-color: #475569; color: #e2e8f0; }
html.dark-mode .pagination .page-link:hover { background: #334155; border-color: #64748b; color: #f1f5f9; }
html.dark-mode .pagination .page-item.active .page-link { background: #1F2ED1; border-color: #1F2ED1; color: #fff; }
html.dark-mode .pagination .page-item.disabled .page-link { background: #1e293b; border-color: #334155; color: #64748b; }

/* Checkboxes/radios (color-scheme claro por defecto en muchos navegadores) */
html.dark-mode .form-check-input { background-color: #1e293b; border-color: #475569; }
html.dark-mode .form-check-input:checked { background-color: #1F2ED1; border-color: #1F2ED1; }

/* ── Responsive móvil ── */
@media (max-width: 767px) {
    #header { height: 56px !important; padding: 0 10px !important; }
    #main { margin-top: 60px !important; padding: 15px 20px !important; }
    .pagetitle { flex-direction: column !important; align-items: flex-start !important; gap: 10px !important; }
    .pagetitle .btn { width: 100% !important; }
    .table-responsive { display: block !important; overflow-x: auto !important; }
}
</style>

@stack('styles')

</head>

<body>

@include('shared.header')
@include('shared.aside')

<main id="main" class="main">
    <section class="section">
        @yield('contenido')
    </section>
</main>

@include('shared.footer')

<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
</a>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content');
</script>

<script src="{{ asset('NiceAdmin/assets/js/main.js') }}"></script>

<script>
$(function(){
    if ($('.datatable').length) {
        $('.datatable').DataTable({
            language: {
                emptyTable: "No hay información",
                info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                infoEmpty: "Mostrando 0 a 0 de 0 entradas",
                lengthMenu: "Mostrar _MENU_ entradas",
                search: "Buscar:",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        });
    }

    @if(session('success'))
    Swal.fire({ title:'Éxito', text:@json(session('success')), icon:'success' });
    @endif

    @if(session('error'))
    Swal.fire({ title:'Error', text:@json(session('error')), icon:'error' });
    @endif
});
</script>

@stack('scripts')

<script>
// Dark Mode Toggle
(function () {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    const themeKey = 'nazareno_theme';

    const savedTheme = localStorage.getItem(themeKey) || 'light';
    if (savedTheme === 'dark') {
        htmlElement.classList.add('dark-mode');
        updateThemeIcon();
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            htmlElement.classList.toggle('dark-mode');
            const currentTheme = htmlElement.classList.contains('dark-mode') ? 'dark' : 'light';
            localStorage.setItem(themeKey, currentTheme);
            updateThemeIcon();
        });
    }

    function updateThemeIcon() {
        const icon = themeToggleBtn.querySelector('i');
        if (htmlElement.classList.contains('dark-mode')) {
            icon.className = 'bi bi-sun-fill';
        } else {
            icon.className = 'bi bi-moon-fill';
        }
    }
})();

(function () {
    var key = 'nazareno_tab_activa';
    @if(session('just_logged_in'))
        sessionStorage.setItem(key, '1');
    @else
        if (!sessionStorage.getItem(key)) {
            window.location.replace('/force-logout');
        }
        sessionStorage.setItem(key, '1');
    @endif
})();
</script>

</body>
</html>
