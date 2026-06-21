<div class="p-6 max-w-7xl mx-auto">
    {{-- ENCABEZADO PRINCIPAL --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <h2 class="text-2xl font-black text-gray-800">📦 Historial de Compras (Entradas)</h2>

        <a href="{{ route('purchases') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow transition">
            ➕ Registrar Compra
        </a>
    </div>

    {{-- SECCIÓN DE BÚSQUEDA --}}
    <div class="bg-white p-4 rounded-lg shadow border mb-6">
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar por factura o proveedor..." class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    {{-- TABLA DEL HISTORIAL DE COMPRAS --}}
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-full">
                <thead>
                    <tr class="bg-gray-800 text-white text-sm font-bold">
                        <th class="p-4 bg-slate-800 text-white">Fecha Compra</th>
                        <th class="p-4 bg-slate-800 text-white">Factura No.</th>
                        <th class="p-4 bg-slate-800 text-white">Proveedor</th>
                        <th class="p-4 bg-slate-800 text-white text-center">Condición</th> {{-- 👈 NUEVA COLUMNA INFORMATIVA --}}
                        <th class="p-4 bg-slate-800 text-white text-right">Inversión ($)</th>
                        <th class="p-4 bg-slate-800 text-white text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 text-sm font-semibold text-gray-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}
                            </td>

                            <td class="p-4 text-sm font-bold text-slate-900 whitespace-nowrap">
                                #{{ $purchase->invoice_number }}
                            </td>

                            <td class="p-4 text-sm font-medium text-gray-900">
                                {{ $purchase->provider->name ?? 'Proveedor General' }}
                            </td>

                            {{-- STATUS BADGE EN LA TABLA MAESTRA --}}
                            <td class="p-4 text-center whitespace-nowrap">
                                @if(($purchase->status ?? 'pagada') === 'pagada')
                                    <span class="px-2.5 py-1 text-[10px] font-black rounded-full bg-green-100 text-green-800 uppercase">PAGADA</span>
                                @elseif(($purchase->status ?? 'credito') === 'credito')
                                    <span class="px-2.5 py-1 text-[10px] font-black rounded-full bg-red-100 text-red-800 uppercase">CRÉDITO</span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-black rounded-full bg-blue-100 text-blue-800 uppercase">PARCIAL</span>
                                @endif
                            </td>

                            <td class="p-4 text-sm text-right font-black text-green-600 whitespace-nowrap">
                                $ {{ number_format($purchase->total_usd, 2) }}
                            </td>

                            <td class="p-4 text-center whitespace-nowrap">
                                <button type="button"
                                        wire:click="viewPurchaseDetails({{ $purchase->id }})"
                                        class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold px-3 py-2 rounded-lg text-xs transition border border-slate-300 shadow-sm">
                                    👁️ Ver Detalles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-sm font-medium text-gray-400 bg-gray-50 italic">
                                No se encontraron registros de compras para esta sede.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>

    {{-- 🚨 MODAL DE DETALLES TOTALMENTE MODIFICADO Y POTENCIADO --}}
    @if($show_detail_modal && $selected_purchase)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none">
            {{-- Fondo Oscuro Detrás --}}
            <div class="fixed inset-0 bg-black opacity-50" wire:click="closeDetailModal"></div>

            {{-- Caja del Modal (Ancho Max 3xl para que quepa todo el nuevo desglose cómodamente) --}}
            <div class="relative w-full max-w-3xl mx-auto my-6 z-50 p-4">
                <div class="relative flex flex-col w-full bg-white border-0 rounded-2xl shadow-2xl overflow-hidden">

                    {{-- Encabezado Superior del Modal --}}
                    <div class="flex items-center justify-between p-4 bg-slate-800 text-white">
                        <div>
                            <h3 class="text-lg font-black">Orden de Compra: #{{ $selected_purchase->invoice_number }}</h3>
                            <p class="text-xs text-slate-300">Fecha de Registro: {{ \Carbon\Carbon::parse($selected_purchase->purchase_date)->format('d/m/Y') }}</p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-white hover:text-gray-300 font-bold text-xl">✕</button>
                    </div>

                    {{-- Cuerpo del Modal --}}
                    <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">

                        {{-- Bloque de Datos Básicos y Estados Financieros Nuevos --}}
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-gray-50 p-4 rounded-xl border gap-4">
                            <div>
                                <span class="text-[10px] text-gray-400 font-bold block uppercase tracking-wider">Proveedor</span>
                                <span class="font-black text-gray-800 text-sm uppercase">{{ $selected_purchase->provider->name ?? 'Proveedor General' }}</span>
                            </div>

                            {{-- Badges Dinámicos de Pago en el Modal --}}
                            <div class="flex flex-wrap gap-2 items-center">
                                @if(($selected_purchase->status ?? 'pagada') === 'pagada')
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-black bg-green-100 text-green-800 uppercase tracking-wide">
                                        🟢 Liquidada (Contado)
                                    </span>
                                @elseif(($selected_purchase->status ?? 'credito') === 'credito')
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-black bg-red-100 text-red-800 uppercase tracking-wide">
                                        ⏳ Crédito Pendiente
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 uppercase tracking-wide">
                                        🔵 Abono Parcial
                                    </span>
                                @endif

                                {{-- Mostrar vencimiento si aplica --}}
                                @if(in_array($selected_purchase->status ?? '', ['credito', 'parcial']) && $selected_purchase->due_date)
                                    <div class="bg-red-50 border border-red-100 px-3 py-1 rounded-lg text-right">
                                        <span class="text-[9px] text-red-500 font-black block uppercase tracking-tight">Vence:</span>
                                        <span class="text-xs font-mono font-bold text-red-700">{{ date('d/m/Y', strtotime($selected_purchase->due_date)) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Tabla de Productos Actualizada --}}
                        <div>
                            <span class="text-xs font-black text-gray-500 uppercase block mb-2">📥 Mercancía Procesada</span>
                            <div class="overflow-x-auto rounded-xl border">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="bg-gray-100 text-gray-600 font-bold text-xs uppercase border-b">
                                            <th class="p-3">Descripción</th>
                                            <th class="p-3 text-center" width="160">Cantidad / Empaque</th>
                                            <th class="p-3 text-right" width="130">Costo Unit ($)</th>
                                            <th class="p-3 text-right" width="140">Subtotal ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y text-gray-700">
                                        @foreach($selected_purchase->details as $detail)
                                            <tr class="hover:bg-gray-50/50 transition">
                                                <td class="p-3">
                                                    <span class="font-bold text-gray-900 uppercase block text-xs">
                                                        {{ $detail->product->name ?? 'Producto General' }}
                                                    </span>
                                                    {{-- Muestra la marca si está asociada en tu relación --}}
                                                    @if(isset($detail->product->brand) && $detail->product->brand)
                                                        <span class="text-[9px] uppercase font-black text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded mt-0.5 inline-block">
                                                            {{ $detail->product->brand->name }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="p-3 text-center bg-indigo-50/30 font-mono font-bold text-indigo-950 text-xs">
                                                    @php
                                                        // Si el subtotal existe y el costo unitario es mayor a cero,
                                                        // deducimos con precisión matemática la cantidad real de unidades individuales
                                                        $unidadesReales = ($detail->cost_unit_usd > 0)
                                                            ? round($detail->subtotal_usd / $detail->cost_unit_usd, 2)
                                                            : $detail->quantity;
                                                    @endphp

                                                    {{-- Formateamos según el tipo de unidad (KG o Unidades fijas) --}}
                                                    @if(isset($detail->product) && $detail->product->unit_type === 'KG')
                                                        {{ number_format($unidadesReales, 3) }} <span class="text-[10px] text-indigo-600 font-normal">KG</span>
                                                    @else
                                                        {{ number_format($unidadesReales, 0) }} <span class="text-[10px] text-indigo-600 font-normal">UND</span>
                                                    @endif

                                                    {{-- Nota aclaratoria pequeña abajo para el operador --}}
                                                    @if($detail->quantity != $unidadesReales)
                                                        <span class="text-[9px] text-gray-400 block font-normal normal-case italic mt-0.5">
                                                            (Ingresaron {{ number_format($detail->quantity, 0) }} bultos/packs)
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="p-3 text-right font-mono text-xs text-gray-600">
                                                    $ {{ number_format($detail->cost_unit_usd, 4) }}
                                                </td>

                                                <td class="p-3 text-right font-mono font-bold text-gray-900 text-xs">
                                                    $ {{ number_format($detail->subtotal_usd, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Panel de Desglose de Cuentas, Abonos y Deuda Histórica --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            {{-- Resumen Informativo Dinámico --}}
                            <div class="text-xs text-gray-500 space-y-1 flex flex-col justify-center border-b md:border-b-0 md:border-r pb-3 md:pb-0 md:pr-4">
                                @if($selected_purchase->status === 'parcial')
                                    <p class="text-blue-800 font-bold">💡 Registro de Abono Parcial.</p>
                                    <p>Esta factura ingresó abonando una porción en caja. El remanente pendiente se cargó de manera automática al sistema de <strong class="text-gray-700">Cuentas por Pagar</strong>.</p>
                                @elseif($selected_purchase->status === 'credito')
                                    <p class="text-red-700 font-bold">⚠️ Factura a Crédito Completo.</p>
                                    <p>No se registraron egresos financieros inmediatos. El total de la operación representa una deuda pendiente de liquidación.</p>
                                @else
                                    <p class="text-green-700 font-bold">✅ Compra de Contado.</p>
                                    <p>La transacción fue cancelada en su totalidad al momento de la recepción física del pedido.</p>
                                @endif
                            </div>

                            {{-- Bloque de Números Financieros Exactos --}}
                            <div class="space-y-1.5 pl-0 md:pl-2">
                                <div class="flex justify-between items-center text-xs text-gray-600">
                                    <span class="font-medium">Total Factura:</span>
                                    <span class="font-mono font-bold">${{ number_format($selected_purchase->total_usd, 2) }}</span>
                                </div>

                                <div class="flex justify-between items-center text-xs text-gray-600 border-t pt-1.5">
                                    <span class="font-medium">Monto Pagado:</span>
                                    <span class="font-mono font-bold text-green-600">
                                        -${{ number_format($selected_purchase->amount_paid ?? $selected_purchase->total_usd, 2) }}
                                    </span>
                                </div>

                                <div class="flex justify-between items-center text-sm border-t pt-1.5">
                                    <span class="font-black text-gray-800">Saldo Pendiente:</span>
                                    <span class="font-mono font-black {{ ($selected_purchase->balance_due ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                        ${{ number_format($selected_purchase->balance_due ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Botón Inferior de Cierre --}}
                    <div class="p-4 bg-gray-50 border-t flex justify-end">
                        <button type="button" wire:click="closeDetailModal" class="bg-gray-500 hover:bg-gray-600 text-white text-xs font-bold px-4 py-2.5 rounded-lg transition shadow-sm">
                            Cerrar Ventana
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
