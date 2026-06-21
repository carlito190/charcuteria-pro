<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\ProductBranch;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class PurchaseManager extends Component
{
    // Datos del encabezado
    public $provider_id, $branch_id, $invoice_number, $purchase_date;

    // 💱 NUEVA PROPIEDAD: Tasa de cambio de compra de la factura
    public $exchange_rate = 0;

    // Array para los productos de la factura
    public $items = [];
    public $total = 0;
    public $searchProduct = '';
    public $searchResults = [];
    public $searchProvider = '';
    public $providerResults = [];
    public $selectedProviderName = '';

    public $status = 'pagada'; // Por defecto iniciará como pagada
    public $due_date;          // Fecha de vencimiento para el crédito
    public $amount_paid = 0;   // 💵 NUEVO: Lo que pagas en el momento
    public $balance_due = 0;   // ⏳ NUEVO: Lo que quedas debiendo

    public function updatedSearchProduct()
    {
        if (strlen($this->searchProduct) < 2) {
            $this->searchResults = [];
            return;
        }
        $this->searchResults = Product::with('brand', 'category')
        ->search($this->searchProduct)
        ->limit(5)
        ->get();

    }

    public function selectProduct($productId, $productName)
    {
        // 🛠️ Incluimos los nuevos campos interactivos en el esquema del Item
        $product = Product::with('brand')->find($productId);
        $brandSuffix = ($product && $product->brand) ? " ({$product->brand->name})" : '';

        $newItem  = [
            'product_id'   => $productId,
            'name'         => $productName . $brandSuffix,
            'quantity'     => 1,
            'currency'     => 'USD',
            'buy_format'   => 'unit',
            'units_per_pack' => 1,
            'input_cost'   => 0,
            'includes_iva' => false,       // 👈 DE VUELTA EL CAMPO DEL IVA
            'cost_unit_usd'=> 0,
            'subtotal'     => 0
        ];

        array_unshift($this->items, $newItem);

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
        $this->providerResults = Provider::where('name', 'like', '%' . $this->searchProvider . '%')
            ->limit(5)->get();
    }

    public function selectProvider($id, $name)
    {
        $this->provider_id = $id;
        $this->selectedProviderName = $name;
        $this->searchProvider = '';
        $this->providerResults = [];
    }

    public function mount()
    {
        $this->purchase_date = date('Y-m-d');
        $this->due_date = date('Y-m-d', strtotime('+15 days'));
    }

    public function addDetail()
    {
        $newItem = [
            'product_id'   => '',
            'name'         => '',
            'quantity'     => 1,
            'currency'     => 'USD',
            'buy_format'   => 'unit',
            'units_per_pack' => 1,
            'input_cost'   => 0,
            'includes_iva' => false,       // 👈 REPLICADO AQUÍ TAMBIÉN
            'cost_unit_usd'=> 0,
            'subtotal'     => 0
        ];

        // PHP empuja el nuevo ítem al inicio del arreglo y corre los demás hacia abajo
        array_unshift($this->items, $newItem);

        // Limpias el buscador para que quede listo para el siguiente producto
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->calculateTotal();
    }

    public function removeDetail($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    // 🔥 CALCULADORA INTERACTIVA AUTOMÁTICA EN TIEMPO REAL
    public function updated($property)
    {
           // Si cambia cualquier cosa de los items, la tasa, el estatus o el monto abonado
        if (str_starts_with($property, 'items') || $property === 'exchange_rate' || $property === 'status' || $property === 'amount_paid') {
            $rate = (float) $this->exchange_rate;

            foreach ($this->items as $index => $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $inputCost = (float) ($item['input_cost'] ?? 0);
                $currency = $item['currency'] ?? 'USD';
                $buyFormat = $item['buy_format'] ?? 'unit';
                $unitsPerPack = (float) ($item['units_per_pack'] ?? 1);
                $hasIva = (bool) ($item['includes_iva'] ?? false);

                if ($unitsPerPack <= 0) $unitsPerPack = 1;

                $costBase = ($buyFormat === 'pack') ? ($inputCost / $unitsPerPack) : $inputCost;

                if ($hasIva) {
                    $costBase = $costBase * 1.16;
                }

                if ($currency === 'BS') {
                    $costUsdCalculated = ($rate > 0) ? ($costBase / $rate) : $costBase;
                } else {
                    $costUsdCalculated = $costBase;
                }

                $realQuantity = ($buyFormat === 'pack') ? ($qty * $unitsPerPack) : $qty;

                $this->items[$index]['cost_unit_usd'] = round($costUsdCalculated, 4);
                $this->items[$index]['subtotal'] = round($realQuantity * $costUsdCalculated, 2);
                $this->items[$index]['real_inventory_qty'] = $realQuantity;
            }

            $this->calculateTotal();

            // 🧮 LÓGICA DE CONTROL DE SALDOS (En Dólares)
            if ($this->status === 'pagada') {
                $this->amount_paid = $this->total;
                $this->balance_due = 0;
            } elseif ($this->status === 'credito') {
                $this->amount_paid = 0;
                $this->balance_due = $this->total;
            } elseif ($this->status === 'parcial') {
                // Forzamos que sea flotante lo que introduce el usuario
                $paid = (float) $this->amount_paid;

                // Si el usuario borra o mete un monto mayor por error, lo limitamos al total
                if ($paid > $this->total) {
                    $this->amount_paid = $this->total;
                    $paid = $this->total;
                }

                $this->balance_due = round($this->total - $paid, 2);
            }
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
            'exchange_rate' => 'required|numeric|min:0.01',
            'status' => 'required|in:pagada,credito,parcial',
            // Si es crédito o parcial, la fecha de vencimiento pasa a ser obligatoria
            'due_date' => in_array($this->status, ['credito', 'parcial']) ? 'required|date|after_or_equal:purchase_date' : 'nullable',
            'amount_paid' => $this->status === 'parcial' ? 'required|numeric|min:0.01' : 'nullable',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.cost_unit_usd' => 'required|numeric|min:0',
        ], [
            'exchange_rate.required' => 'Debe ingresar la tasa de cambio del día.',
            'due_date.required' => 'Debe indicar la fecha de vencimiento para el saldo pendiente.',
            'amount_paid.min' => 'El monto abonado debe ser mayor a cero en pagos parciales.'
        ]);

        DB::transaction(function () {
            // 1. Guardar la compra maestro incorporando abono y deuda
            $purchase = Purchase::create([
                'provider_id' => $this->provider_id,
                'branch_id' => $this->branch_id,
                'invoice_number' => $this->invoice_number,
                'purchase_date' => $this->purchase_date,
                'total_usd' => $this->total,
                'status' => $this->status,
                'due_date' => in_array($this->status, ['credito', 'parcial']) ? $this->due_date : null,
                'amount_paid' => $this->amount_paid,
                'balance_due' => $this->balance_due,
            ]);

            // 2. Crear detalles y actualizar STOCK (Se mantiene intacto e impecable)
            foreach ($this->items as $item) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_unit_usd' => $item['cost_unit_usd'],
                    'subtotal_usd' => $item['subtotal'],
                ]);

                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->update([
                        'cost_usd' => $item['cost_unit_usd']
                    ]);
                }

                $stockRecord = ProductBranch::firstOrCreate(
                    ['product_id' => $item['product_id'], 'branch_id' => $this->branch_id],
                    ['stock' => 0]
                );
                $stockRecord->increment('stock', $item['real_inventory_qty']);
            }
        });

        return redirect()->to('/productos')->with('message',
        'Compra y estados de cuenta procesados con éxito.');
    }

    public function render()
    {
        return view('livewire.purchase-manager', [
            'providers' => Provider::all(),
            'branches' => Branch::all(),
            'products' => Product::all(),
        ]);
    }
}
