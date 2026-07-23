<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - Iglesia del Nazareno Ciudad de Dios</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">

    <div class="relative overflow-hidden bg-gradient-to-b from-amber-50 via-stone-50 to-stone-50">
        <div class="pointer-events-none absolute -top-24 right-[-10%] h-80 w-80 rounded-full bg-amber-200/40 blur-3xl"></div>

        <div class="relative mx-auto flex min-h-screen max-w-xl flex-col justify-center px-6 py-16">
            <a href="{{ url('/') }}" class="mx-auto mb-8 flex items-center gap-2 text-sm font-semibold text-stone-600 transition hover:text-amber-700">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white">CD</span>
                Ciudad de Dios
            </a>

            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">¡Nos alegra que nos visites!</h1>
                <p class="mt-2 text-stone-600">Cuéntanos un poco de ti para poder acompañarte.</p>
            </div>

            <div class="mt-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                @livewire('registro-persona-nueva')
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
