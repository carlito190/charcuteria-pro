<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">Sedes / Sucursales</h2>
                <x-button wire:click="create()">Nueva Sucursal</x-button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($branches as $branch)
                    <div class="border rounded-lg p-4 shadow-sm hover:shadow-md transition bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-lg text-indigo-700">{{ $branch->name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">📍 {{ $branch->address ?? 'Sin dirección' }}</p>
                                <p class="text-sm text-gray-600">📞 {{ $branch->phone ?? 'Sin teléfono' }}</p>
                            </div>
                            <button wire:click="edit({{ $branch->id }})" class="text-gray-400 hover:text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-dialog-modal wire:model="is_open">
        <x-slot name="title">{{ $branch_id ? 'Editar Sede' : 'Registrar Nueva Sede' }}</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label value="Nombre de la Sucursal" />
                    <x-input type="text" class="w-full" wire:model="name" placeholder="Ej: Sede Principal" />
                </div>
                <div>
                    <x-label value="Dirección" />
                    <x-input type="text" class="w-full" wire:model="address" />
                </div>
                <div>
                    <x-label value="Teléfono de Contacto" />
                    <x-input type="text" class="w-full" wire:model="phone" placeholder="Ej: +58 412-3456789" />
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('is_open', false)">Cancelar</x-secondary-button>
            <x-button class="ml-3" wire:click="store()">Guardar</x-button>
        </x-slot>
    </x-dialog-modal>
</div>
