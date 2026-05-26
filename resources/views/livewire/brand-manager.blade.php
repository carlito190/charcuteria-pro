<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

            {{-- Encabezado y Barra de Búsqueda --}}
            <div class="flex items-center justify-between mb-6 gap-4">
                <div class="flex-1">
                    <x-input type="text"
                             wire:model.live="search"
                             placeholder="Buscar marca por nombre..."
                             class="w-full" />
                </div>
                <div>
                    <x-button wire:click="create()">
                        {{ __('Nueva Marca') }}
                    </x-button>
                </div>
            </div>

            {{-- Mensajes de Alerta (Éxito o Error de Relación) --}}
            @if (session()->has('message'))
                <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-xl font-bold text-sm">
                    ✅ {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded-xl font-bold text-sm">
                    ❌ {{ session('error') }}
                </div>
            @endif

            {{-- Tabla de Marcas --}}
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3">ID</th>
                            <th class="px-6 py-3">Nombre de la Marca</th>
                            <th class="px-6 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($brands as $brand)
                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-mono text-xs text-gray-400">#{{ $brand->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-sm uppercase">{{ $brand->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button wire:click="edit({{ $brand->id }})" class="text-indigo-600 hover:text-indigo-900 font-bold text-sm bg-indigo-50 px-3 py-1.5 rounded-lg transition">
                                    Editar
                                </button>

                                <button wire:click="delete({{ $brand->id }})" 
                                        onclick="confirm('¿Estás seguro de eliminar esta marca? No se podrá proceder si está vinculada a un producto.') || event.stopImmediatePropagation()"
                                        class="text-red-600 hover:text-red-900 ml-2 bg-red-50 px-3 py-1.5 rounded-lg transition text-sm font-bold">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-400 font-medium">
                                No se encontraron marcas que coincidan con la búsqueda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $brands->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL DE CREACIÓN / EDICIÓN --}}
    <x-dialog-modal wire:model="is_open">
        <x-slot name="title">
            <span class="font-black text-gray-800">{{ $brand_id ? '📝 Editar Marca' : '🏷️ Registrar Nueva Marca' }}</span>
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <x-label value="Nombre del Fabricante / Marca" class="font-bold text-gray-600" />
                    <x-input type="text" class="w-full mt-1" wire:model="name" placeholder="Ej: Plumrose, Kraft, Alimentos Polar..." />
                    <x-input-error for="name" class="mt-1" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeModal()">Cancelar</x-secondary-button>
            <x-button class="ml-3" wire:click="store()">
                Guardar Marca
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>