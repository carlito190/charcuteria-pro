<?php

namespace App\Livewire\Purchases;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class IndexPurchases extends Component
{
    use WithPagination;

    public $search = '';
    public $branch_id;

    // Propiedades para el modal de detalle
    public $show_detail_modal = false;
    public $selected_purchase = null;

    public function mount()
    {
        // Filtramos por la sede del usuario logueado
        $this->branch_id = Auth::user()->branch_id ?? 1;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewPurchaseDetails($purchaseId)
    {
        // Cargamos la compra con sus detalles (purchase_details) y los productos
        // NOTA: Asegúrate de que en tu modelo Purchase la relación se llame 'details'
        $this->selected_purchase = Purchase::with(['details.product', 'provider'])->find($purchaseId);

        if ($this->selected_purchase) {
            $this->show_detail_modal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->show_detail_modal = false;
        $this->selected_purchase = null;
    }

    public function render()
    {
        $user = auth()->user();

        // Iniciamos la consulta base de compras
        $query = Purchase::query();

        // 🛡️ APLICAMOS FILTRO DE JERARQUÍA:
        // Si NO es administrador global, lo obligamos a ver solo lo de su sede asignada
        if (!$user->isGlobalAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        // Aplicamos el buscador por factura o proveedor
        $purchases = $query->where(function($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('provider', function($prov) {
                    $prov->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.purchases.index-purchases', [
            'purchases' => $purchases
        ]);
    }
}
