<div class="p-6 max-w-7xl mx-auto">
    {{-- Mensajes de Feedback --}}
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

    {{-- Alerta de Éxito Flotante o Fija --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)" {{-- Se oculta automáticamente a los 4 segundos --}}
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="mb-4 flex items-center gap-3 p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 shadow-sm transition-all">

            {{-- Icono de Check en SVG --}}
            <svg class="flex-shrink-0 inline w-5 h-5 text-green-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
            </svg>

            <span class="sr-only">Éxito</span>

            <div>
                <span class="font-bold">¡Excelente!</span> {{ session('success') }}
            </div>

            {{-- Botón manual para cerrar --}}
            <button type="button" @click="show = false" class="ml-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-100 inline-flex items-center justify-center h-8 w-8 transition-colors">
                <span class="sr-only">Cerrar</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative font-bold text-sm shadow-sm">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- COLUMNA IZQUIERDA: BUSCADOR Y CARRITO (OCUPA 2 COLUMNAS) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Card: Panel de búsqueda de Artículos --}}
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h2 class="text-xl font-bold mb-4 text-gray-800">🔍 Buscar Artículos</h2>

                <div class="relative">
                    <input type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Escribe el nombre o código de barras..."
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">

                    @if(!empty($products) && strlen($search) >= 2)
                        <div class="absolute z-10 w-full bg-white border mt-1 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            @foreach($this->products as $product)
                                @php
                                    $costUsd = (float) $product->cost_usd;
                                    $profitMargin = (float) $product->profit_margin;
                                    $priceInUsd = $costUsd * (1 + ($profitMargin / 100));
                                    $priceInBs = $priceInUsd * (float) $this->exchange_rate;
                                @endphp

                                <button type="button"
                                    wire:click="selectProduct({{ $product->id }})"
                                    class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b flex justify-between items-center">

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-700">{{ $product->name }}</span>
                                            @if($product->brand)
                                                <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-1.5 py-0.5 rounded border border-gray-200 uppercase">
                                                    {{ $product->brand->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-400 block">Código: {{ $product->barcode }}</span>
                                    </div>

                                    <span class="bg-blue-100 text-blue-800 font-bold px-2 py-1 rounded text-sm">
                                        Bs. {{ number_format($priceInBs, 2) }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Sección del Producto Seleccionado (REESTRUCTURADO CON GRID PARA EVITAR DESBORDAMIENTOS) --}}
                @if($selected_product_id)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200" wire:key="selected-product-box-{{ $selected_product_id }}">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">

                            {{-- Columna Izquierda: Información del Producto (4 de 12 espacios) --}}
                            <div class="lg:col-span-4 min-w-0">
                                <p class="text-sm font-bold text-gray-600">Producto Seleccionado:</p>
                                <p class="text-xl font-bold text-blue-700 truncate">
                                    @php
                                        $selectedProduct = App\Models\Product::with('brand')->find($selected_product_id);
                                    @endphp

                                    {{ $selectedProduct->name }}

                                    @if($selectedProduct->brand)
                                        <span class="inline-block bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded border border-blue-200 uppercase align-middle ml-1">
                                            {{ $selectedProduct->brand->name }}
                                        </span>
                                    @endif
                                </p>

                                <p class="text-xs text-gray-500 mt-1 flex flex-wrap items-center gap-1">
                                    📍 <span class="font-semibold text-gray-600 uppercase">Sede Activa:</span>

                                    @if($is_global)
                                        <select wire:model.live="branch_id" class="bg-white text-gray-800 font-bold px-1 py-0.5 rounded text-[11px] border border-gray-300 focus:outline-none">
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="bg-gray-200 text-gray-800 font-bold px-1.5 py-0.5 rounded text-[11px] border border-gray-300">
                                            {{ $branch_name }}
                                        </span>
                                    @endif

                                    <span class="text-gray-400 mx-1">|</span>
                                    <span class="truncate">Disp: <b class="text-gray-700">{{ number_format($current_stock, 3) }} {{ $unit_type }}</b></span>
                                </p>
                            </div>

                            {{-- Columna Derecha: Selectores, Inputs y Botón (8 de 12 espacios) --}}
                            <div class="lg:col-span-8 w-full flex flex-col items-stretch lg:items-end gap-2">

                                {{-- Pestañas/Selectores de tipo de entrada --}}
                                @if($unit_type === 'KG')
                                    <div class="flex bg-gray-200 p-1 rounded-lg text-xs font-semibold self-start lg:self-auto">
                                        <button type="button" wire:click="$set('input_type', 'weight')" class="px-3 py-1 rounded-md transition {{ $input_type === 'weight' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-600' }}">⚖️ Por Peso (KG)</button>
                                        <button type="button" wire:click="$set('input_type', 'money_bs')" class="px-3 py-1 rounded-md transition {{ $input_type === 'money_bs' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-600' }}">Bs. Monto</button>
                                        <button type="button" wire:click="$set('input_type', 'money_usd')" class="px-3 py-1 rounded-md transition {{ $input_type === 'money_usd' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-600' }}">$ Monto</button>
                                    </div>
                                @endif

                                {{-- Fila de Inputs Numéricos y Botón de Acción --}}
                                <div class="flex flex-wrap lg:flex-nowrap items-end justify-end gap-3 w-full">

                                    @if($input_type === 'money_bs' && $unit_type === 'KG')
                                        <div class="w-full sm:w-32 flex-shrink-0" wire:key="input-bs-wrapper">
                                            <label class="block text-[11px] font-bold text-gray-600 uppercase mb-0.5">Monto en Bs.</label>
                                            <input type="number" step="0.01" wire:model.live="money_input_bs" placeholder="Ej: 500" class="w-full px-3 py-1.5 border border-blue-400 rounded bg-blue-50 font-bold text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    @endif

                                    @if($input_type === 'money_usd' && $unit_type === 'KG')
                                        <div class="w-full sm:w-32 flex-shrink-0" wire:key="input-usd-wrapper">
                                            <label class="block text-[11px] font-bold text-gray-600 uppercase mb-0.5">Monto en $</label>
                                            <input type="number" step="0.01" wire:model.live="money_input_usd" placeholder="Ej: 5" class="w-full px-3 py-1.5 border border-green-400 rounded bg-green-50 font-bold text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                                        </div>
                                    @endif

                                    <div class="w-full sm:w-36 flex-shrink-0">
                                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-0.5">
                                            {{ $unit_type === 'KG' ? 'Peso Final (KG)' : 'Cantidad (UND)' }}
                                        </label>
                                        <input type="number"
                                            wire:model="quantity"
                                            step="0.001"
                                            {{ $input_type !== 'weight' && $unit_type === 'KG' ? 'readonly' : '' }}
                                            class="w-full px-3 py-1.5 border rounded font-bold text-sm text-gray-800 {{ $input_type !== 'weight' && $unit_type === 'KG' ? 'bg-gray-100 border-gray-300' : 'focus:ring-2 focus:ring-blue-500' }}">
                                    </div>

                                    {{-- Botón Agregar - Perfectamente Integrado y sin Desbordarse --}}
                                    <div class="w-full sm:w-auto flex-shrink-0">
                                        <button type="button"
                                                wire:click="addToCart"
                                                class="w-full sm:w-auto h-[38px] bg-green-600 hover:bg-green-700 text-white px-5 rounded-lg font-bold shadow transition flex items-center justify-center gap-1 text-sm">
                                            ➕ Agregar
                                        </button>
                                    </div>
                                </div>

                                {{-- Indicador de gramos equivalentes --}}
                                @if($input_type != 'weight' && (float)$quantity > 0)
                                    <div class="text-[11px] text-gray-500 italic text-left lg:text-right w-full mt-0.5">
                                        Equivale aproximadamente a: <b>{{ number_format((float)$quantity * 1000, 0) }} gramos</b>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Card: Tabla del Carrito de Ventas --}}
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
                        @forelse($cart as $index => $item)
                            <tr class="border-b hover:bg-gray-50 text-gray-700 text-sm" wire:key="cart-item-{{ $item['product_id'] }}">
                                <td class="p-3">
                                    <span class="font-medium text-gray-800 block">{{ $item['name']}}</span>
                                    @if(!empty($item['brand_name']))
                                        <span class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider block mt-0.5">
                                            🏷️ {{ $item['brand_name'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">Bs. {{ number_format($item['price'], 2) }}</td>
                                <td class="p-3 text-center font-bold">
                                    {{ $item['unit_type'] === 'KG' ? number_format($item['quantity'], 3) : number_format($item['quantity'], 0) }}
                                    <span class="text-xs text-gray-400 font-normal">{{ $item['unit_type'] }}</span>
                                </td>
                                <td class="p-3 text-right font-bold text-gray-900">Bs. {{ number_format($item['subtotal'], 2) }}</td>
                                <td class="p-3 text-center">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 font-bold">
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

        {{-- COLUMNA DERECHA: CLIENTE, RESUMEN Y COBROS --}}
        <div class="space-y-6">
            {{-- Botón de Arqueo --}}
            <button type="button"
                    wire:click="openDailyTotals"
                    class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs uppercase tracking-wider py-2 rounded shadow transition flex items-center justify-center gap-2">
                📊 Consultar Total Diario / Arqueo
            </button>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">
                    📅 Fecha de la Venta
                </label>
                <input type="datetime-local"
                    wire:model.blur="date_sale"
                    class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
            </div>

            {{-- Card: Datos del Cliente (CORREGIDO EL DOBLE DIV) --}}
            {{-- Card: Datos del Cliente (CON INTEGRACIÓN DE BOTÓN NUEVO Y DIRECTIVAS CORREGIDAS) --}}
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                    👤 Datos del Cliente
                </h3>

                @if(!$selected_client_id)
                    {{-- Bloque de Búsqueda y Registro --}}
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Buscar por Nombre o Cédula/RIF</label>

                        {{-- Contenedor Flex para alinear el input y el botón "Nuevo" --}}
                        <div class="flex gap-2 relative">
                            <div class="flex-1">
                                <input type="text"
                                    wire:model.live="search_client"
                                    class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Ej: Juan Pérez o V-123456...">
                            </div>

                            {{-- Botón para abrir el modal en caliente --}}
                            <button type="button"
                                wire:click="openClientModal"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 rounded transition-colors shadow-sm flex items-center gap-1 shrink-0 h-[38px]">
                                ➕ Nuevo
                            </button>

                            {{-- Resultados del Buscador Desplegable Flotante --}}
                            @if(!empty($client_results))
                                <div class="absolute left-0 right-0 top-full z-50 bg-white border border-gray-200 rounded-lg shadow-xl mt-1 max-h-48 overflow-y-auto">
                                    @foreach($client_results as $c)
                                        <button type="button"
                                            wire:click="selectClient({{ $c->id }})"
                                            class="w-full text-left px-4 py-2 hover:bg-blue-50 transition-colors flex justify-between items-center border-b border-gray-100 last:border-0 text-sm">
                                            <div>
                                                <span class="font-semibold text-gray-700 block">{{ $c->name }}</span>
                                                <span class="text-xs text-gray-400">CI/RIF: {{ $c->id_number }}</span>
                                            </div>
                                            @if($c->allow_credit)
                                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-1.5 py-0.5 rounded">Crédito Activo</span>
                                            @else
                                                <span class="bg-gray-100 text-gray-400 text-[10px] font-bold px-1.5 py-0.5 rounded">Solo Contado</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div> {{-- Fin del div flex --}}
                    </div> {{-- Fin del div de búsqueda mb-3 --}}

                    {{-- Modo Venta Rápida Informativo --}}
                    <div class="p-2 bg-blue-50 border border-blue-200 rounded text-center text-xs text-blue-700 font-semibold">
                        Modo actual: <strong>{{ $client_name }}</strong> (Venta rápida sin registrar)
                    </div>

                @else
                    {{-- Bloque que se muestra cuando un cliente ya está seleccionado --}}
                    <div class="p-3 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg relative">
                        <button type="button"
                                wire:click="resetClient"
                                class="absolute top-2 right-2 text-gray-400 hover:text-red-500 font-bold text-sm transition-colors"
                                title="Cambiar cliente">
                            ✕ Cambiar
                        </button>
                        <p class="text-sm font-bold text-blue-800">{{ $client_name }}</p>
                        <p class="text-xs text-gray-600">Documento: <span class="font-mono">{{ $client_id_number }}</span></p>

                        <div class="mt-2 flex items-center gap-2">
                            @if($client_allow_credit)
                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 text-xs font-bold px-2 py-0.5 rounded-full border border-green-200">
                                    ● Autorizado para Crédito
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-xs font-bold px-2 py-0.5 rounded-full border border-amber-200">
                                    ⚠ Solo pagos de contado
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Card: Caja de Totales y Procesamiento de Factura --}}
            <div class="bg-white p-6 rounded-lg shadow-md border-2 border-blue-500 space-y-4">
                <div class="text-center pb-3 border-b">
                    <span class="text-sm font-bold text-gray-500 uppercase">Total a Pagar</span>
                    <h2 class="text-3xl font-black text-gray-900">Bs. {{ number_format($total, 2) }}</h2>
                    <span class="text-xs text-gray-400 block mt-1">Ref: $ {{ number_format($total / $exchange_rate, 2) }} (Tasa: {{ number_format($exchange_rate, 2) }})</span>
                </div>

                {{-- Módulo de Cobros Activo si hay artículos en carrito --}}
                @if($total > 0)
                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 mb-4" wire:key="payment-registration-card">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-1.5">
                            💵 Registrar Pago
                        </h4>

                        {{-- Contenedor principal con espaciado vertical uniforme --}}
                        <div class="space-y-3.5">

                            {{-- 1. Selector de Método de Pago --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">
                                    Método de Pago
                                </label>
                                <select wire:model.live="payment_method"
                                    class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                    <option value="" disabled>
                                        --Selecione--
                                    </option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Divisas">Divisas ($)</option>
                                    <option value="Pago Móvil">Pago Móvil</option>
                                    <option value="Bio Pago">Bio Pago</option>
                                    <option value="Punto">Punto de Venta</option>

                                    @if($selected_client_id && $client_allow_credit)
                                        <option value="credito" wire:key="pay-method-credit">💳 Crédito / Fiado</option>
                                    @endif
                                </select>
                            </div>

                            {{-- 2. Tasa del Crédito (Aparece con el mismo ancho exacto solo si es crédito) --}}
                            @if($payment_method === 'credito')
                                <div wire:key="credit-rate-wrapper" class="transition-all">
                                    <label class="block text-xs font-semibold text-red-600 uppercase mb-1">
                                        💵 Tasa del Crédito (Bs por $)
                                    </label>
                                    <input type="number"
                                        step="0.01"
                                        wire:model.live="credit_exchange_rate"
                                        class="w-full bg-red-50 border border-red-200 text-red-900 font-bold rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                            @endif

                            {{-- 3. Input del Monto (Abonar o Cargar a Crédito) --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">
                                    @if($payment_method === 'credito')
                                        Monto a Cargar a Crédito ($)
                                    @else
                                        Monto a Abonar en {{ $currency === 'USD' ? 'Dólares ($)' : 'Bolívares (Bs)' }}
                                    @endif
                                </label>
                                <input type="number"
                                    step="0.01"
                                    wire:model="payment_amount"
                                    class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium"
                                    placeholder="0.00"
                                    @if($payment_method === 'credito') readonly @endif
                                >
                            </div>

                            {{-- 4. Botón de Acción --}}
                            <button type="button"
                                    wire:click="addPayment"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-2 px-4 rounded transition-colors flex items-center justify-center gap-1 mt-4 shadow-sm">
                                ➕ Aplicar este Pago
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Listado de Pagos Parciales Cargados --}}
                @if(!empty($payments_added))
                    <div class="border-t pt-3 space-y-2" wire:key="payments-list-container">
                        <p class="text-xs font-bold text-gray-500 uppercase">Pagos recibidos:</p>
                        @foreach($payments_added as $index => $pay)
                            <div class="flex justify-between items-center bg-gray-50 p-2 rounded text-xs border" wire:key="added-payment-{{ $index }}">
                                <div>
                                    <span class="font-bold text-gray-700 uppercase">{{ str_replace('_', ' ', $pay['payment_method']) }}</span>
                                    <span class="text-gray-400">({{ $pay['currency'] }})</span>
                                    @if($pay['currency'] === 'USD')
                                        <p class="text-[10px] text-gray-400">Recibido: ${{ number_format($pay['amount_received'], 2) }} x {{ $pay['exchange_rate'] }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900">Bs. {{ number_format($pay['amount'], 2) }}</span>
                                    <button type="button" wire:click="removePayment({{ $index }})" class="text-red-500 hover:text-red-700 font-bold text-xs">✕</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Restante por Cobrar y Botón Final --}}
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

    {{-- MODAL INTERNO: ARQUEO DE CAJA (CORREGIDO EL DOBLE P-6) --}}
    @if($show_daily_totals_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative w-full max-w-md mx-auto my-6 z-50">
                <div class="relative flex flex-col w-full bg-white border-0 rounded-xl shadow-2xl outline-none focus:outline-none overflow-hidden">

                    <div class="flex items-center justify-between p-4 bg-gray-900 text-white">
                        <h3 class="text-lg font-bold">📊 Ventas del Día de Hoy</h3>
                        <button type="button" wire:click="$set('show_daily_totals_modal', false)" class="text-white hover:text-gray-300 font-bold text-xl">✕</button>
                    </div>

                    {{-- Contenedor General del Cuerpo del Modal --}}
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

                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider pt-2">Resultados por Métodos:</p>

                        <div class="divide-y divide-gray-200">
                            @php
                                $uniqueMethods = array_unique(array_merge(['Efectivo', 'Divisas', 'Pago Móvil', 'Punto','Bio Pago', 'credito'], array_keys($daily_totals_by_method)));
                            @endphp

                            @foreach($uniqueMethods as $method)
                                <div class="py-2.5 flex justify-between items-center">
                                    <span class="font-medium text-gray-700 capitalize">{{ str_replace('_', ' ', $method) }}</span>
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
                            <span class="text-xs font-bold text-green-600">$ {{ number_format($grand_daily_total / $exchange_rate, 2) }}</span>
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

    {{-- MODAL EN CALIENTE PARA CREAR CLIENTE --}}
    @if($show_client_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-gray-200 flex flex-col overflow-hidden animate-fade-in">

                {{-- Encabezado --}}
                <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide flex items-center gap-1.5">
                        👤 Registrar Cliente Nuevo
                    </h3>
                    <button type="button" wire:click="$set('show_client_modal', false)" class="text-gray-400 hover:text-gray-600 font-bold text-sm">✕</button>
                </div>

                {{-- Formulario --}}
                <form wire:submit.prevent="saveClient">
                    <div class="p-5 space-y-4">

                        {{-- Cédula / RIF --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Cédula / RIF *</label>
                            <input type="text" wire:model="new_client_id_number" placeholder="Ej: J3215874123"
                                class="w-full bg-white border @error('new_client_id_number') border-red-500 @else border-gray-300 @enderror rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                            @error('new_client_id_number') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Nombre --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nombre Completo o Razón Social *</label>
                            <input type="text" wire:model="new_client_name" placeholder="Ej: Juan Gonzalez"
                                class="w-full bg-white border @error('new_client_name') border-red-500 @else border-gray-300 @enderror rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                            @error('new_client_name') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Teléfono (WhatsApp)</label>
                            <input type="text" wire:model="new_client_phone" placeholder="Ej: 04121234567"
                                class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                        </div>

                        {{-- Correo --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Correo Electrónico</label>
                            <input type="email" wire:model="new_client_email" placeholder="ejemplo@correo.com"
                                class="w-full bg-white border @error('new_client_email') border-red-500 @else border-gray-300 @enderror rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                            @error('new_client_email') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                {{-- Checkbox oculto con lógica de Livewire --}}
                                <div class="relative">
                                    <input type="checkbox" wire:model="new_client_allow_credit" class="sr-only peer">
                                    {{-- Fondo del Switch --}}
                                    <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-700 uppercase block">¿Habilitar Crédito?</span>
                                    <span class="text-[11px] text-gray-400 block font-medium">Permite fiar mercancía y acumular deuda en cuenta corriente.</span>
                                </div>
                            </label>
                        </div>

                    </div>

                    {{-- Acciones --}}
                    <div class="p-4 border-t bg-gray-50 flex justify-end gap-2">
                        <button type="button" wire:click="$set('show_client_modal', false)"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs py-2 px-4 rounded transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2 px-4 rounded shadow-sm transition-colors">
                            💾 Guardar y Seleccionar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif
</div>
