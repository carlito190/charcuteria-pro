<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="max-w-md mx-auto">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Gestión de Tasa de Cambio</h2>

                <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tasa Actual BCV</p>
                            <p class="text-4xl font-black text-indigo-600">
                                {{ number_format($currentRate, 2) }} <span class="text-sm font-normal">Bs/$</span>
                            </p>
                        </div>

                   <x-button wire:click="fetchFromApi"
                            type="button"
                            class="relative inline-flex items-center justify-center p-4 bg-red-600 text-white rounded-full shadow-lg z-50 min-w-[50px] min-h-[50px]">

                        <div wire:loading.remove>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>

                        <div wire:loading>
                            <span class="text-xs font-bold">Cargando...</span>
                        </div>
                    BCV</x-button>
                    </div>

                    <div class="space-y-3">
                        <x-label value="Valor de la Tasa" />
                        <div class="flex gap-2">
                            <x-input type="number" step="0.0001" wire:model="manualRate" class="flex-1" />
                            <x-button wire:click="saveManual">
                                {{ __('Guardar') }}
                            </x-button>
                        </div>
                        <x-input-error for="manualRate" />
                    </div>

                    @if (session()->has('message'))
                        <div class="mt-4 p-2 bg-green-100 text-green-700 text-xs rounded-lg text-center font-bold">
                            {{ session('message') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
