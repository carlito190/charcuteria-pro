<div class="py-12">
    <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-4 lg:px-6">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl p-4">

            {{-- Buscadores y Filtros Compactados --}}
            <div class="flex flex-wrap items-end justify-between mb-4 gap-3">
                <div class="flex-1 min-w-[250px]">
                    <x-input type="text"
                             wire:model.live="search"
                             placeholder="Buscar por nombre o código..."
                             class="w-full text-xs py-1.5" />
                </div>

                <div class="w-48">
                    <x-label value="Ver inventario de:" class="text-[11px] font-bold text-gray-500 mb-1" />
                    <select wire:model.live="viewBranchId" class="w-full text-xs border-gray-300 rounded-md shadow-sm py-1.5 focus:ring-indigo-500">
                        @foreach($all_branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center bg-gray-50 px-3 py-1.5 rounded-md border border-gray-200 h-[34px]">
                    <label for="lowStock" class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" id="lowStock" wire:model.live="onlyLowStock" class="sr-only">
                            <div class="block bg-gray-200 w-8 h-4 rounded-full {{ $onlyLowStock ? 'bg-red-500' : '' }}"></div>
                            <div class="dot absolute left-0.5 top-0.5 bg-white w-3 h-3 rounded-full transition {{ $onlyLowStock ? 'transform translate-x-4' : '' }}"></div>
                        </div>
                        <div class="ml-2 text-gray-600 font-bold text-xs whitespace-nowrap">
                            ⚠️ (< 5kg)
                        </div>
                    </label>
                </div>

                {{-- Agrega este bloque de botones de exportación en tu barra de herramientas superior --}}
                <div class="flex items-center gap-2">
                    {{-- Botón PDF --}}
                    <a href="{{ route('products.export.pdf', ['search' => $search]) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white rounded-md border border-red-200 text-xs font-bold transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span>PDF</span>
                    </a>

                    {{-- Botón Excel --}}
                    <a href="{{ route('products.export.excel', ['search' => $search]) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white rounded-md border border-emerald-200 text-xs font-bold transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v16.5M21 3v16.5M3.75 12h17.25M3.75 5.25h17.25M3.75 18.75h17.25" />
                        </svg>
                        <span>Excel</span>
                    </a>
                </div>

                <div>
                    <x-button wire:click="create()" class="text-xs py-2 bg-indigo-600 hover:bg-indigo-700">
                        ✨ {{ __('Nuevo Producto') }}
                    </x-button>
                </div>
            </div>

            {{-- Listado en Tabla Super Compacta --}}
            <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
                <table class="w-full text-xs text-left text-gray-500 table-auto">
                    <thead class="text-[11px] text-gray-700 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-2 py-2 text-center w-12">Foto</th>
                            <th class="w-1/3 px-4 py-3 text-left">Producto</th>
                            <th class="px-3 py-2 w-28">Categoría</th>
                            <th class="px-3 py-2 text-center w-28">Stock Sede</th>
                            <th class="px-3 py-2 text-center bg-slate-100/80 w-32">Stock Global</th>
                            <th class="px-2 py-2 text-right w-20">Costo ($)</th>
                            <th class="px-2 py-2 text-right w-20">Venta ($)</th>
                            <th class="px-3 py-2 text-right text-blue-600 bg-blue-50/50 w-24">Venta (Bs)</th>
                            <th class="px-3 py-2 text-center w-20">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($products as $product)
                        <tr class="bg-white hover:bg-slate-50/80 transition">

                            {{-- 📸 MINIATURA DE FOTO --}}
                            <td class="px-2 py-1.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded border border-slate-200 bg-slate-100 overflow-hidden shadow-sm">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[8px] text-gray-400 font-bold uppercase">N/A</span>
                                    @endif
                                </div>
                            </td>

                            {{-- PRODUCTO, MARCA Y CÓDIGO --}}
                            <td class="px-3 py-1.5">
                                <div class="font-bold text-slate-900 uppercase leading-tight truncate max-w-[300px]" title="{{ $product->name }}">
                                    {{ $product->name }}
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5 text-[10px]">
                                    <span class="font-bold text-indigo-600 uppercase">{{ $product->brand->name ?? 'Genérico' }}</span>
                                    <span class="text-slate-400">| 📌 {{ $product->barcode ?? 'Sin código' }}</span>
                                </div>
                            </td>

                            {{-- CATEGORÍA --}}
                            <td class="px-3 py-1.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded font-medium text-[10px] uppercase tracking-wide">
                                    {{ $product->category->name }}
                                </span>
                            </td>

                            {{-- STOCK SEDE ACTUAL --}}
                            <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                @php
                                    $branchData = $product->branches->first();
                                    $stock = $branchData ? $branchData->stock : 0;
                                    $currentBranchName = $all_branches->find($viewBranchId)->name;
                                @endphp
                                <span class="px-2 py-0.5 rounded font-black text-[11px] {{ $stock <= 5 ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                                    {{ number_format($stock, 3) }}
                                </span>
                                <span class="text-[9px] text-gray-400 block font-medium mt-0.5 truncate max-w-[100px]" title="En {{ $currentBranchName }}">
                                    {{ $currentBranchName }}
                                </span>
                            </td>

                            {{-- STOCK GLOBAL --}}
                            <td class="px-3 py-1.5 text-center bg-indigo-50/30 whitespace-nowrap">
                                <span class="text-xs font-black text-indigo-700">
                                    {{ number_format($product->total_stock ?? 0, 3) }}
                                </span>
                                <span class="text-[9px] block text-indigo-400 font-bold uppercase tracking-tighter">Totalizado</span>
                            </td>

                            {{-- COSTO USD --}}
                            <td class="px-2 py-1.5 text-right font-medium text-slate-700 whitespace-nowrap">
                                ${{ number_format($product->cost_usd, 2) }}
                            </td>

                            {{-- VENTA USD --}}
                            <td class="px-2 py-1.5 text-right font-bold text-slate-900 whitespace-nowrap">
                                ${{ number_format($product->selling_price_usd, 2) }}
                            </td>

                            {{-- VENTA BS --}}
                            <td class="px-3 py-1.5 text-right font-black text-blue-700 bg-blue-50/40 whitespace-nowrap">
                                {{ number_format($product->price_bs, 2) }} <span class="text-[9px] font-bold text-blue-500">Bs</span>
                            </td>

                            {{-- 🛠️ ACCIONES CON ICONOS ULTRA COMPACTOS --}}
                            <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    {{-- Botón Editar con Icono Lapicito --}}
                                    <button wire:click="edit({{ $product->id }})"
                                            class="p-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white rounded transition shadow-sm group relative"
                                            title="Editar Producto">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>

                                    {{-- Botón Transferir con Icono de Flechas de Intercambio --}}
                                    <button wire:click="openTransfer({{ $product->id }})"
                                            class="p-1.5 bg-slate-100 hover:bg-slate-700 text-slate-600 hover:text-white rounded transition shadow-sm group relative"
                                            title="Transferir Mercancía">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    {{-- [Aquí abajo siguen intactos tus modales de Dialog-Modal para Creación y Transferencia] --}}

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

               {{-- 🔍 BUSCADOR DE CATEGORÍAS --}}
                <div class="relative">
                    <x-label value="Categoría" class="font-bold text-gray-600" />

                    <div class="relative mt-1">
                        <x-input type="text"
                                wire:model.live="searchCategory"
                                class="w-full text-sm"
                                placeholder="{{ $category_id ? '📂 ' . $selectedCategoryName : '🔍 Escribe para buscar categoría...' }}" />

                        {{-- Si ya hay una categoría asignada, muestra una etiqueta de confirmación --}}
                        @if($category_id)
                            <div class="mt-1 text-xs text-indigo-600 font-bold truncate">
                                Seleccionado: <span class="uppercase bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">{{ $selectedCategoryName }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Desplegable de Resultados --}}
                    @if(!empty($categoryResults))
                        <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto divide-y divide-gray-50">
                            @foreach($categoryResults as $category)
                                <button type="button"
                                        wire:click="selectCategory({{ $category->id }}, '{{ $category->name }}')"
                                        class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 text-sm font-medium text-gray-700 transition uppercase">
                                    {{ $category->name }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <x-input-error for="category_id" class="mt-1" />
                </div>

                {{-- 🔍 BUSCADOR DE MARCAS / FABRICANTES --}}
                <div class="relative">
                    <x-label value="Marca / Fabricante" class="font-bold text-gray-600" />

                    <div class="relative mt-1">
                        <x-input type="text"
                                wire:model.live="searchBrand"
                                class="w-full text-sm"
                                placeholder="{{ $brand_id ? '🏷️ ' . $selectedBrandName : '🔍 Escribe para buscar marca...' }}" />

                        {{-- Si ya hay una marca asignada, muestra la etiqueta --}}
                        @if($brand_id)
                            <div class="mt-1 text-xs text-blue-600 font-bold truncate">
                                Seleccionado: <span class="uppercase bg-blue-50 px-2 py-0.5 rounded border border-blue-200">{{ $selectedBrandName }}</span>
                            </div>
                        @else
                            <div class="mt-1 text-xs text-gray-400 italic">Marca Genérica / Ninguna</div>
                        @endif
                    </div>

                    {{-- Desplegable de Resultados --}}
                    @if(!empty($brandResults))
                        <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto divide-y divide-gray-50">
                            {{-- Opción rápida para resetear y dejar sin marca --}}
                            <button type="button"
                                    wire:click="selectBrand(null, '')"
                                    class="w-full text-left px-4 py-2.5 hover:bg-gray-100 text-sm font-bold text-gray-400 border-b transition">
                                ❌ DEJAR SIN MARCA (GENÉRICA)
                            </button>

                            @foreach($brandResults as $brand)
                                <button type="button"
                                        wire:click="selectBrand({{ $brand->id }}, '{{ $brand->name }}')"
                                        class="w-full text-left px-4 py-2.5 hover:bg-blue-50 text-sm font-medium text-gray-700 transition uppercase">
                                    {{ $brand->name }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <x-input-error for="brand_id" class="mt-1" />
                </div>

                <div>
                    <x-label value="Kilos / Unidades" class="font-bold text-gray-600" />
                    <select wire:model="unit_type" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 text-sm bg-white">
                        <option value="">-- Kilos / Unidades --</option>
                            <option value="KG">Kilos</option>
                            <option value="UND">Unidades</option>
                    </select>
                    <x-input-error for="unity_type" class="mt-1" />
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
