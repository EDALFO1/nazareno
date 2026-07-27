<x-filament-panels::page>
    @php($url = $this->getUrlRegistro())

    {{-- El SVG del QR trae width/height fijos (280); esto lo deja fluido para que no desborde en pantallas angostas. --}}
    <style>
        .qr-code-wrap svg { width: 100%; height: auto; display: block; }
    </style>

    <x-filament::section>
        <div class="flex flex-col items-center gap-6 py-4 text-center">
            <div class="qr-code-wrap w-[280px] max-w-full rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                {!! QrCode::size(280)->format('svg')->generate($url) !!}
            </div>

            <div class="space-y-1">
                <p class="text-sm text-gray-500 dark:text-gray-400">Este QR abre directamente el formulario de registro:</p>
                <p class="break-all font-mono text-sm font-medium text-gray-900 dark:text-gray-100">{{ $url }}</p>
            </div>

            <div class="flex flex-wrap justify-center gap-3 print:hidden">
                <x-filament::button
                    tag="a"
                    :href="$url"
                    target="_blank"
                    icon="heroicon-o-arrow-top-right-on-square"
                    color="gray"
                >
                    Abrir formulario
                </x-filament::button>

                <div x-data="{ copiado: false }">
                    <x-filament::button
                        x-on:click="navigator.clipboard.writeText(@js($url)); copiado = true; setTimeout(() => copiado = false, 1500)"
                        icon="heroicon-o-clipboard-document"
                        color="gray"
                    >
                        <span x-show="!copiado">Copiar enlace</span>
                        <span x-show="copiado" x-cloak>Enlace copiado ✓</span>
                    </x-filament::button>
                </div>

                <x-filament::button
                    icon="heroicon-o-printer"
                    color="gray"
                    x-data
                    x-on:click="window.print()"
                >
                    Imprimir
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="¿Cómo usarlo?" icon="heroicon-o-information-circle" class="print:hidden">
        <ul class="list-disc space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                <span class="font-medium text-gray-900 dark:text-gray-100">Para un voluntario que toma datos en el celular:</span>
                envíale el enlace por WhatsApp (botón "Copiar enlace") o pídele que escanee el QR con la cámara. Se
                abre el formulario público, sin necesidad de iniciar sesión, y después de guardar cada persona el
                formulario queda listo para registrar a la siguiente.
            </li>
            <li>
                <span class="font-medium text-gray-900 dark:text-gray-100">Para dejarlo pegado en la entrada:</span>
                usa el botón "Imprimir" para sacar una hoja solo con el QR y el enlace.
            </li>
            <li>
                <span class="font-medium text-gray-900 dark:text-gray-100">Acceso directo en el celular:</span>
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
    </x-filament::section>
</x-filament-panels::page>
