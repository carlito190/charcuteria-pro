<div class="py-6 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Encabezado Principal de la Vista --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-800">Registrar Compra</h2>
                <p class="text-xs text-slate-500 font-medium">Módulo de Entrada de Mercancía e Inventario Dolarizado</p>
            </div>

            {{-- 💵 CONTROL CENTRAL DE LA TASA DE CAMBIO --}}
            <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-xl shadow-sm border border-blue-100 flex items-center gap-3">
                <div class="flex flex-col">
                    <x-label value="Tasa de Compra" class="font-black text-blue-700 text-[10px] uppercase tracking-wider" />
                    <span class="text-[10px] text-slate-400 font-bold leading-none">BCV / Mercado</span>
                </div>
                <div class="relative rounded-md shadow-sm w-32">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2">
                        <span class="text-xs font-bold text-slate-400">Bs.</span>
                    </div>
                    <x-input type="number" step="0.01"
                             class="w-full font-black text-blue-900 border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-right pl-7 pr-2 py-1 text-sm bg-blue-50/30 rounded-lg"
                             wire:model.live="exchange_rate" placeholder="0.00" />
                </div>
            </div>
        </div>

        {{-- 🏢 SECCIÓN 1: DATOS ADMINISTRATIVOS DE LA FACTURA --}}
        <div class="bg-white p-5 shadow-sm rounded-xl border border-slate-200/60">
            <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider">Información del Documento</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-start">
                {{-- Buscador de Proveedor --}}
                <div class="relative md:col-span-1">
                    <x-label value="Proveedor" class="font-bold text-xs text-slate-600" />
                    <x-input type="text" class="w-full mt-1 text-sm rounded-lg border-slate-200" placeholder="🔍 Buscar proveedor..." wire:model.live="searchProvider" />

                    @if(!empty($providerResults))
                        <div class="absolute z-50 w-full bg-white border border-slate-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto divide-y divide-slate-50">
                            @foreach($providerResults as $p)
                                <button wire:click="selectProvider({{ $p->id }}, '{{ $p->name }}')"
                                        class="w-full text-left px-4 py-2 hover:bg-slate-50 text-xs font-medium text-slate-700 uppercase transition">
                                    {{ $p->name }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if($provider_id)
                        <div class="mt-1.5 text-[11px] text-blue-600 font-bold truncate flex items-center gap-1 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                            <span class="uppercase">Activo: {{ $selectedProviderName }}</span>
                        </div>
                    @endif
                    <x-input-error for="provider_id" class="mt-1" />
                </div>

                {{-- Sucursal Destino --}}
                <div>
                    <x-label value="Sucursal Destino" class="font-bold text-xs text-slate-600" />
                    <select wire:model="branch_id" class="w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm mt-1 text-sm font-medium text-slate-700">
                        <option value="">Seleccione sede...</option>
                        @foreach($branches as $b) <option value="{{$b->id}}">{{$b->name}}</option> @endforeach
                    </select>
                    <x-input-error for="branch_id" class="mt-1" />
                </div>

                {{-- Nro Factura --}}
                <div>
                    <x-label value="Nro Factura / Control" class="font-bold text-xs text-slate-600" />
                    <x-input type="text" class="w-full mt-1 text-sm rounded-lg border-slate-200 font-mono" wire:model="invoice_number" placeholder="Ej: 000234" />
                </div>

                {{-- Fecha Emisión --}}
                <div>
                    <x-label value="Fecha Emisión" class="font-bold text-xs text-slate-600" />
                    <x-input type="date" class="w-full mt-1 text-sm rounded-lg border-slate-200 font-medium" wire:model="purchase_date" />
                </div>

                {{-- Condición de Pago --}}
                <div>
                    <x-label value="Condición de Pago" class="font-bold text-xs text-slate-600" />
                    <select wire:model.live="status" class="w-full border-slate-200 rounded-lg shadow-sm mt-1 text-sm font-bold transition-colors {{ $status === 'pagada' ? 'text-green-700 bg-green-50 focus:border-green-500' : ($status === 'credito' ? 'text-red-700 bg-red-50 focus:border-red-500' : 'text-blue-700 bg-blue-50 focus:border-blue-500') }}">
                        <option value="pagada">🟢 FULL PAGADA</option>
                        <option value="credito">⏳ CRÉDITO TOTAL</option>
                        <option value="parcial">🔵 PAGO PARCIAL</option>
                    </select>
                    <x-input-error for="status" class="mt-1" />
                </div>
            </div>

            {{-- Fila Secundaria Condicional (Vencimiento de Crédito) --}}
            @if(in_array($status, ['credito', 'parcial']))
                <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-4 gap-4 items-end animate-fade-in">
                    <div>
                        <x-label value="Vencimiento del Crédito" class="font-bold text-xs text-red-600" />
                        <x-input type="date" class="w-full mt-1 text-sm rounded-lg border-red-200 focus:border-red-500 focus:ring-red-500" wire:model="due_date" />
                        <x-input-error for="due_date" class="mt-1" />
                    </div>
                </div>
            @endif
        </div>

        {{-- 💵 INTERFAZ DE ENTRADA EXCLUSIVA PARA PAGO PARCIAL --}}
        @if($status === 'parcial')
            <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50/50 border border-blue-200 rounded-xl grid grid-cols-1 md:grid-cols-3 gap-4 items-center shadow-sm">
                <div class="md:col-span-1">
                    <span class="text-sm font-black text-blue-900 block uppercase tracking-tight">Registro de Abono Inicial</span>
                    <span class="text-[11px] text-slate-500 font-medium">Especifica el capital entregado al proveedor en caja física.</span>
                </div>
                <div class="w-full">
                    <div class="relative rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-sm font-bold text-blue-500">$</span>
                        </div>
                        <x-input type="number" step="0.01" class="w-full font-mono font-black text-right text-blue-900 border-blue-300 bg-white rounded-lg pl-8 text-base focus:ring-blue-500"
                                wire:model.live="amount_paid" placeholder="0.00" />
                    </div>
                    <x-input-error for="amount_paid" class="mt-1" />
                </div>
                <div class="bg-white px-4 py-2 rounded-xl border border-blue-100 flex justify-between items-center shadow-inner">
                    <span class="text-[10px] text-slate-400 font-black uppercase tracking-wider block">Saldo Restante (Deuda):</span>
                    <span class="font-mono text-lg font-black text-red-600">${{ number_format($balance_due, 2) }}</span>
                </div>
            </div>
        @endif

        {{-- 🛒 SECCIÓN 2: CARGA DE PRODUCTOS E INVENTARIO --}}
        <div class="bg-white p-5 shadow-sm rounded-xl border border-slate-200/60 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider">Carga y Búsqueda de Artículos</h3>
            </div>

            {{-- Buscador Dinámico de Productos --}}
            <div class="relative bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200">
                <x-label value="Escribe el nombre o código de barras para añadir" class="text-indigo-800 font-black text-xs uppercase tracking-wider" />
                <x-input type="text"
                         class="w-full mt-1.5 rounded-lg border-slate-200 text-sm shadow-inner placeholder-slate-400 focus:ring-indigo-500 focus:border-indigo-500"
                         placeholder="Ej: Jamón Ahumado, Mantequilla, Harina..."
                         wire:model.live="searchProduct" />

                @if(!empty($searchResults))
                    <div class="absolute应用 z-50 w-full left-0 bg-white border border-slate-200 rounded-lg shadow-xl mt-1 max-h-60 overflow-y-auto divide-y divide-slate-100">
                        @foreach($searchResults as $product)
                            <button wire:click="selectProduct({{ $product->id }}, '{{ $product->name }}')"
                                    class="w-full text-left px-4 py-3 hover:bg-indigo-50 flex justify-between items-center transition group">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-bold text-sm text-slate-800 group-hover:text-indigo-900 uppercase">{{ $product->name }}</span>
                                    @if($product->brand)
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 rounded border border-blue-100">
                                            {{ $product->brand->name }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase bg-slate-100 text-slate-400 rounded">
                                            Genérico
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[11px] font-black uppercase text-indigo-600 bg-indigo-50 group-hover:bg-indigo-600 group-hover:text-white px-2.5 py-1 rounded-md transition border border-indigo-100">
                                    Añadir +
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tabla de Carga Operativa --}}
            <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800 text-slate-200 uppercase text-[10px] font-black tracking-wider border-b border-slate-700">
                        <tr>
                            <th class="p-3 text-left">Producto / Detalle</th>
                            <th class="p-3 text-center" width="95">Moneda</th>
                            <th class="p-3 text-center" width="115">Formato</th>
                            <th class="p-3 text-center" width="90">U/Bulto</th>
                            <th class="p-3 text-center" width="100">Cant. Fac</th>
                            <th class="p-3 text-center" width="125">Costo Doc.</th>
                            <th class="p-3 text-center" width="85">¿IVA?</th>
                            <th class="p-3 text-right" width="115">Cost. Unit ($)</th>
                            <th class="p-3 text-right" width="125">Subtotal ($)</th>
                            <th class="p-3 text-center" width="45"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            {{-- Detalle del Producto --}}
                            <td class="p-3">
                                <span class="font-bold text-slate-800 uppercase text-xs block leading-snug">{{ $item['name'] }}</span>
                                @if(($item['buy_format'] ?? 'unit') === 'pack')
                                    <span class="inline-flex mt-0.5 px-1.5 py-0.2 bg-indigo-50 border border-indigo-100 rounded text-[9px] text-indigo-600 font-black uppercase tracking-wide">
                                        Ingresarán: {{ $item['real_inventory_qty'] ?? 0 }} Unids.
                                    </span>
                                @endif
                            </td>

                            {{-- Selector de Moneda --}}
                            <td class="p-2">
                                <select wire:model.live="items.{{$index}}.currency" class="w-full border-slate-200 rounded-md p-1 text-xs font-bold focus:ring-slate-400">
                                    <option value="USD">💵 $</option>
                                    <option value="BS">🇻🇪 Bs.</option>
                                </select>
                            </td>

                            {{-- Formato de Compra --}}
                            <td class="p-2">
                                <select wire:model.live="items.{{$index}}.buy_format" class="w-full border-slate-200 rounded-md p-1 text-xs font-bold focus:ring-slate-400">
                                    <option value="unit">Unidad</option>
                                    <option value="pack">Bulto</option>
                                </select>
                            </td>

                            {{-- Unidades por Pack --}}
                            <td class="p-2">
                                <x-input type="number" class="w-full text-center p-1 text-xs font-mono font-bold border-slate-200 rounded-md bg-slate-50 disabled:opacity-40"
                                         wire:model.live="items.{{$index}}.units_per_pack"
                                         :disabled="($item['buy_format'] ?? 'unit') === 'unit'" />
                            </td>

                            {{-- Cantidad Facturada --}}
                            <td class="p-2">
                                <x-input type="number" step="0.001" class="w-full text-center p-1 text-xs font-black border-slate-200 rounded-md focus:ring-indigo-500" wire:model.live="items.{{$index}}.quantity" />
                            </td>

                            {{-- Costo Documento --}}
                            <td class="p-2">
                                <x-input type="number" step="0.01" class="w-full text-right p-1 font-mono text-xs font-black border-slate-200 rounded-md focus:ring-indigo-500"
                                         wire:model.live="items.{{$index}}.input_cost"
                                         placeholder="{{ $item['currency'] == 'USD' ? '$.' : 'Bs.' }}" />
                            </td>

                            {{-- Switch de IVA --}}
                            <td class="p-2 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" wire:model.live="items.{{$index}}.includes_iva" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-4 h-4">
                                    <span class="ml-1 text-[10px] font-black text-slate-400 uppercase">16%</span>
                                </label>
                            </td>

                            {{-- Costo Unitario Equivalente --}}
                            <td class="p-3 text-right font-mono text-xs text-slate-500 font-bold">
                                ${{ number_format($item['cost_unit_usd'], 4) }}
                            </td>

                            {{-- Subtotal Neto en USD --}}
                            <td class="p-3 text-right font-black text-slate-900 font-mono bg-slate-50/60 text-xs">
                                ${{ number_format($item['subtotal'], 2) }}
                            </td>

                            {{-- Botón de Remover --}}
                            <td class="p-3 text-center">
                                <button type="button" wire:click="removeDetail({{$index}})" class="text-slate-400 hover:text-red-600 transition-colors p-1 rounded-md hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="p-10 text-center text-slate-400 italic text-xs bg-slate-50/40">
                                📌 No hay productos añadidos a esta orden de entrada. Utiliza el buscador superior.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 📊 SECCIÓN 3: RESUMEN FINANCIERO Y PROCESAMIENTO --}}
        <div class="bg-white p-5 shadow-sm rounded-xl border border-slate-200/60 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/50">
                Renglones Cargados: <span class="text-slate-800 font-black ml-1">{{ count($items) }}</span>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-6 text-center md:text-right w-full md:w-auto">
                {{-- Bloque de Totales Monetarios --}}
                <div class="flex flex-col">
                    <div class="flex items-center justify-center md:justify-end gap-2.5">
                        <span class="text-slate-500 text-xs font-black uppercase tracking-tight">Total Equivalente:</span>
                        <span wire:loading.class="opacity-40" class="text-3xl font-black text-emerald-600 font-mono tracking-tight">
                            ${{ number_format($total, 2) }}
                        </span>
                    </div>
                    @if($exchange_rate > 0)
                        <div class="text-xs font-black text-blue-600 font-mono mt-0.5 bg-blue-50/60 border border-blue-100/50 rounded px-2 py-0.5 inline-self-center md:align-self-end">
                            Monto en Bolívares: Bs. {{ number_format($total * $exchange_rate, 2) }}
                        </div>
                    @endif
                </div>

                {{-- Botón de Envío Final --}}
                <x-button wire:click="save" class="w-full md:w-auto px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-md text-sm font-black uppercase tracking-wider text-white rounded-xl transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Procesar Factura</span>
                </x-button>
            </div>
        </div>

    </div>
</div>
