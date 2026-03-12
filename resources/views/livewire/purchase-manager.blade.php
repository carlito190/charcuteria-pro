<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow-xl rounded-lg">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Registrar Compra (Entrada de Mercancía)</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="relative">
                    <x-label value="Proveedor" />
                    <x-input type="text" class="w-full" placeholder="Buscar proveedor..." wire:model.live="searchProvider" />

                    @if(!empty($providerResults))
                        <div class="absolute z-50 w-full bg-white border rounded-md shadow-lg mt-1">
                            @foreach($providerResults as $p)
                                <button wire:click="selectProvider({{ $p->id }}, '{{ $p->name }}')"
                                        class="w-full text-left px-4 py-2 hover:bg-gray-100 border-b text-sm">
                                    {{ $p->name }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if($provider_id)
                        <div class="mt-1 text-xs text-blue-600 font-bold">
                            Seleccionado: {{ $selectedProviderName }}
                        </div>
                    @endif
                    <x-input-error for="provider_id" />
                </div>

                <div>
                    <x-label value="Sucursal de Destino" />
                    <select wire:model="branch_id" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Seleccione...</option>
                        @foreach($branches as $b) <option value="{{$b->id}}">{{$b->name}}</option> @endforeach
                    </select>
                </div>
                <div>
                    <x-label value="Nro Factura" />
                    <x-input type="text" class="w-full" wire:model="invoice_number" />
                </div>
                <div>
                    <x-label value="Fecha" />
                    <x-input type="date" class="w-full" wire:model="purchase_date" />
                </div>
            </div>

            <div class="relative mb-6 bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300">
                <x-label value="Añadir Producto a la factura" class="text-indigo-700 font-bold" />
                <x-input type="text"
                         class="w-full mt-1"
                         placeholder="Escribe el nombre del producto (ej: Jamón, Queso...)"
                         wire:model.live="searchProduct" />

                @if(!empty($searchResults))
                    <div class="absolute z-50 w-full bg-white border rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
                        @foreach($searchResults as $product)
                            <button wire:click="selectProduct({{ $product->id }}, '{{ $product->name }}')"
                                    class="w-full text-left px-4 py-3 hover:bg-indigo-50 border-b flex justify-between">
                                <span>{{ $product->name }}</span>
                                <span class="text-gray-400 text-xs">Añadir +</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full mb-4">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="p-3 text-left">Producto</th>
                            <th class="p-3 text-center" width="150">Cant (Kg/Unid)</th>
                            <th class="p-3 text-right" width="150">Costo Unit ($)</th>
                            <th class="p-3 text-right" width="150">Subtotal</th>
                            <th class="p-3" width="50"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">
                                <span class="font-bold text-gray-800 uppercase text-sm">{{ $item['name'] }}</span>
                                <input type="hidden" wire:model="items.{{$index}}.product_id">
                            </td>
                            <td class="p-3">
                                <x-input type="number" step="0.001" class="w-full text-center" wire:model.live="items.{{$index}}.quantity" />
                            </td>
                            <td class="p-3">
                                <x-input type="number" step="0.01" class="w-full text-right" wire:model.live="items.{{$index}}.cost_unit_usd" />
                            </td>
                            <td class="p-3 text-right font-bold text-gray-700">
                                ${{ number_format($item['subtotal'], 2) }}
                            </td>
                            <td class="p-3 text-center">
                                <button wire:click="removeDetail({{$index}})" class="text-red-500 hover:text-red-700 font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 italic">
                                No hay productos añadidos. Usa el buscador de arriba.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Artículos en factura: {{ count($items) }}
                </div>
                <div class="text-right">
                    <span class="text-gray-600 text-lg">Total Factura:</span>
                    <span wire:loading.class="opacity-50" class="text-4xl font-black text-indigo-600 ml-2">
                        ${{ number_format($total, 2) }}
                    </span>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <x-button wire:click="save" class="px-10 py-4 bg-green-600 hover:bg-green-700 shadow-lg">
                    Procesar Compra y Cargar Stock
                </x-button>
            </div>
        </div>
    </div>
</div>
