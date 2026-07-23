<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Iglesia del Nazareno Ciudad de Dios') }}</title>
    <meta name="description" content="Iglesia del Nazareno Ciudad de Dios — una familia de fe donde encontrarás propósito, comunidad y crecimiento.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-stone-900 antialiased">

    <header class="sticky top-0 z-30 border-b border-stone-200 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-lg font-semibold tracking-tight text-stone-900">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-600 text-sm font-bold text-white">CD</span>
                Ciudad de Dios
            </a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-stone-600 md:flex">
                <a href="#nosotros" class="transition hover:text-amber-700">Nosotros</a>
                <a href="#redes" class="transition hover:text-amber-700">Redes</a>
                <a href="#procesos" class="transition hover:text-amber-700">Procesos</a>
            </nav>
            <div class="flex items-center gap-4">
                <a href="/admin" class="hidden text-sm font-medium text-stone-500 transition hover:text-stone-800 sm:inline">Acceso administrativo</a>
                <a href="{{ route('registro') }}" class="inline-flex items-center rounded-full bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/30 transition hover:bg-amber-700">
                    Regístrate
                </a>
            </div>
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden bg-gradient-to-b from-amber-50 via-stone-50 to-stone-50">
            <div class="pointer-events-none absolute -top-24 right-[-10%] h-96 w-96 rounded-full bg-amber-200/40 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 left-[-10%] h-96 w-96 rounded-full bg-amber-100/60 blur-3xl"></div>

            <div class="relative mx-auto max-w-6xl px-6 py-24 text-center sm:py-32">
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-white px-4 py-1.5 text-sm font-medium text-amber-800">
                    Bienvenido a nuestra comunidad de fe
                </span>
                <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-bold tracking-tight text-stone-900 sm:text-5xl lg:text-6xl">
                    Iglesia del Nazareno
                    <span class="text-amber-700">Ciudad de Dios</span>
                </h1>
                <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-stone-600">
                    Un lugar para crecer en la fe, construir comunidad y caminar juntos en cada etapa del proceso.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('registro') }}" class="inline-flex w-full items-center justify-center rounded-full bg-amber-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-amber-600/30 transition hover:bg-amber-700 sm:w-auto">
                        Regístrate aquí
                    </a>
                    <a href="#nosotros" class="inline-flex w-full items-center justify-center rounded-full border border-stone-300 bg-white px-8 py-3.5 text-base font-semibold text-stone-700 transition hover:border-stone-400 hover:bg-stone-50 sm:w-auto">
                        Conócenos
                    </a>
                </div>
            </div>
        </section>

        <section id="nosotros" class="mx-auto max-w-6xl px-6 py-20">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-amber-700">Quiénes somos</h2>
                <p class="mt-3 text-3xl font-bold tracking-tight text-stone-900">Una familia que camina en fe, juntos</p>
                <p class="mt-4 text-lg leading-relaxed text-stone-600">
                    Creemos en el poder de la comunidad: acompañar a cada persona desde su primera visita
                    hasta convertirse en discípulo y líder que impacta a otros.
                </p>
            </div>

            <div class="mt-14 grid gap-8 sm:grid-cols-3">
                <div class="rounded-2xl border border-stone-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-2.7-9-6.3-9-11.25A5.25 5.25 0 0 1 12 6.75a5.25 5.25 0 0 1 9 3A11.25 11.25 0 0 1 12 21Z" /></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-stone-900">Acogida</h3>
                    <p class="mt-2 text-sm text-stone-600">Cada persona nueva es recibida y acompañada desde el primer momento.</p>
                </div>
                <div class="rounded-2xl border border-stone-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-stone-900">Comunidad</h3>
                    <p class="mt-2 text-sm text-stone-600">Redes y puntos de conexión donde construir relaciones reales.</p>
                </div>
                <div class="rounded-2xl border border-stone-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-stone-900">Discipulado</h3>
                    <p class="mt-2 text-sm text-stone-600">Un camino claro de crecimiento, paso a paso, con acompañamiento.</p>
                </div>
            </div>
        </section>

        <section id="redes" class="bg-white py-20">
            <div class="mx-auto max-w-6xl px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-amber-700">Redes</h2>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-stone-900">Encuentra tu lugar</p>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2">
                    <div class="rounded-2xl bg-stone-900 p-8 text-white">
                        <h3 class="text-xl font-semibold">Red de Hombres</h3>
                        <p class="mt-2 text-stone-300">Un espacio para crecer, servir y fortalecer el liderazgo espiritual.</p>
                    </div>
                    <div class="rounded-2xl bg-amber-700 p-8 text-white">
                        <h3 class="text-xl font-semibold">Red de Mujeres</h3>
                        <p class="mt-2 text-amber-100">Comunidad de apoyo, formación y crecimiento en la fe.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="procesos" class="mx-auto max-w-6xl px-6 py-20">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-amber-700">Procesos</h2>
                <p class="mt-3 text-3xl font-bold tracking-tight text-stone-900">Tu camino de crecimiento</p>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    ['n' => '1', 't' => 'Encuentro'],
                    ['n' => '2', 't' => 'Sanidad Integral'],
                    ['n' => '3', 't' => 'Discipulado I'],
                    ['n' => '4', 't' => 'Discipulado II'],
                    ['n' => '5', 't' => 'Discipulado III'],
                ] as $paso)
                    <div class="rounded-xl border border-stone-200 bg-white p-6 text-center transition hover:border-amber-300 hover:shadow-md">
                        <span class="text-2xl font-bold text-amber-600">{{ $paso['n'] }}</span>
                        <p class="mt-2 text-sm font-semibold text-stone-800">{{ $paso['t'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-stone-900">
            <div class="mx-auto max-w-4xl px-6 py-20 text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white">¿Es tu primera vez con nosotros?</h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-stone-300">
                    Cuéntanos un poco de ti para poder acompañarte desde hoy.
                </p>
                <a href="{{ route('registro') }}" class="mt-8 inline-flex items-center justify-center rounded-full bg-amber-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-amber-600/30 transition hover:bg-amber-500">
                    Completar registro
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-stone-200 bg-white py-10">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 text-sm text-stone-500 sm:flex-row">
            <p>&copy; {{ now()->year }} Iglesia del Nazareno Ciudad de Dios. Todos los derechos reservados.</p>
            <a href="/admin" class="font-medium text-stone-500 transition hover:text-amber-700">Acceso administrativo</a>
        </div>
    </footer>

</body>
</html>
