<div>
    @if ($enviado)
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            <p class="font-medium">¡Gracias por registrarte! Pronto alguien de la iglesia se pondrá en contacto contigo.</p>
        </div>
    @endif

    <form wire:submit="guardar" class="space-y-5">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="nombres" class="mb-1.5 block text-sm font-semibold text-stone-700">Nombres</label>
                <input type="text" id="nombres" wire:model="nombres" autocomplete="given-name"
                    class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                @error('nombres') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="apellidos" class="mb-1.5 block text-sm font-semibold text-stone-700">Apellidos</label>
                <input type="text" id="apellidos" wire:model="apellidos" autocomplete="family-name"
                    class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                @error('apellidos') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="telefono" class="mb-1.5 block text-sm font-semibold text-stone-700">Teléfono</label>
                <input type="tel" id="telefono" wire:model="telefono" autocomplete="tel"
                    class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                @error('telefono') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="correo" class="mb-1.5 block text-sm font-semibold text-stone-700">Correo electrónico</label>
                <input type="email" id="correo" wire:model="correo" autocomplete="email"
                    class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                @error('correo') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="direccion" class="mb-1.5 block text-sm font-semibold text-stone-700">Dirección</label>
            <input type="text" id="direccion" wire:model="direccion" autocomplete="street-address"
                class="w-full rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            @error('direccion') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="peticion_oracion" class="mb-1.5 block text-sm font-semibold text-stone-700">¿Tienes alguna petición de oración?</label>
            <textarea id="peticion_oracion" wire:model="peticion_oracion" rows="4"
                class="w-full resize-y rounded-lg border border-stone-300 px-3.5 py-2.5 text-stone-900 placeholder-stone-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30"></textarea>
            @error('peticion_oracion') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="flex w-full items-center justify-center gap-2 rounded-full bg-amber-600 px-6 py-3.5 text-base font-semibold text-white shadow-lg shadow-amber-600/30 transition hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-70">
            <svg wire:loading wire:target="guardar" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span>Enviar</span>
        </button>
    </form>
</div>
