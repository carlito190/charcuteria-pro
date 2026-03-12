<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductBranch;
use App\Models\Branch;
use Illuminate\Support\Facades\DB; // <--- AÑADE ESTA LÍNEA AQUÍ
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithPagination;

public $search = '';
    public $product_id, $name, $category_id, $barcode;
    public $cost_usd = 0;
    public $profit_margin = 30;
    public $is_open = false;
    public $showTransferModal = false;
    public $selectedProduct;
    public $fromBranchId, $toBranchId, $transferAmount;
    public $branches = [];
    public $viewBranchId = 1; // Sede 1 por defecto para mostrar stock
    public $onlyLowStock = false; // Propiedad para el filtro


    public function render()
    {
        $productsQuery = Product::with(['category', 'branches' => function($query) {
            $query->where('branch_id', $this->viewBranchId);
        }])
        ->withSum('branches as total_stock', 'stock');

        // FILTRO DE STOCK BAJO
        if ($this->onlyLowStock) {
            $productsQuery->whereHas('branches', function($query) {
                $query->where('branch_id', $this->viewBranchId)
                    ->where('stock', '<=', 5); // Cambia el 5 por tu límite deseado
            });
        }

        $products = $productsQuery
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.product-manager', [
            'products' => $products,
            'all_branches' => \App\Models\Branch::all(),
            'categories' => \App\Models\Category::all()
        ]);
    }

    public function openTransfer($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->branches = Branch::all();
        // Pre-seleccionamos la Sede 1 como origen por defecto
        $this->fromBranchId = 1;
        $this->showTransferModal = true;
    }

    public function executeTransfer()
    {
        $this->validate([
            'transferAmount' => 'required|numeric|min:0.001',
            'fromBranchId'   => 'required|exists:branches,id',
            'toBranchId'     => 'required|exists:branches,id|different:fromBranchId',
        ], [
            'toBranchId.different' => 'La sucursal de destino debe ser diferente a la de origen.'
        ]);

        DB::transaction(function () {
            // 1. Validar y Restar del ORIGEN
            $source = ProductBranch::where('product_id', $this->selectedProduct->id)
                ->where('branch_id', $this->fromBranchId)
                ->first();

            if (!$source || $source->stock < $this->transferAmount) {
                // Esto lanza un error que Livewire captura o muestra en el log
                throw new \Exception("Stock insuficiente en la sucursal de origen.");
            }

            $source->decrement('stock', $this->transferAmount);

            // 2. Sumar al DESTINO (usamos firstOrCreate por si el producto es nuevo en esa sede)
            $dest = ProductBranch::firstOrCreate(
                ['product_id' => $this->selectedProduct->id, 'branch_id' => $this->toBranchId],
                ['stock' => 0]
            );
            $dest->increment('stock', $this->transferAmount);
        });

        $this->showTransferModal = false;
        $this->reset(['transferAmount', 'fromBranchId', 'toBranchId']);
        $this->dispatch('saved'); // Opcional: para mostrar un banner de éxito
    }

    public function create()
    {
        $this->resetFields();
        $this->openModal();
    }

    public function openModal() { $this->is_open = true; }
    public function closeModal() { $this->is_open = false; }

    private function resetFields()
    {
        $this->name = '';
        $this->category_id = null;
        $this->barcode = '';
        $this->cost_usd = 0;
        $this->profit_margin = 30;
        $this->product_id = null;
    }

    public function store()
    {
        // Validamos solo lo que tenemos en el formulario
        $this->validate([
            'name' => 'required|min:3',
            'category_id' => 'required|exists:categories,id',
            'cost_usd' => 'required|numeric|min:0',
            'profit_margin' => 'required|numeric|min:0',
        ]);

        Product::updateOrCreate(['id' => $this->product_id], [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'barcode' => $this->barcode,
            'cost_usd' => $this->cost_usd,
            'profit_margin' => $this->profit_margin,
        ]);

        session()->flash('message', 'Producto guardado exitosamente.');
        $this->closeModal();
        $this->resetFields();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->product_id = $id;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->barcode = $product->barcode;
        $this->cost_usd = $product->cost_usd;
        $this->profit_margin = $product->profit_margin;
        $this->openModal();
    }
}
