<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IndexSales extends Component
{
    use WithPagination;

    public $search = '';
    public $branch_id;

    // Propiedades para el modal de detalle
    public $show_detail_modal = false;
    public $selected_sale = null;

    public function mount()
    {
        $this->branch_id = Auth::user()->branch_id ?? 1;
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reinicia la paginación si el usuario escribe en el buscador
    }

    // Función para cargar la venta con sus relaciones y abrir el modal
    public function viewSaleDetails($saleId)
    {
        // Cargamos la venta junto con sus items (y sus productos) y sus pagos
        $this->selected_sale = Sale::with(['items.product', 'payments'])->find($saleId);

        if ($this->selected_sale) {
            $this->show_detail_modal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->show_detail_modal = false;
        $this->selected_sale = null;
    }

    public function cancelSale($saleId)
    {
        // Usamos una transacción de base de datos por seguridad absoluta
        DB::transaction(function () use ($saleId) {
            $sale = Sale::with('items')->findOrFail($saleId);

            if ($sale->status === 'cancelada') {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Esta venta ya se encuentra cancelada.']);
                return;
            }

            // 1. Revertir el inventario: Devolver cada artículo al stock de la sucursal
            foreach ($sale->items as $item) {
                DB::table('product_branches')
                    ->where('branch_id', $sale->branch_id)
                    ->where('product_id', $item->product_id)
                    ->increment('stock', $item->quantity); // ➕ Devolvemos las unidades vendidas
            }

            // 2. CORREGIDO: Buscar el pago a crédito real en la tabla sales_payments
            $creditPayment = DB::table('sales_payments')
                ->where('sale_id', $saleId)
                ->where('payment_method', 'credito')
                ->first();

            if ($creditPayment) {
                // Buscamos al cliente vinculando de forma segura (por ID o por Cédula si el ID está vacío)
                $client = Client::where('id', $sale->client_id)
                    ->orWhere('id_number', $sale->client_id_number)
                    ->first();

                if ($client) {
                    // Convertimos el monto que estaba en Bolívares a Dólares con la tasa de la fila
                    $amountInBs = (float)$creditPayment->amount;
                    $rate = (float)$creditPayment->exchange_rate;

                    $amountInUSD = $rate > 0 ? ($amountInBs / $rate) : 0;

                    // Restamos el equivalente real en dólares al balance del cliente
                    $client->decrement('current_balance', round($amountInUSD, 2));
                }
            }

            // 3. Cambiar el estatus de la venta
            $sale->update([
                'status' => 'cancelada'
            ]);
        });

        session()->flash('success', 'Venta cancelada con éxito. El inventario y el saldo del cliente han sido actualizados.');
        return redirect()->route('sales.index');
    }

    public function render()
    {
        $user = auth()->user();
        $query = Sale::query();

        // 🛡️ Filtro jerárquico activo
        if (!$user->isGlobalAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        // Buscador por número de factura o nombre del cliente
        $sales = $query->where(function($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                ->orWhere('client_name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        // 💡 PASAMOS LAS VARIABLES EXPLICITAMENTE para que Blade no las dé como indefinidas
        return view('livewire.sales.index-sales', [
            'sales' => $sales,
            'show_detail_modal' => $this->show_detail_modal,
            'selected_sale' => $this->selected_sale
        ]);
    }
}
