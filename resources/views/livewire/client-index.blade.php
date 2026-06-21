<div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">👤 Directorio de Clientes</h2>
            <p class="text-xs text-gray-500">Gestión de estados de cuenta, créditos y recordatorios de cobro.</p>
        </div>

        {{-- Buscador --}}
        <div class="w-full md:w-72">
            <input type="text" wire:model.live="search"
                placeholder="Buscar por nombre o cédula..."
                class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    {{-- Tabla de Clientes --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full text-left border-collapse bg-white text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-700 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3">Cédula / RIF</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Teléfono</th>
                    <th class="px-4 py-3 text-right">Saldo Pendiente</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 border-t border-gray-100">
                @forelse($clients as $client)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $client->id_number }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $client->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $client->phone ?? 'N/A' }}</td>

                        {{-- Saldo en color rojo si debe dinero --}}
                        <td class="px-4 py-3 text-right font-bold {{ ($client->allow_credit ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                            $ {{ number_format($client->current_balance ?? 0, 2) }}
                        </td>

                        <td class="px-4 py-3 text-center space-x-1">
                            {{-- Botón Estado de Cuenta --}}
                            <button type="button" wire:click="viewStatement({{ $client->id }})"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-2.5 py-1.5 rounded font-semibold text-xs border border-gray-300 transition-colors">
                                📊 Estado
                            </button>

                            {{-- Botón Recordatorio WhatsApp --}}
                            @if(($client->current_balance ?? 0) > 0 && $client->phone)
                                <a href="{{ $this->getWhatsAppLink($client->id) }}" target="_blank"
                                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-2.5 py-1.5 rounded font-bold text-xs inline-flex items-center gap-1 shadow-sm transition-colors">
                                    💬 Cobrar
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-400">No se encontraron clientes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $clients->links() }}
    </div>

    {{-- MODAL DEL ESTADO DE CUENTA DETALLADO --}}
    @if($show_statement_modal && $selected_client)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col border border-gray-200">
                <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">📊 Historial de Créditos: {{ $selected_client->name }}</h3>
                        <p class="text-xs text-gray-500">Documentos pendientes por cobrar de esta cuenta.</p>
                    </div>
                    <button type="button" wire:click="$set('show_statement_modal', false)" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                </div>

                <div class="p-5 overflow-y-auto space-y-3 flex-1">
                    {{-- Tarjeta de Resumen Rápido --}}
                    <div class="bg-red-50 border border-red-100 rounded-lg p-4 flex justify-between items-center">
                        <span class="text-sm font-semibold text-red-800 uppercase">Deuda Total Acumulada</span>
                        <span class="text-xl font-black text-red-700">$ {{ number_format($selected_client->current_balance ?? 0, 2) }}</span>
                    </div>

                    {{-- Lista de ventas a crédito --}}
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Detalle de Compras (Fiado)</h4>
                        @forelse($selected_client->sales as $sale)
                            <div class="border rounded-lg p-3 flex justify-between items-center text-sm hover:bg-gray-50/50">
                                <div>
                                    <span class="font-semibold text-gray-700 block">Factura #{{ $sale->id }}</span>
                                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($sale->date_sale)->format('d/m/Y') }}</span>
                                </div>
                                <div class="text-right">
                                    {{-- Total de la venta --}}
                                    <span class="font-bold text-gray-800 block">$ {{ number_format($sale->total, 2) }}</span>
                                    <span class="text-xs text-red-500 font-semibold uppercase tracking-tight bg-red-50 px-1.5 py-0.5 rounded border border-red-100">
                                        Por Cobrar
                                    </span>
                                </div>
                            </div>
                            @empty
                            <p class="text-center py-4 text-gray-400 text-xs">Este cliente no tiene facturas cargadas a crédito.</p>
                        @endforelse
                    </div>
                </div>

                <div class="p-4 border-t bg-gray-50 flex justify-end rounded-b-xl">
                    <button type="button" wire:click="$set('show_statement_modal', false)" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs py-2 px-4 rounded transition-colors">
                        Cerrar Ventana
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
