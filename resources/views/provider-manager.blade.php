<div class="p-6">
    <div class="flex justify-between mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar por RIF o Nombre..." class="form-input rounded-md shadow-sm w-1/3">
        <x-button wire:click="create()" class="bg-indigo-600 text-white px-4 py-2 rounded-md">
            Nuevo Proveedor
        </x-button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">{{ session('message') }}</div>
    @endif

    <table class="table-auto w-full border-collapse bg-white shadow-sm rounded-lg overflow-hidden">
        <thead class="bg-gray-100 text-left text-gray-600 uppercase text-sm">
            <tr>
                <th class="px-4 py-2">RIF</th>
                <th class="px-4 py-2">Nombre</th>
                <th class="px-4 py-2">Contacto</th>
                <th class="px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($providers as $provider)
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-2">{{ $provider->rif }}</td>
                <td class="px-4 py-2">{{ $provider->name }}</td>
                <td class="px-4 py-2">{{ $provider->contact_person }} ({{ $provider->phone }})</td>
                <td class="px-4 py-2">
                    <button wire:click="edit({{ $provider->id }})" class="text-blue-600 hover:underline">Editar</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $providers->links() }}</div>

    @if($is_open)
    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-1/2">
            <h2 class="text-xl font-bold mb-4">{{ $provider_id ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h2>
            <div class="grid grid-cols-2 gap-4">
                <input type="text" wire:model="rif" placeholder="RIF (J-12345678-9)" class="border p-2 rounded">
                <input type="text" wire:model="name" placeholder="Nombre / Razón Social" class="border p-2 rounded">
                <input type="text" wire:model="contact_person" placeholder="Nombre del Vendedor" class="border p-2 rounded">
                <input type="text" wire:model="phone" placeholder="Teléfono" class="border p-2 rounded">
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button wire:click="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded">Cancelar</button>
                <button wire:click="store()" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>
