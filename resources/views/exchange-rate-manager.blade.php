<div class="p-4 bg-white shadow rounded-lg">
    <h3 class="text-lg font-bold">Tasa del Día: <span class="text-blue-600">{{ $currentRate }} Bs.</span></h3>

    <div class="mt-4 flex gap-2">
        <button wire:click="fetchFromApi" class="bg-green-500 text-white px-4 py-2 rounded">
            Actualizar desde BCV
        </button>

        <div class="flex border rounded">
            <input type="number" wire:model="manualRate" step="0.01" placeholder="Tasa manual" class="p-2 border-none">
            <button wire:click="saveManual" class="bg-gray-800 text-white px-4 py-2">
                Guardar
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mt-2 text-green-600 text-sm">{{ session('message') }}</div>
    @endif
</div>
