<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\ProductBranch;
use Illuminate\Support\Facades\DB;

class PurchaseManager extends Component
{
    // Datos del encabezado
    public $provider_id, $branch_id, $invoice_number, $purchase_date;

    // Array para los productos de la factura
    public $items = [];
    public $total = 0;
    public $searchProduct = '';
    public $searchResults = [];
    public $searchProvider = '';
    public $providerResults = [];
    public $selectedProviderName = ''; // Para mostrar quién elegiste

        // Función para buscar productos mientras escribes
    public function updatedSearchProduct()
    {
        if (strlen($this->searchProduct) < 2) {
            $this->searchResults = [];
            return;
        }
        $this->searchResults = Product::where('name', 'like', '%' . $this->searchProduct . '%')
            ->limit(5)->get();
    }

    // Función para seleccionar el producto y añadirlo a la tabla
    public function selectProduct($productId, $productName)
    {
        // Buscamos si el producto ya está en la lista para no repetirlo (opcional)
        $this->items[] = [
            'product_id' => $productId,
            'name' => $productName, // Aquí pasamos el nombre para la vista
            'quantity' => 1,
            'cost_unit_usd' => 0,
            'subtotal' => 0
        ];

        // Limpiamos el buscador para la siguiente búsqueda
        $this->searchProduct = '';
        $this->searchResults = [];

        $this->calculateTotal();
    }

    public function updatedSearchProvider()
    {
        if (strlen($this->searchProvider) < 2) {
            $this->providerResults = [];
            return;
        }
        $this->providerResults = \App\Models\Provider::where('name', 'like', '%' . $this->searchProvider . '%')
            ->limit(5)->get();
    }

    public function selectProvider($id, $name)
    {
        $this->provider_id = $id;
        $this->selectedProviderName = $name;
        $this->searchProvider = ''; // Limpiamos el buscador
        $this->providerResults = [];
    }

    public function mount()
    {
        $this->purchase_date = date('Y-m-d');
        //$this->addDetail(); // Inicia con una fila vacía
    }

    // Añadir una fila nueva al "Excel"
    public function addDetail()
    {
        $this->items[] = [
            'product_id' => '',
            'name' => '', // <--- AGREGAR ESTA LÍNEA
            'quantity' => 1,
            'cost_unit_usd' => 0,
            'subtotal' => 0
        ];
    }

    // Quitar una fila
    public function removeDetail($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    // Calcular subtotales y total general
    // Cambia el nombre a 'updated' (genérico) para capturar cualquier cambio en el array items
    public function updated($property)
    {
        // Si lo que cambió pertenece al array 'items'
        if (str_starts_with($property, 'items')) {

            foreach ($this->items as $index => $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $cost = (float) ($item['cost_unit_usd'] ?? 0);

                // Forzamos el cálculo del subtotal en el array
                $this->items[$index]['subtotal'] = $qty * $cost;
            }

            // Recalculamos el gran total
            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->total = array_sum(array_column($this->items, 'subtotal'));
    }

    public function save()
    {
        $this->validate([
            'provider_id' => 'required',
            'branch_id' => 'required',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.cost_unit_usd' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
            // 1. Crear la Compra
            $purchase = Purchase::create([
                'provider_id' => $this->provider_id,
                'branch_id' => $this->branch_id,
                'invoice_number' => $this->invoice_number,
                'purchase_date' => $this->purchase_date,
                'total_usd' => $this->total,
            ]);

            // 2. Crear detalles y actualizar STOCK
            foreach ($this->items as $item) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_unit_usd' => $item['cost_unit_usd'],
                    'subtotal_usd' => $item['subtotal'],
                ]);

                    // Dentro del foreach de la función save()
                $product = Product::find($item['product_id']);
                $product->update([
                    'cost_usd' => $item['cost_unit_usd'] // Actualiza el costo maestro del producto
                ]);

                // ¡ACTUALIZAR STOCK EN LA SUCURSAL ELEGIDA!
                $stockRecord = ProductBranch::firstOrCreate(
                    ['product_id' => $item['product_id'], 'branch_id' => $this->branch_id],
                    ['stock' => 0]
                );
                $stockRecord->increment('stock', $item['quantity']);
            }
        });

        return redirect()->to('/productos')->with('message', 'Compra procesada y stock actualizado.');
    }


    public function render()
    {
        return view('livewire.purchase-manager',
        [
            'providers' => Provider::all(),
            'branches' => Branch::all(),
            'products' => Product::all(),
        ]);
    }
}
