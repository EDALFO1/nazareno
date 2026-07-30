@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle">
    <h1 class="mb-0"><i class="bi bi-qr-code me-2"></i>Código QR para registro de personas</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item active">QR de registro</li>
        </ol>
    </nav>
</div>

<section class="section mt-3">

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-4">
        <div class="d-inline-block bg-white p-3 rounded-4 shadow-sm border" style="width: 280px; max-width: 100%;">
            {!! QrCode::size(280)->format('svg')->generate($urlRegistro) !!}
        </div>

        <p class="text-muted small mt-3 mb-1">Este QR abre directamente el formulario de registro:</p>
        <p class="font-monospace small fw-semibold text-break">{{ $urlRegistro }}</p>

        <div class="d-flex flex-wrap justify-content-center gap-2 mt-3 print-hide">
            <a href="{{ $urlRegistro }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i>Abrir formulario
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copiarEnlaceQr()">
                <i class="bi bi-clipboard me-1"></i><span id="qr-copy-label">Copiar enlace</span>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimir
            </button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3 print-hide">
    <div class="card-header bg-transparent fw-semibold"><i class="bi bi-info-circle me-1"></i>¿Cómo usarlo?</div>
    <div class="card-body">
        <ul class="mb-0">
            <li class="mb-2">
                <strong>Para un voluntario que toma datos en el celular:</strong>
                envíale el enlace por WhatsApp (botón "Copiar enlace") o pídele que escanee el QR con la cámara. Se
                abre el formulario público, sin necesidad de iniciar sesión, y después de guardar cada persona el
                formulario queda listo para registrar a la siguiente.
            </li>
            <li class="mb-2">
                <strong>Para dejarlo pegado en la entrada:</strong>
                usa el botón "Imprimir" para sacar una hoja solo con el QR y el enlace.
            </li>
            <li class="mb-2">
                <strong>Acceso directo en el celular:</strong>
                una vez abierto el formulario en el navegador del celular, la persona puede usar la opción "Agregar a
                pantalla de inicio" del navegador para dejarlo como un ícono, sin tener que escanear el QR cada vez.
            </li>
            <li>
                Este enlace depende de <code>APP_URL</code> en el archivo <code>.env</code>. Ahora mismo apunta a
                <code>{{ config('app.url') }}</code>, que solo funciona desde este mismo computador. Para que el QR
                funcione desde un celular necesitas que el sitio sea accesible desde afuera: por la misma red WiFi
                (usando la IP local del computador en vez de "localhost"), o desde un dominio real una vez el sistema
                esté publicado en internet.
            </li>
        </ul>
    </div>
</div>

</section>

@push('styles')
<style>
.qr-code-wrap svg, [style*="width: 280px"] svg { width: 100%; height: auto; display: block; }
@media print {
    .print-hide, #header, #sidebar, #footer { display: none !important; }
    #main { margin: 0 !important; padding: 0 !important; }
}
</style>
@endpush

@push('scripts')
<script>
function copiarEnlaceQr() {
    navigator.clipboard.writeText(@json($urlRegistro));
    const label = document.getElementById('qr-copy-label');
    label.textContent = 'Enlace copiado ✓';
    setTimeout(() => { label.textContent = 'Copiar enlace'; }, 1500);
}
</script>
@endpush

@endsection
