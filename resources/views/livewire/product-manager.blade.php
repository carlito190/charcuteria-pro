<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

            {{-- Buscadores y Filtros --}}
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

            {{-- Listado en Tabla --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-left text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            {{-- Modificamos la cabecera para albergar la foto --}}
                            <th class="px-6 py-3 text-center">Foto</th>
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
                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                            
                            {{-- 📸 COLUMNA 1: MINIATURA DE FOTO DEL PRODUCTO --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-lg bg-gray-100 border overflow-hidden shadow-sm">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[10px] text-gray-400 font-bold uppercase">Sin Foto</span>
                                    @endif
                                </div>
                            </td>

                            {{-- PRODUCTO Y MARCA --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-sm">{{ $product->name }}</div>
                                {{-- Si el producto tiene marca asociada, la mostramos elegantemente abajo --}}
                                @if($product->brand)
                                    <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mt-0.5">{{ $product->brand->name }}</div>
                                @else
                                    <div class="text-xs text-gray-400 italic">Sin marca registrada</div>
                                @endif
                                <div class="text-[11px] text-gray-400 mt-1">📌 {{ $product->barcode ?? 'Sin código' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">
                                    {{ $product->category->name }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                @php
                                    $branchData = $product->branches->first();
                                    $stock = $branchData ? $branchData->stock : 0;
                                    $currentBranchName = $all_branches->find($viewBranchId)->name;
                                @endphp

                                <span class="px-2 py-1 rounded-full font-bold {{ $stock <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ number_format($stock, 3) }}
                                </span>
                                @if($stock <= 5)
                                    <span class="text-[10px] text-red-500 font-bold uppercase mt-1 block">¡Reponer ya!</span>
                                @endif
                                <span class="text-[10px] text-gray-400 block uppercase font-semibold mt-1">En {{ $currentBranchName }}</span>
                            </td>
                            <td class="p-3 text-center bg-indigo-50">
                                <span class="text-lg font-black text-indigo-700">
                                    {{ number_format($product->total_stock ?? 0, 3) }}
                                </span>
                                <span class="text-[10px] block text-indigo-400 font-bold uppercase">Disponibilidad total</span>
                            </td>
                            <td class="px-6 py-4 text-right">${{ number_format($product->cost_usd, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-700">
                                ${{ number_format($product->selling_price_usd, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-blue-600 bg-blue-50">
                                {{ number_format($product->price_bs, 2) }} 
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button wire:click="edit({{ $product->id }})" class="text-indigo-600 hover:text-indigo-900 font-bold text-sm bg-indigo-50 px-3 py-1.5 rounded-lg transition">
                                    Editar
                                </button>

                                <button wire:click="openTransfer({{ $product->id }})" class="text-gray-600 hover:text-gray-900 ml-2 bg-gray-100 px-3 py-1.5 rounded-lg transition text-xs font-bold uppercase">
                                    Transferir
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

    {{-- MODAL DE CREACIÓN / EDICIÓN --}}
    <x-dialog-modal wire:model="is_open">
        <x-slot name="title">
            <span class="font-black text-gray-800">{{ $product_id ? '📝 Editar Datos del Producto' : '🚀 Registrar Nuevo Producto' }}</span>
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Nombre Completo --}}
                <div class="col-span-2">
                    <x-label value="Nombre del Producto" class="font-bold text-gray-600" />
                    <x-input type="text" class="w-full mt-1" wire:model="name" placeholder="Ej: Jamón Ahumado Superior" />
                    <x-input-error for="name" class="mt-1" />
                </div>
                
                {{-- Categoría Relacional --}}
                <div>
                    <x-label value="Categoría" class="font-bold text-gray-600" />
                    <select wire:model="category_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 text-sm">
                        <option value="">Seleccione...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="category_id" class="mt-1" />
                </div>

                {{-- 🏷️ NUEVO CAMPO: MARCAS POR SEPARADO --}}
                <div>
                    <x-label value="Marca / Fabricante" class="font-bold text-gray-600" />
                    <select wire:model="brand_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 text-sm bg-white">
                        <option value="">-- Marca Genérica / Ninguna --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="brand_id" class="mt-1" />
                </div>

                {{-- Código de Barras --}}
                <div>
                    <x-label value="Código de Barras (Opcional)" class="font-bold text-gray-600" />
                    <x-input type="text" class="w-full mt-1 text-sm" wire:model="barcode" placeholder="Dejar vacío para auto-generar" />
                    <x-input-error for="barcode" class="mt-1" />
                </div>
                
                {{-- Costo Monetario --}}
                <div>
                    <x-label value="Costo en USD ($)" class="font-bold text-gray-600" />
                    <x-input type="number" step="0.01" class="w-full mt-1" wire:model="cost_usd" />
                    <x-input-error for="cost_usd" class="mt-1" />
                </div>
                
                {{-- Margen Operativo --}}
                <div>
                    <x-label value="% Margen de Ganancia" class="font-bold text-gray-600" />
                    <x-input type="number" class="w-full mt-1" wire:model="profit_margin" />
                    <x-input-error for="profit_margin" class="mt-1" />
                </div>

                {{-- 📸 NUEVA SECCIÓN: SUBIDA DINÁMICA DE LA FOTO --}}
                <div class="col-span-2 mt-2 bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                    <x-label value="Foto del Producto" class="font-black text-gray-700 uppercase tracking-wide text-xs mb-2" />
                    
                    <div class="flex items-center gap-4">
                        {{-- Input nativo modificado para estilizarse con Jetstream --}}
                        <input type="file" wire:model="image" id="upload-{{ $product_id ?? 'new' }}" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        
                        {{-- Área de Previsualización Inteligente --}}
                        <div class="w-20 h-20 rounded-xl bg-white border shadow-inner overflow-hidden flex items-center justify-center relative flex-shrink-0">
                            @if ($image)
                                <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                <span class="absolute bottom-0 inset-x-0 bg-emerald-600 text-[9px] text-white font-bold text-center py-0.5 uppercase tracking-widest">Nueva</span>
                            @elseif($existing_image)
                                <img src="{{ asset('storage/' . $existing_image) }}" class="w-full h-full object-cover">
                                <span class="absolute bottom-0 inset-x-0 bg-indigo-600 text-[9px] text-white font-bold text-center py-0.5 uppercase tracking-widest">Actual</span>
                            @else
                                <div class="text-center p-1">
                                    <span class="text-[20px] block">🖼️</span>
                                    <span class="text-[9px] text-gray-400 font-bold uppercase block">Sin foto</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Feedback visual de carga asíncrona --}}
                    <div wire:loading wire:target="image" class="text-xs text-indigo-600 font-bold mt-2 flex items-center gap-1 animate-pulse">
                        ⏳ Subiendo archivo al servidor, espere...
                    </div>
                    <x-input-error for="image" class="mt-1" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeModal()">Cancelar</x-secondary-button>
            <x-button class="ml-3" wire:click="store()" wire:loading.attr="disabled" wire:target="image">
                Guardar Producto
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- MODAL DE TRANSFERENCIA ENTRE SEDES --}}
    <x-dialog-modal wire:model.live="showTransferModal">
        <x-slot name="title">
            {{ __('Transferir Mercancía') }}: <span class="text-indigo-600 font-black">{{ $selectedProduct->name ?? '' }}</span>
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="fromBranchId" value="{{ __('Desde Sucursal') }}" />
                    <select id="fromBranchId" wire:model.live="fromBranchId" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 text-sm mt-1">
                        <option value="">Seleccione origen...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="fromBranchId" class="mt-2" />
                </div>

                <div>
                    <x-label for="toBranchId" value="{{ __('Hacia Sucursal') }}" />
                    <select id="toBranchId" wire:model.live="toBranchId" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 text-sm mt-1">
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