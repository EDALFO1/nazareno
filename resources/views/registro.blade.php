<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - Iglesia del Nazareno Ciudad de Dios</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">

    <div class="relative overflow-hidden bg-gradient-to-b from-amber-50 via-stone-50 to-stone-50">
        <div class="pointer-events-none absolute -top-24 right-[-10%] h-80 w-80 rounded-full bg-amber-200/40 blur-3xl"></div>

        <div class="relative mx-auto flex min-h-screen max-w-xl flex-col justify-center px-6 py-16">
            <a href="{{ url('/') }}" class="mx-auto mb-8 flex items-center gap-2 text-sm font-semibold text-stone-600 transition hover:text-amber-700">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white">INP</span>
                Ciudad de Dios
            </a>

            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">¡Nos alegra que nos visites!</h1>
                <p class="mt-2 text-stone-600">Cuéntanos un poco de ti para poder acompañarte.</p>
            </div>

            <div class="mt-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">

                @if (session('enviado'))
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        <p class="font-medium">¡Gracias por registrarte! Pronto alguien de la iglesia se pondrá en contacto contigo.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('registro.store') }}" class="space-y-5">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="nombres" class="mb-1.5 block text-sm font-semibold text-stone-700">Nombres</label>
                            <input type="text" id="nombres" name="nombres" value="{{ old('nombres') }}" autocomplete="given-name"
                                class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('nombres') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="apellidos" class="mb-1.5 block text-sm font-semibold text-stone-700">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" autocomplete="family-name"
                                class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('apellidos') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="tipo_documento" class="mb-1.5 block text-sm font-semibold text-stone-700">Tipo de documento</label>
                            <select id="tipo_documento" name="tipo_documento"
                                class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                                <option value="">Seleccione una opción</option>
                                @foreach (\App\Models\Persona::TIPOS_DOCUMENTO as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected(old('tipo_documento') === $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            @error('tipo_documento') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="numero_documento" class="mb-1.5 block text-sm font-semibold text-stone-700">Número de documento</label>
                            <input type="text" id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}" inputmode="numeric" autocomplete="off"
                                class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('numero_documento') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="telefono" class="mb-1.5 block text-sm font-semibold text-stone-700">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" autocomplete="tel"
                                class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('telefono') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="correo" class="mb-1.5 block text-sm font-semibold text-stone-700">Correo electrónico</label>
                            <input type="email" id="correo" name="correo" value="{{ old('correo') }}" autocomplete="email"
                                class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('correo') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="direccion" class="mb-1.5 block text-sm font-semibold text-stone-700">Dirección</label>
                        <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" autocomplete="street-address"
                            class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                        @error('direccion') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="genero" class="mb-1.5 block text-sm font-semibold text-stone-700">Género</label>
                            <select id="genero" name="genero"
                                class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                                <option value="">Seleccione una opción</option>
                                <option value="masculino" @selected(old('genero') === 'masculino')>Masculino</option>
                                <option value="femenino" @selected(old('genero') === 'femenino')>Femenino</option>
                            </select>
                            @error('genero') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="fecha_nacimiento" class="mb-1.5 block text-sm font-semibold text-stone-700">Fecha de nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                                class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('fecha_nacimiento') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="fecha_primera_visita" class="mb-1.5 block text-sm font-semibold text-stone-700">Fecha de primera visita</label>
                        <input type="date" id="fecha_primera_visita" name="fecha_primera_visita" value="{{ old('fecha_primera_visita', now()->toDateString()) }}"
                            class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                        @error('fecha_primera_visita') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="peticion_oracion" class="mb-1.5 block text-sm font-semibold text-stone-700">¿Tienes alguna petición de oración?</label>
                        <textarea id="peticion_oracion" name="peticion_oracion" rows="4"
                            class="w-full resize-y rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">{{ old('peticion_oracion') }}</textarea>
                        @error('peticion_oracion') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
                        <p class="mb-3 text-sm font-semibold text-stone-700">Datos del acudiente <span class="font-normal text-stone-500">(solo si eres menor de edad)</span></p>
                        <div class="grid gap-5 sm:grid-cols-3">
                            <div>
                                <label for="acudiente" class="mb-1.5 block text-sm font-semibold text-stone-700">Nombre del acudiente</label>
                                <input type="text" id="acudiente" name="acudiente" value="{{ old('acudiente') }}"
                                    class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                                @error('acudiente') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="telefono_acudiente" class="mb-1.5 block text-sm font-semibold text-stone-700">Teléfono del acudiente</label>
                                <input type="tel" id="telefono_acudiente" name="telefono_acudiente" value="{{ old('telefono_acudiente') }}"
                                    class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                                @error('telefono_acudiente') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="parentesco" class="mb-1.5 block text-sm font-semibold text-stone-700">Parentesco</label>
                                <select id="parentesco" name="parentesco"
                                    class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                                    <option value="">Seleccione una opción</option>
                                    @foreach(['padre'=>'Padre','madre'=>'Madre','conyuge'=>'Cónyuge','abuelo_a'=>'Abuelo/a','tio_a'=>'Tío/a','hermano_a'=>'Hermano/a','tutor_legal'=>'Tutor legal','otro'=>'Otro'] as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected(old('parentesco') === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                @error('parentesco') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" name="autorizoDatos" value="1"
                                class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-amber-600 focus:ring-amber-500/30">
                            <span class="text-sm text-stone-600">
                                {{ config('app.name') }} es el Responsable del tratamiento de sus datos. Al marcar la casilla de
                                aceptación, usted autoriza de manera libre, voluntaria y expresa el uso de los datos aquí
                                registrados con la finalidad exclusiva de gestionar el registro de asistencia, brindar
                                acompañamiento pastoral y enviar invitaciones a nuestros cultos o actividades comunitarias. Se le
                                informa que este registro puede revelar de forma indirecta su afiliación religiosa (considerado
                                un dato sensible bajo la Ley 1581 de 2012) y que su aceptación es totalmente facultativa. Como
                                titular, puede solicitar en cualquier momento la consulta, corrección o eliminación de sus datos
                                enviando una solicitud al correo electrónico: {{ config('app.correo_datos_personales') }}.
                                <span class="text-rose-600">*</span>
                                <br>
                                <span class="text-xs text-stone-500">Si estás registrando a un menor de edad, esta autorización debes darla tú como su acudiente.</span>
                            </span>
                        </label>
                        @error('autorizoDatos') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-amber-600 px-6 py-3.5 text-base font-semibold text-white shadow-lg shadow-amber-600/30 transition hover:bg-amber-700">
                        <span>Enviar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
