<div class="p-6 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        📊 Control de Cuentas por Cobrar (CxC)
    </h2>

    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm font-medium">
            ✨ {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- COLUMNA IZQUIERDA: LISTA DE DEUDORES --}}
        <div class="lg:col-span-1 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="mb-4">
                <input wire:model.live="search" type="text" placeholder="Buscar cliente por nombre o CI..."
                       class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
                @forelse($clients as $client)
                    <button type="button" wire:click="selectClient({{ $client->id }})"
                            class="w-full text-left p-3 rounded-lg transition-colors flex justify-between items-center {{ $selected_client && $selected_client->id === $client->id ? 'bg-blue-50 border border-blue-100' : 'hover:bg-gray-50' }}">
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ $client->name }}</p>
                            <p class="text-xs text-gray-400">CI: {{ $client->id_number }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-red-600 block">${{ number_format($client->current_balance, 2) }}</span>
                            <span class="text-[11px] text-gray-400 block">Bs. {{ number_format($client->current_balance * $exchange_rate, 2) }}</span>
                        </div>
                    </button>
                @empty
                    <p class="text-center text-gray-400 text-sm py-4">Al día. No hay cuentas pendientes 🎉</p>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $clients->links() }}
            </div>
        </div>

        {{-- COLUMNA DERECHA: DETALLE Y ACCIONES DEL SELECCIONADO --}}
        <div class="lg:col-span-2 space-y-6">
            @if($selected_client)
                {{-- 👈 LE AGREGAMOS UN wire:key DINÁMICO AQUÍ CON EL ID DEL CLIENTE SELECCIONADO --}}
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm" wire:key="client-detail-{{ $selected_client->id }}">
                    <div class="flex justify-between items-start border-b pb-4 mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">{{ $selected_client->name }}</h3>
                            <p class="text-sm text-gray-500">Teléfono: {{ $selected_client->phone ?? 'N/T' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Deuda Total Acumulada</p>
                            <p class="text-3xl font-black text-red-600">${{ number_format($selected_client->current_balance, 2) }}</p>
                            <button type="button" wire:click="openPaymentModal"
                                    class="mt-2 bg-green-600 hover:bg-green-700 text-white font-bold text-xs px-4 py-2 rounded-lg shadow transition-colors">
                                💰 Registrar Abono / Pago
                            </button>
                        </div>
                    </div>

                    <h4 class="font-bold text-gray-700 text-sm mb-3">Facturas Pendientes de Pago:</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estatus</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Factura</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Monto Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($debts as $debt)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-bold text-blue-900">
                                            {{$debt->status }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-blue-600 hover:text-blue-800 cursor-pointer">
                                            <a wire:click="viewInvoiceDetails({{ $debt->id }})">
                                                V-{{ str_replace('V-', '', $debt->invoice_number) }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($debt->date_sale)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <span class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded text-xs">
                                                {{ $debt->branch_name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">
                                            $ {{ number_format($debt->total_usd, 2, '.', ',') }}
                                            <span class="block text-[10px] text-gray-400 font-normal">Ref: {{ number_format($debt->exchange_rate, 2, ',', '.') }} Bs.</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                            No hay facturas pendientes para este cliente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-12 text-center text-gray-400">
                    📥 Selecciona un cliente de la lista de la izquierda para ver su estado de cuenta corriente.
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL INTERACTIVO PARA ABONAR (Manejado de forma nativa por Livewire) --}}
    @if($show_payment_modal)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 animate-fade-in">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">💰 Registrar Abono a Cuenta</h3>

                <form wire:submit.prevent="registerAbono" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Moneda del Pago</label>
                        <select wire:model.live="currency" class="w-full bg-gray-50 border rounded-lg p-2 text-sm focus:outline-none">
                            <option value="USD">Dólares ($)</option>
                            <option value="VES">Bolívares (Bs.)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Monto Recibido</label>
                        <input wire:model.live="amount_received" type="number" step="0.01" placeholder="0.00"
                               class="w-full bg-gray-50 border rounded-lg p-2 text-sm font-bold focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Método de Recibo</label>
                        <select wire:model="payment_method" class="w-full bg-gray-50 border rounded-lg p-2 text-sm focus:outline-none">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Pago Móvil">Pago Móvil</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button type="button" wire:click="$set('show_payment_modal', false)"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-xs px-4 py-2 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold text-xs px-4 py-2 rounded-lg shadow transition-colors">
                            Confirmar Cobro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($selected_invoice_details)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto p-4 animated fadeIn fast">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full overflow-hidden">

                <div class="bg-gray-800 text-white px-4 py-3 flex justify-between items-center">
                    <h3 class="font-bold text-md">
                        Contenido de Factura: #{{ str_replace('V-', '', $selected_invoice_details['number']) }}
                    </h3>
                    <button wire:click="closeInvoiceDetails()" class="text-gray-400 hover:text-white text-xl font-bold focus:outline-none">
                        &times;
                    </button>
                </div>

                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Cant</th>
                                    <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-2 py-1 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-xs">
                                @forelse($invoice_items as $item)
                                    <tr>
                                        <td class="px-2 py-2 font-semibold text-gray-700">
                                            @if(floor($item->quantity) == $item->quantity)
                                                {{ number_format($item->quantity, 0) }}x
                                            @else
                                                {{ number_format($item->quantity, 3, ',', '.') }} {{ $item->unit_type ?? 'KG' }}
                                            @endif
                                        </td>

                                        <td class="px-2 py-2 text-gray-900">
                                            {{ $item->product_name }}
                                        </td>

                                        <td class="px-2 py-2 text-right font-bold text-gray-900">
                                            {{ number_format($item->subtotal, 2, ',', '.') }} Bs.
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-2 py-4 text-center text-gray-400">
                                            No se encontraron artículos en esta venta.
                                        </td>
                                    </tr>
                                @endforelse
                        </table>
                    </div>

                    <div class="mt-4 pt-3 border-t flex justify-between items-center text-sm">
                        <span class="text-gray-500">Monto Base Facturado:</span>
                        <span class="font-bold text-gray-900 text-base">
                            {{ number_format($selected_invoice_details['total'], 2, ',', '.') }} Bs.
                        </span>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-2.5 flex justify-end">
                    <button wire:click="closeInvoiceDetails()" class="px-4 py-1.5 bg-gray-500 hover:bg-gray-600 text-white rounded text-xs font-medium transition shadow-sm">
                        Regresar al Historial
                    </button>
                </div>
            </div>
        </div>
    @endif


</div>
