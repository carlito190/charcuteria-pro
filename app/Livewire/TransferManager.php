<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class TransferManager extends Component
{
    use WithPagination;

    // Propiedades para el formulario
    public $from_branch_id = 1;
    public $to_branch_id = 2;
    public $observation;

    // Propiedades para agregar productos
    public $search_product;
    public $selected_product_id;
    public $quantity = 1;
    public $items = []; // El "carrito" temporal
    public $selectedTransfer = null; // Variable para el modal
    public $showModal = false; // Nueva variable controladora

    public function showDetails($transferId)
    {
        $this->selectedTransfer = Transfer::with(['fromBranch', 'toBranch', 'items.product'])
        ->findOrFail($transferId);

        $this->showModal = true; // Forzamos la apertura
    }

        // Asegúrate de resetearlo al cerrar
    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTransfer = null;
    }

    public function selectProduct($id, $name)
    {
        $this->selected_product_id = $id;
        $this->search_product = $name; // Esto pone el nombre en el input
        // Al hacer clic, ocultamos los resultados limpiando la búsqueda si quisieras,
        // pero aquí simplemente dejamos que el usuario vea que se seleccionó.
        //$this->search_results = [];
    }

    public function addItem()
    {
        $this->validate([
            'selected_product_id' => 'required',
            'quantity' => 'required|numeric|min:0.001',
            'from_branch_id' => 'required',
        ]);


        $product = Product::find($this->selected_product_id);

        // Verificar si ya está en la lista para sumar cantidad en lugar de duplicar
        foreach ($this->items as $index => $item) {
            if ($item['product_id'] == $product->id) {
                $this->items[$index]['quantity'] += $this->quantity;
                $this->reset(['selected_product_id', 'search_product', 'quantity']);
                return;
            }
        }

        $this->items[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => $this->quantity,
        ];

        $this->reset(['selected_product_id', 'search_product', 'quantity']);
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reindexar el array
    }

    public function saveTransfer()
    {
        $this->validate([
            'from_branch_id' => 'required',
            'to_branch_id' => 'required|different:from_branch_id',
            'items' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            $transfer = Transfer::create([
                'user_id' => auth()->id(),
                'from_branch_id' => $this->from_branch_id,
                'to_branch_id' => $this->to_branch_id,
                'observation' => $this->observation,
            ]);

            foreach ($this->items as $item) {
                // 1. Crear el renglón del historial
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);

                // 2. Descontar stock de origen
                DB::table('product_branches')
                    ->where('product_id', $item['product_id'])
                    ->where('branch_id', $this->from_branch_id)
                    ->decrement('stock', $item['quantity']);

                // 3. Aumentar stock en destino
                $dest = DB::table('product_branches')->updateOrInsert(
                    ['product_id' => $item['product_id'], 'branch_id' => $this->to_branch_id],
                    ['stock' => DB::raw("stock + {$item['quantity']}"), 'updated_at' => now()]
                );
            }
        });

        $this->reset(['items', 'observation']);
        session()->flash('message', 'Transferencia registrada exitosamente.');
    }

    public function render()
    {
        $search_results = [];
        if (strlen($this->search_product) > 1) {
            $search_results = Product::with(['brand', 'category'])
            ->search($this->search_product)
            ->limit(5)
            ->get();
        }

        return view('livewire.transfer-manager', [
            'branches' => Branch::all(),
            'search_results' => $search_results,
            'history' => Transfer::with(['fromBranch', 'toBranch', 'items.product'])
                ->latest()
                ->paginate(5)
        ]);
    }
}
