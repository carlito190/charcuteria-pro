<div class="p-6 max-w-7xl mx-auto">
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => show = false, 4000)"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-xl font-bold text-sm flex items-center gap-2 shadow-sm">
            <span>✅</span>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h2 class="text-xl font-bold mb-4 text-gray-800">🔍 Buscar Artículos</h2>
                
                <div class="relative">
                    <input type="text" 
                           wire:model.live="search" 
                           placeholder="Escribe el nombre o código de barras..." 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    
                    @if(!empty($products))
                        <div class="absolute z-10 w-full bg-white border mt-1 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            @foreach($products as $product)
                            @php
                                // Hacemos exactamente la misma matemática que en el controlador
                                $costUsd = (float) $product->cost_usd;
                                $profitMargin = (float) $product->profit_margin;
                                $priceInUsd = $costUsd * (1 + ($profitMargin / 100));
                                $priceInBs = $priceInUsd * $this->exchange_rate;
                            @endphp
                                <button type="button" 
                                        wire:click="selectProduct({{ $product->id }})"
                                        class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b flex justify-between items-center">
                                    <div>
                                        <span class="font-semibold text-gray-700">{{ $product->name }}</span>
                                        <span class="text-xs text-gray-400 block">Código: {{ $product->code }}</span>
                                    </div>
                                    <span class="bg-blue-100 text-blue-800 font-bold px-2 py-1 rounded text-sm">
                                        Bs. {{ number_format($priceInBs, 2) }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($selected_product_id)
                   <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-gray-600">Producto Seleccionado:</p>
                                <p class="text-xl font-bold text-blue-700">
                                    {{ App\Models\Product::find($selected_product_id)->name }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Disponible en sede: <span class="font-bold text-gray-700">{{ number_format($current_stock, 3) }} {{ $unit_type }}</span>
                                </p>
                            </div>

                            <div class="flex flex-col items-end gap-2 w-full md:w-auto">
                                
                                @if($unit_type === 'KG')
                                    <div class="flex bg-gray-200 p-1 rounded-lg text-xs font-semibold self-start md:self-auto">
                                        <button type="button" wire:click="$set('input_type', 'weight')" class="px-3 py-1 rounded-md transition {{ $input_type === 'weight' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-600' }}">⚖️ Por Peso (KG)</button>
                                        <button type="button" wire:click="$set('input_type', 'money_bs')" class="px-3 py-1 rounded-md transition {{ $input_type === 'money_bs' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-600' }}">Bs. Balanza / Monto</button>
                                        <button type="button" wire:click="$set('input_type', 'money_usd')" class="px-3 py-1 rounded-md transition {{ $input_type === 'money_usd' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-600' }}">$ Monto</button>
                                    </div>
                                @endif

                                <div class="flex items-center gap-4 w-full justify-end">
                                    @if($input_type === 'money_bs' && $unit_type === 'KG')
                                        <div class="w-36">
                                            <label class="block text-xs font-bold text-gray-600 uppercase">Monto en Bs.</label>
                                            <input type="number" step="0.01" wire:model.live="money_input_bs" placeholder="Ej: 500" class="w-full px-3 py-1.5 border border-blue-400 rounded bg-blue-50 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    @endif

                                    @if($input_type === 'money_usd' && $unit_type === 'KG')
                                        <div class="w-36">
                                            <label class="block text-xs font-bold text-gray-600 uppercase">Monto en $</label>
                                            <input type="number" step="0.01" wire:model.live="money_input_usd" placeholder="Ej: 5" class="w-full px-3 py-1.5 border border-green-400 rounded bg-green-50 font-bold focus:outline-none focus:ring-2 focus:ring-green-500">
                                        </div>
                                    @endif

                                    <div class="w-36">
                                        <label class="block text-xs font-bold text-gray-600 uppercase">
                                            {{ $unit_type === 'KG' ? 'Peso Final (KG)' : 'Cantidad (UND)' }}
                                        </label>
                                        <input type="number" 
                                            wire:model="quantity" 
                                            step="0.001" 
                                            {{ $input_type !== 'weight' && $unit_type === 'KG' ? 'readonly' : '' }}
                                            class="w-full px-3 py-1.5 border rounded font-bold text-gray-800 {{ $input_type !== 'weight' && $unit_type === 'KG' ? 'bg-gray-100 border-gray-300' : 'focus:ring-2 focus:ring-blue-500' }}">
                                    </div>

                                    <button type="button" 
                                            wire:click="addToCart" 
                                            class="h-10 mt-5 bg-green-600 hover:bg-green-700 text-white px-5 rounded-lg font-bold shadow transition flex items-center gap-1">
                                        ➕ Agregar
                                    </button>
                                </div>
                                
                                @if($input_type != 'weight' && (float)$quantity > 0)
                                    <span class="text-[11px] text-gray-500 italic">Equivale aproximadamente a: <b>{{ number_format((float)$quantity * 1000, 0) }} gramos</b></span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="p-4 bg-gray-800 text-white font-bold flex justify-between">
                    <span>🛒 Lista de Compra</span>
                    <span>{{ count($cart) }} artículos</span>
                </div>
                
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b text-gray-600 text-sm font-semibold">
                            <th class="p-3">Descripción</th>
                            <th class="p-3 text-center">Precio</th>
                            <th class="p-3 text-center">Cant / Peso</th>
                            <th class="p-3 text-right">Subtotal</th>
                            <th class="p-3 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cart as $item)
                            <tr class="border-b hover:bg-gray-50 text-gray-700 text-sm">
                                <td class="p-3 font-medium">{{ $item['name'] }}</td>
                                <td class="p-3 text-center">Bs. {{ number_format($item['price'], 2) }}</td>
                                <td class="p-3 text-center font-bold">
                                    {{ $item['unit_type'] === 'KG' ? number_format($item['quantity'], 3) : number_format($item['quantity'], 0) }} 
                                    <span class="text-xs text-gray-400 font-normal">{{ $item['unit_type'] }}</span>
                                </td>
                                <td class="p-3 text-right font-bold text-gray-900">Bs. {{ number_format($item['subtotal'], 2) }}</td>
                                <td class="p-3 text-center">
                                    <button wire:click="removeItem('{{ $item['product_id'] }}')" class="text-red-500 hover:text-red-700 font-bold">
                                        ❌
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-400">El carrito está vacío. Empieza buscando un producto arriba.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <button type="button" 
                    wire:click="openDailyTotals" 
                    class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs uppercase tracking-wider py-2 rounded shadow mb-4 transition flex items-center justify-center gap-2">
                📊 Consultar Total Diario / Arqueo
            </button>
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-3">👤 Datos del Cliente</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase">Nombre</label>
                        <input type="text" wire:model="client_name" class="w-full px-3 py-1.5 border rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase">Cédula / RIF</label>
                        <input type="text" wire:model="client_id_number" class="w-full px-3 py-1.5 border rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-2 border-blue-500 space-y-4">
                <div class="text-center pb-3 border-b">
                    <span class="text-sm font-bold text-gray-500 uppercase">Total a Pagar</span>
                    <h2 class="text-3xl font-black text-gray-900">Bs. {{ number_format($total, 2) }}</h2>
                    <span class="text-xs text-gray-400 block mt-1">Ref: $ {{ number_format($total / $exchange_rate, 2) }} (Tasa: {{ number_format($exchange_rate, 2) }})</span>
                </div>

                @if($total > 0)
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 space-y-3">
                        <h4 class="text-sm font-bold text-blue-900">💵 Registrar Pago</h4>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600">Método</label>
                                <select wire:model="payment_method" class="w-full p-1.5 border rounded bg-white text-sm">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Divisas">Divisas ($)</option>
                                    <option value="Pago Móvil">Pago Móvil</option>
                                    <option value="Bio Pago">Bio Pago</option>
                                    <option value="Punto">Punto de Venta</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600">Moneda Entrega</label>
                                <select wire:model="currency" class="w-full p-1.5 border rounded bg-white text-sm">
                                    <option value="VES">Bolívares (VES)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600">Monto Entregado</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-sm text-gray-400 font-bold">
                                    {{ $currency === 'USD' ? '$' : 'Bs.' }}
                                </span>
                                <input type="number" step="0.01" wire:model="payment_amount" class="w-full pl-10 pr-3 py-1.5 border rounded text-right font-bold text-lg">
                            </div>
                        </div>

                        <button type="button" wire:click="addPayment" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 rounded shadow transition">
                            ➕ Aplicar este Pago
                        </button>
                    </div>
                @endif

                @if(!empty($payments_added))
                    <div class="border-t pt-3 space-y-2">
                        <p class="text-xs font-bold text-gray-500 uppercase">Pagos recibidos:</p>
                        @foreach($payments_added as $index => $pay)
                            <div class="flex justify-between items-center bg-gray-50 p-2 rounded text-xs border">
                                <div>
                                    <span class="font-bold text-gray-700">{{ $pay['payment_method'] }}</span> 
                                    <span class="text-gray-400">({{ $pay['currency'] }})</span>
                                    @if($pay['currency'] === 'USD')
                                        <p class="text-[10px] text-gray-400">Recibido: ${{ number_format($pay['amount_received'], 2) }} x {{ $pay['exchange_rate'] }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900">Bs. {{ number_format($pay['amount'], 2) }}</span>
                                    <button type="button" wire:click="removePayment({{ $index }})" class="text-red-500 hover:text-red-700">✕</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="pt-3 border-t flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-600">Restante por cobrar:</span>
                    <span class="text-lg font-black {{ $pending_amount <= 0.01 ? 'text-green-600' : 'text-red-600' }}">
                        Bs. {{ number_format(max(0, $pending_amount), 2) }}
                    </span>
                </div>

                <button type="button" 
                        wire:click="saveSale"
                        {{ $pending_amount > 0.01 || count($cart) === 0 ? 'disabled' : '' }}
                        class="w-full font-bold text-center py-3 rounded-lg shadow-md text-white transition-all
                        {{ $pending_amount > 0.01 || count($cart) === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 cursor-pointer' }}">
                    ✅ Confirmar y Facturar Venta
                </button>
            </div>
        </div>
    </div>
    @if($show_daily_totals_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            
            <div class="relative w-full max-w-md mx-auto my-6 z-50">
                <div class="relative flex flex-col w-full bg-white border-0 rounded-xl shadow-2xl outline-none focus:outline-none overflow-hidden">
                    
                    <div class="flex items-center justify-between p-4 bg-gray-900 text-white">
                        <h3 class="text-lg font-bold">📊 Ventas del Día de Hoy</h3>
                        <button type="button" wire:click="$set('show_daily_totals_modal', false)" class="text-white hover:text-gray-300 font-bold text-xl">✕</button>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Cierre Parcial por Métodos de Pago:</p>

                        <div class="p-6 space-y-4">
    
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 space-y-2">
                                <span class="text-[11px] font-bold text-gray-600 uppercase tracking-wider block">📅 Rango de Consulta</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-gray-500 font-semibold uppercase">Desde</label>
                                        <input type="date" 
                                            wire:model.live="date_from" 
                                            class="w-full text-xs p-1.5 border rounded bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-500 font-semibold uppercase">Hasta</label>
                                        <input type="date" 
                                            wire:model.live="date_to" 
                                            class="w-full text-xs p-1.5 border rounded bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider pt-2">Resultados del Período:</p>
                            
                            <div class="divide-y divide-gray-200">
                                {{-- Mantenemos una lista base ordenada pero sumamos soporte para cualquier método guardado como Bio Pago --}}
                                @php 
                                    // Obtenemos los métodos que tienen montos reales registrados en el array, o usamos por defecto los estándar
                                    $uniqueMethods = array_unique(array_merge(['Efectivo', 'Divisas', 'Pago Móvil', 'Punto'], array_keys($daily_totals_by_method)));
                                @endphp

                                @foreach($uniqueMethods as $method)
                                    <div class="py-2.5 flex justify-between items-center">
                                        <span class="font-medium text-gray-700">{{ $method }}</span>
                                        <span class="font-bold text-gray-900">
                                            Bs. {{ number_format($daily_totals_by_method[$method] ?? 0.00, 2) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-4 border-t-2 border-dashed border-gray-300 flex justify-between items-center bg-blue-50 p-3 rounded-lg">
                                <span class="font-black text-blue-900 uppercase text-sm">Caja Total (Bs)</span>
                                <span class="text-xl font-black text-blue-900">
                                    Bs. {{ number_format($grand_daily_total, 2) }}
                                </span>
                            </div>
                            
                            <div class="text-center">
                                <span class="text-[10px] text-gray-400 block italic">Referencia aproximada total en divisas:</span>
                                <span class="text-xs text-red font-bold text-green-600">$ {{ number_format($grand_daily_total / $exchange_rate, 2) }}</span>
                            </div>
                        </div>
                        
                    <div class="p-4 bg-gray-50 border-t flex justify-end">
                        <button type="button" wire:click="$set('show_daily_totals_modal', false)" class="bg-gray-600 hover:bg-gray-700 text-white text-xs font-bold px-4 py-2 rounded shadow transition">
                            Cerrar Ventana
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>