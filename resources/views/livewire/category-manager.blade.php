<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">Categorías de Productos</h2>
                <x-button wire:click="create()">Nueva Categoría</x-button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($categories as $category)
                    <div class="flex justify-between items-center p-4 border rounded-lg hover:bg-gray-50 transition">
                        <div>
                            <p class="font-bold text-gray-700">{{ $category->name }}</p>
                            <p class="text-xs text-gray-500">{{ $category->description ?? 'Sin descripción' }}</p>
                        </div>
                        <button wire:click="edit({{ $category->id }})" class="text-indigo-600 hover:text-indigo-900">
                            Editar
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-dialog-modal wire:model="is_open">
        <x-slot name="title">{{ $category_id ? 'Editar Categoría' : 'Nueva Categoría' }}</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label value="Nombre de la Categoría (Ej: Embutidos)" />
                    <x-input type="text" class="w-full mt-1" wire:model="name" />
                    <x-input-error for="name" />
                </div>
                <div>
                    <x-label value="Descripción (Opcional)" />
                    <x-input type="text" class="w-full mt-1" wire:model="description" />
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('is_open', false)">Cancelar</x-secondary-button>
            <x-button class="ml-3" wire:click="store()">Guardar</x-button>
        </x-slot>
    </x-dialog-modal>
</div>
