<div class="p-6 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <h2 class="text-2xl font-black text-gray-800">📦 Historial de Compras (Entradas)</h2>
        
        <a href="{{ route('purchases') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow transition">
            ➕ Registrar Compra
        </a>
    </div>

    <div class="bg-white p-4 rounded-lg shadow border mb-6">
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar por factura o proveedor..." class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse min-w-full">
            <thead>
                <tr class="bg-gray-800 text-white text-sm font-bold">
                    <th class="p-4 bg-slate-800 text-white">Fecha Compra</th>
                    <th class="p-4 bg-slate-800 text-white">Factura No.</th>
                    <th class="p-4 bg-slate-800 text-white">Proveedor</th>
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
                            {{ $purchase->provider->name ?? 'FRIO CARNE, C.A' }}
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
                        <td colspan="5" class="p-8 text-center text-sm font-medium text-gray-400 bg-gray-50 italic">
                            No se encontraron registros de compras para esta sede.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($purchases->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>

    @if($show_detail_modal && $selected_purchase)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none">
            <div class="fixed inset-0 bg-black opacity-50" wire:click="closeDetailModal"></div>
            
            <div class="relative w-full max-w-2xl mx-auto my-6 z-50 p-4">
                <div class="relative flex flex-col w-full bg-white border-0 rounded-xl shadow-2xl overflow-hidden">
                    
                    <div class="flex items-center justify-between p-4 bg-slate-800 text-white">
                        <div>
                            <h3 class="text-lg font-black">Orden de Compra: #{{ $selected_purchase->invoice_number }}</h3>
                            <p class="text-xs text-slate-300">Fecha de Registro: {{ \Carbon\Carbon::parse($selected_purchase->purchase_date)->format('d/m/Y') }}</p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-white hover:text-gray-300 font-bold text-xl">✕</button>
                    </div>
                    
                    <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                        <div class="bg-gray-50 p-3 rounded-lg border text-sm text-gray-700">
                            <span class="text-xs text-gray-400 font-bold block uppercase">Proveedor</span>
                            <span class="font-bold text-gray-800">{{ $selected_purchase->provider->name ?? 'Proveedor General' }}</span>
                        </div>

                        <div>
                            <span class="text-xs font-black text-gray-500 uppercase block mb-2">📥 Productos Ingresados al Inventario</span>
                            <table class="w-full text-left text-sm border">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-600 font-bold text-xs uppercase border-b">
                                        <th class="p-2">Descripción</th>
                                        <th class="p-2 text-center">Cantidad / Peso</th>
                                        <th class="p-2 text-right">Costo Unit ($)</th>
                                        <th class="p-2 text-right">Subtotal ($)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y text-gray-700">
                                    @foreach($selected_purchase->details as $detail)
                                        <tr>
                                            <td class="p-2 font-medium">{{ $detail->product->name ?? 'Producto General' }}</td>
                                            <td class="p-2 text-center font-bold text-gray-900">
                                                {{-- Verificamos si es por KG para meterle los 3 decimales limpios --}}
                                                {{ (isset($detail->product) && $detail->product->unit_type === 'KG') ? number_format($detail->quantity, 3) : number_format($detail->quantity, 2) }}
                                                <span class="text-xs text-gray-400">{{ $detail->product->unit_type ?? 'KG' }}</span>
                                            </td>
                                            <td class="p-2 text-right text-xs text-gray-600">$ {{ number_format($detail->cost_unit_usd, 2) }}</td>
                                            <td class="p-2 text-right font-bold text-gray-900">$ {{ number_format($detail->subtotal_usd, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t pt-4 flex justify-end items-center">
                            <div class="bg-green-50 p-4 rounded-xl border border-green-200 text-right w-full md:w-auto">
                                <span class="text-xs text-green-700 font-bold block uppercase">Total Inversión Realizada</span>
                                <span class="text-2xl font-black text-green-700">$ {{ number_format($selected_purchase->total_usd, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-gray-50 border-t flex justify-end">
                        <button type="button" wire:click="closeDetailModal" class="bg-gray-500 hover:bg-gray-600 text-white text-xs font-bold px-4 py-2.5 rounded-lg transition">
                            Cerrar Ventana
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>