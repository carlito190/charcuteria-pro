<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <h2 class="text-2xl font-bold text-gray-800 mb-6">Gestión de Transferencias</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                        <span class="bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center mr-2 text-sm">1</span>
                        Datos de Envío
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <x-label value="Sede Origen" />
                            <select wire:model="from_branch_id"
                                    class="w-full mt-1 border-gray-300 rounded-lg shadow-sm"
                                    @if(count($items) > 0) disabled @endif>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label value="Sede Destino" />
                            <select wire:model="to_branch_id"
                                    class="w-full mt-1 border-gray-300 rounded-lg shadow-sm"
                                    @if(count($items) > 0) disabled @endif>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                        <span class="bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center mr-2 text-sm">2</span>
                        Agregar Producto
                    </h3>

                    <div class="relative z-20 space-y-4">
                        <div>
                            <x-label value="Buscar Producto" />
                            <div class="relative">
                                <x-input type="text"
                                         wire:model.live.debounce.300ms="search_product"
                                         class="w-full pr-10"
                                         placeholder="Escribe para buscar..." />
                            </div>

                            @if(!empty($search_product) && count($search_results) > 0 && is_null($selected_product_id))
                                <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-b-lg shadow-xl mt-1 max-h-60 overflow-y-auto">
                                    @foreach($search_results as $product)
                                        <button type="button"
                                                wire:click="selectProduct({{ $product->id }}, '{{ $product->name }}')"
                                                class="w-full text-left p-3 hover:bg-indigo-50 border-b text-sm transition">
                                            {{ $product->name }}
                                            @if($product->brand)
                                                <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-1.5 py-0.5 rounded border border-gray-200 uppercase">
                                                    {{ $product->brand->name }}
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @if (session()->has('error'))
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-2 items-end">
                            <div class="flex-1">
                                <x-label value="Cantidad" />
                                <x-input type="number" step="0.001" wire:model="quantity" class="w-full" />
                            </div>
                            <x-button wire:click="addItem" class="bg-indigo-600 hover:bg-indigo-800">
                                AGREGAR
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-700 mb-4">Productos en Carrito</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="border-b">
                                <tr class="text-gray-400 text-xs uppercase tracking-wider">
                                    <th class="pb-3">Producto</th>
                                    <th class="pb-3 text-center">Cantidad</th>
                                    <th class="pb-3 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-4 font-medium">{{ $item['name'] }}</td>
                                        <td class="py-4 text-center font-bold text-indigo-600">{{ number_format($item['quantity'], 3) }}</td>
                                        <td class="py-4 text-right">
                                            <button wire:click="removeItem({{ $index }})" class="text-red-400 hover:text-red-600 text-sm">Eliminar</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-10 text-center text-gray-400">Carrito vacío</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(count($items) > 0)
                        <div class="mt-6 border-t pt-4">
                            <x-label value="Observaciones" class="mb-2" />
                            <textarea wire:model="observation" class="w-full border-gray-300 rounded-lg shadow-sm" rows="2"></textarea>
                            <x-button wire:click="saveTransfer" class="mt-4 w-full justify-center bg-indigo-700 py-3">
                                PROCESAR ENVÍO COMPLETO
                            </x-button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b font-bold text-gray-700">Historial de Movimientos</div>

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="p-4 text-center">Nro Guía</th>
                            <th class="p-4 text-center">Ruta</th>
                            <th class="p-4 text-center">Usuario</th> <th class="p-4 text-center">Fecha</th>
                            <th class="p-4 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($history as $record)
                            <tr>
                                <td class="p-4 text-center font-bold text-indigo-600">{{ $record->transfer_number }}</td>
                                <td class="p-4 text-center text-sm">{{ $record->fromBranch->name }} ⮕ {{ $record->toBranch->name }}</td>
                                <td class="p-4 text-center text-sm text-gray-600">{{ $record->user->name ?? 'N/A' }}</td>
                                <td class="p-4 text-center text-xs text-gray-500">{{ $record->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-4 text-center">
                                    <x-button wire:click="showDetails({{ $record->id }})" class="bg-gray-600 hover:bg-gray-800">
                                        VER DETALLES
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
    </div>
      <x-dialog-modal wire:model.live="showModal">
            <x-slot name="title">
                Detalle de Transferencia: {{ $selectedTransfer ? $selectedTransfer->transfer_number : '' }}
            </x-slot>

            <x-slot name="content">
                @if($selectedTransfer)
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div><strong>Origen:</strong> {{ $selectedTransfer->fromBranch->name }}</div>
                        <div><strong>Destino:</strong> {{ $selectedTransfer->toBranch->name }}</div>
                        <div><strong>Usuario:</strong> <br> {{ $selectedTransfer->user->name }}</div>
                        <div class="col-span-3 border-t pt-2 mt-1"><strong>Nota:</strong> {{ $selectedTransfer->observation }}</div>
                    </div>

                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr>
                                <th class="text-left py-2">Producto</th>
                                <th class="text-right py-2">Cantidad (Kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedTransfer->items as $item)
                                <tr class="text-center">
                                    <td class="py-2">{{ $item->product->name }}</td>
                                    <td class="text-right py-2">{{ number_format($item->quantity, 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-slot>

           <x-slot name="footer">
                <x-secondary-button wire:click="closeModal">Cerrar</x-secondary-button>
            </x-slot>
        </x-dialog-modal>
</div>
