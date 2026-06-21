<div class="p-6 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <h2 class="text-2xl font-black text-gray-800">📋 Historial de Ventas Realizadas</h2>
        <a href="{{ route('sales.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow transition">
            ➕ Nueva Venta
        </a>
    </div>

    <div class="bg-white p-4 rounded-lg shadow border mb-6">
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar por número de factura o cliente..." class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
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

    <div class="bg-white rounded-lg shadow border overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800 text-white text-sm font-bold">
                    <th class="p-3">Fecha / Hora</th>
                    <th class="p-3">Factura No.</th>
                    <th class="p-3">Cliente</th>
                    <th class="p-3 text-center">Métodos de Pago</th>
                    <th class="p-3 text-right">Total Facturado</th>
                    <th class="p-3 text-right">status</th>
                    <th class="p-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr class="border-b hover:bg-gray-50 transition-colors {{ ($sale->status ?? 'completada') === 'cancelada' ? 'bg-gray-50/70 opacity-60 line-through text-gray-400' : 'text-gray-700' }}">
                        <td class="p-3 text-xs text-gray-500">{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                        <td class="p-3 font-bold text-blue-600">{{ $sale->invoice_number }}</td>
                        <td class="p-3">
                            <span class="text-sm font-light block">{{ $sale->client_name }}</span>
                            <span class="text-xs text-gray-400">{{ $sale->client_id_number ?? 'Sin Cédula' }}</span>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex flex-wrap justify-center gap-1">
                                @foreach($sale->payments as $payment)
                                    <span class="bg-gray-100 text-gray-800 text-[10px] font-semibold px-2 py-0.5 rounded border border-gray-200">
                                        {{ $payment->payment_method }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="p-3 text-right font-black text-gray-900">Bs. {{ number_format($sale->total, 2) }}</td>
                        <td class="p-3">
                            @if(($sale->status ?? 'completada') === 'cancelada')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Cancelada
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Completada
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-right text-sm font-medium space-x-2">
                            <button type="button"
                                    wire:click="viewSaleDetails({{ $sale->id }})"
                                    class="bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold px-3 py-1.5 rounded-md text-xs transition border border-blue-200">
                                👁️ Det
                            </button>
                            @if(($sale->status ?? 'completada') !== 'cancelada')
                            {{-- Botón Editar: Carga la venta en el carrito --}}
                            <a href="{{ route('sales.create', ['edit_id' => $sale->id]) }}"
                                class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded transition-colors text-xs font-bold inline-block">
                                    ✏️ Edit
                            </a>

                            {{-- Botón Anular (Eliminar lógico): Cambia estado y devuelve stock --}}
                            <button type="button"
                                    wire:click="cancelSale({{ $sale->id }})"
                                    wire:confirm="¿Estás seguro de que deseas CANCELAR esta venta? Esta acción es irreversible y devolverá los artículos al inventario."
                                    class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 rounded transition-colors text-xs font-bold">
                                🗑️
                            </button>
                            @else
                                {{-- Texto o icono indicando que no hay acciones disponibles para facturas ya muertas --}}
                                <span class="text-xs text-gray-400 italic font-medium">Sin acciones</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">No se encontraron registros de ventas para esta sede.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t bg-gray-50">
            {{ $sales->links() }}
        </div>
    </div>

    @if($this->show_detail_modal && $this->selected_sale)
        <div wire:key="modal-sale-{{ $this->selected_sale->id }}" class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none">
            <div class="fixed inset-0 bg-black opacity-50" wire:click="closeDetailModal"></div>

            <div class="relative w-full max-w-2xl mx-auto my-6 z-50 p-4">
                <div class="relative flex flex-col w-full bg-white border-0 rounded-xl shadow-2xl overflow-hidden">

                    <div class="flex items-center justify-between p-4 bg-blue-900 text-white">
                        <div>
                            <h3 class="text-lg font-black">Detalle de Factura: #{{ $this->selected_sale->invoice_number }}</h3>
                            <p class="text-xs text-blue-200">{{ $this->selected_sale->created_at->format('d/m/Y h:i A') }}</p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-white hover:text-gray-300 font-bold text-xl">✕</button>
                    </div>

                    <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg border text-sm text-gray-700">
                            <div>
                                <span class="text-xs text-gray-400 font-bold block uppercase">Cliente</span>
                                <span class="font-bold text-gray-800">{{ $this->selected_sale->client_name }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-400 font-bold block uppercase">Cédula / RIF</span>
                                <span class="font-bold text-gray-800">{{ $this->selected_sale->client_id_number ?? 'V-00000000' }}</span>
                            </div>
                        </div>

                        <div>
                            <span class="text-xs font-black text-gray-500 uppercase block mb-2">📦 Productos Comprados</span>
                            <table class="w-full text-left text-sm border">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-600 font-bold text-xs uppercase border-b">
                                        <th class="p-2">Descripción</th>
                                        <th class="p-2 text-center">Cant / Peso</th>
                                        <th class="p-2 text-right">Precio Ref.</th>
                                        <th class="p-2 text-right">Subtotal (Bs)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y text-gray-700">
                                    @foreach($this->selected_sale->items as $item)
                                        <tr>
                                            <td class="p-2 font-medium">{{ $item->product->name ?? 'Producto Eliminado' }}</td>
                                            <td class="p-2 text-center font-bold text-gray-900">
                                                {{ $item->unit_type === 'KG' ? number_format($item->quantity, 3) : number_format($item->quantity, 0) }}
                                                <span class="text-xs text-gray-400">{{ $item->unit_type }}</span>
                                            </td>
                                            <td class="p-2 text-right text-xs text-gray-500">Bs. {{ number_format($item->price, 2) }}</td>
                                            <td class="p-2 text-right font-bold">Bs. {{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <div class="space-y-2">
                                <span class="text-xs font-black text-gray-500 uppercase block">💳 Desglose de Pago</span>
                                <div class="bg-gray-50 rounded border divide-y text-xs">
                                    @foreach($this->selected_sale->payments as $payment)
                                        <div class="p-2 flex justify-between">
                                            <span class="font-semibold text-gray-600">{{ $payment->payment_method }}</span>
                                            <span class="font-bold text-gray-800">Bs. {{ number_format($payment->amount, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 flex flex-col justify-center items-end">
                                <span class="text-xs text-blue-700 font-bold uppercase">Monto Neto Facturado</span>
                                <span class="text-2xl font-black text-blue-900">Bs. {{ number_format($this->selected_sale->total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 border-t flex justify-between items-center">
                        <button type="button"
                                onclick="printTicket({{ $this->selected_sale->id }})"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2.5 rounded-lg shadow transition flex items-center gap-1">
                            🖨️ Imprimir Ticket Térmico
                        </button>

                        <button type="button" wire:click="closeDetailModal" class="bg-gray-500 hover:bg-gray-600 text-white text-xs font-bold px-4 py-2.5 rounded-lg transition">
                            Cerrar Ventana
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function printTicket(saleId) {
        // Apuntamos a una ruta limpia que renderizará el diseño puro del ticket
        const url = `/ventas/${saleId}/ticket`;

        // Abrimos una ventana emergente oculta temporal
        const printWindow = window.open(url, '_blank', 'width=300,height=600');

        // Cuando la ventana termine de cargar el diseño, dispara la impresión y se auto-cierra
        printWindow.onload = function() {
            printWindow.print();
            setTimeout(() => {
                printWindow.close();
            }, 500);
        };
    }
</script>
