<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

            <div class="flex items-center justify-between mb-6 gap-4">
                <div class="flex-1">
                    <x-input type="text"
                             wire:model.live="search"
                             placeholder="Buscar producto por nombre o código..."
                             class="w-full" />
                </div>
                <div class="w-64">
                    <x-label value="Ver inventario de:" class="text-xs" />
                    <select wire:model.live="viewBranchId" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                        @foreach($all_branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-4 mb-4 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <div class="flex items-center">
                        <label for="lowStock" class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" id="lowStock" wire:model.live="onlyLowStock" class="sr-only">
                                <div class="block bg-gray-200 w-10 h-6 rounded-full {{ $onlyLowStock ? 'bg-red-500' : '' }}"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition {{ $onlyLowStock ? 'transform translate-x-4' : '' }}"></div>
                            </div>
                            <div class="ml-3 text-gray-700 font-medium text-sm">
                                ⚠️(< 5kg)
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <x-button wire:click="create()">
                        {{ __('Nuevo Producto') }}
                    </x-button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3">Producto</th>
                            <th class="px-6 py-3">Categoría</th>
                            <th class="px-6 py-3">Stock Sede Actual</th>
                            <th class="p-3 text-center bg-gray-50">Stock Global (Total)</th>
                            <th class="px-6 py-3 text-right">Costo ($)</th>
                            <th class="px-6 py-3 text-right">Venta ($)</th>
                            <th class="px-6 py-3 text-right text-blue-600">Venta (Bs)</th>
                            <th class="px-6 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr class="bg-white text-center border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-400">{{ $product->barcode ?? 'Sin código' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">
                                    {{ $product->category->name }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                @php
                                    $branchData = $product->branches->first();
                                    $stock = $branchData ? $branchData->stock : 0;
                                    // Buscamos el nombre de la sede actual seleccionada
                                    $currentBranchName = $all_branches->find($viewBranchId)->name;
                                @endphp

                                    <span class="px-2 py-1 rounded-full font-bold {{ $stock <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ number_format($stock, 3) }}
                                    </span>
                                    @if($stock <= 5)
                                        <span class="text-[10px] text-red-500 font-bold uppercase mt-1">¡Reponer ya!</span>
                                    @endif
                                    <span class="text-xs text-gray-400 block uppercase font-semibold">En {{ $currentBranchName }}</span>
                            </td>
                            <td class="p-3 text-center bg-indigo-50">
                                <span class="text-lg font-black text-indigo-700">
                                    {{ number_format($product->total_stock ?? 0, 3) }}
                                </span>
                                <span class="text-xs block text-indigo-400 font-bold uppercase">Kilos/Unid Totales</span>
                            </td>
                            <td class="px-6 py-4 text-right">${{ number_format($product->cost_usd, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-700">
                                ${{ number_format($product->selling_price_usd, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-blue-600 bg-blue-50">
                                {{ number_format($product->price_bs, 2) }} Bs.
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="edit({{ $product->id }})" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                    Editar
                                </button>

                            <button wire:click="openTransfer({{ $product->id }})" class="text-indigo-600 hover:text-indigo-900 ml-4">
                                <span class="text-xs font-bold uppercase">Transferir</span>
                            </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <x-dialog-modal wire:model="is_open">
        <x-slot name="title">{{ $product_id ? 'Editar Producto' : 'Nuevo Producto' }}</x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <x-label value="Nombre del Producto" />
                    <x-input type="text" class="w-full mt-1" wire:model="name" />
                </div>
                <div>
                    <x-label value="Categoría" />
                    <select wire:model="category_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1">
                        <option value="">Seleccione...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-label value="Código de Barras" />
                    <x-input type="text" class="w-full mt-1" wire:model="barcode" />
                </div>
                <div>
                    <x-label value="Costo en USD ($)" />
                    <x-input type="number" step="0.01" class="w-full mt-1" wire:model="cost_usd" />
                </div>
                <div>
                    <x-label value="% Margen de Ganancia" />
                    <x-input type="number" class="w-full mt-1" wire:model="profit_margin" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeModal()">Cancelar</x-secondary-button>
            <x-button class="ml-3" wire:click="store()">Guardar Producto</x-button>
        </x-slot>
    </x-dialog-modal>

    <x-dialog-modal wire:model.live="showTransferModal">
        <x-slot name="title">
            {{ __('Transferir Mercancía') }}: <span class="text-indigo-600">{{ $selectedProduct->name ?? '' }}</span>
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="fromBranchId" value="{{ __('Desde Sucursal') }}" />
                    <select id="fromBranchId" wire:model.live="fromBranchId" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                        <option value="">Seleccione origen...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="fromBranchId" class="mt-2" />
                </div>

                <div>
                    <x-label for="toBranchId" value="{{ __('Hacia Sucursal') }}" />
                    <select id="toBranchId" wire:model.live="toBranchId" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                        <option value="">Seleccione destino...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="toBranchId" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="transferAmount" value="{{ __('Cantidad a mover (Kg/Unid)') }}" />
                    <x-input id="transferAmount" type="number" step="0.001" class="mt-1 block w-full" wire:model="transferAmount" placeholder="Ej: 5.500" />
                    <x-input-error for="transferAmount" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showTransferModal', false)" wire:loading.attr="disabled">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-button class="ml-3 bg-indigo-600" wire:click="executeTransfer" wire:loading.attr="disabled">
                {{ __('Confirmar Transferencia') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
