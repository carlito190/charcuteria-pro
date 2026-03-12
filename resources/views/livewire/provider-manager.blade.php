<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

            <div class="flex items-center justify-between mb-6 gap-4">
                <div class="flex-1">
                    <x-input type="text"
                             wire:model.live="search"
                             placeholder="Buscar por RIF o Nombre..."
                             class="w-full" />
                </div>

                <div>
                    <x-button wire:click="create()" class="whitespace-nowrap">
                        {{ __('Nuevo Proveedor') }}
                    </x-button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm  text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr class="text-left">
                            <th class="px-6 py-3">RIF</th>
                            <th class="px-6 py-3">Nombre</th>
                            <th class="px-6 py-3">Contacto</th>
                            <th class="px-6 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($providers as $provider)
                        <tr class="bg-white border-b hover:bg-gray-50 transition text-center">
                            <td class="px-6 py-4 font-medium">{{ $provider->rif }}</td>
                            <td class="px-6 py-4">{{ $provider->name }}</td>
                            <td class="px-6 py-4">
                                <span class="block text-xs text-gray-400">{{ $provider->contact_person }}</span>
                                {{ $provider->phone }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <x-danger-button wire:click="edit({{ $provider->id }})" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                    Editar
                                </x-danger-button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $providers->links() }}
            </div>

        </div>
    </div>

            @if($is_open)


            <x-dialog-modal wire:model="is_open">
                <x-slot name="title">
                    {{ $provider_id ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
                </x-slot>

                <x-slot name="content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="rif" value="RIF" />
                            <x-input id="rif" type="text" class="mt-1 block w-full" wire:model="rif" />
                            <x-input-error for="rif" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="name" value="Nombre" />
                            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                            <x-input-error for="name" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="contact_person" value="Contacto" />
                            <x-input id="contact_person" type="text" class="mt-1 block w-full" wire:model="contact_person" />
                            <x-input-error for="contact_person" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="phone" value="Teléfono" />
                            <x-input id="phone" type="text" class="mt-1 block w-full" wire:model="phone" />
                            <x-input-error for="phone" class="mt-2" />
                        </div>
                    </div>
                </x-slot>

                <x-slot name="footer">
                    <x-secondary-button wire:click="closeModal()" wire:loading.attr="disabled">
                        Cancelar
                    </x-secondary-button>

                    <x-button class="ml-3" wire:click="store()" wire:loading.attr="disabled">
                        Guardar
                    </x-button>
                </x-slot>
            </x-dialog-modal>
            @endif

</div>
