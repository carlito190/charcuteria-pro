<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
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

    public function render()
    {
        // Busca ventas por número de factura o nombre de cliente de la sede actual
        $sales = Sale::where('branch_id', $this->branch_id)
            ->where(function($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                      ->orWhere('client_name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.sales.index-sales', [
            'sales' => $sales
        ]);
    }
}
