<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreateSale extends Component
{
    // Datos del cliente y la sede
    public $client_name = 'Cliente Frecuente';
    public $client_id_number = null;
    public $branch_id; // Se captura de la sesión o del usuario logueado

    // Buscador de productos
    public $search = '';
    public $selected_product_id = null;
    public $quantity = 1;
    public $current_stock = 0.000;
    public $unit_type = 'UND';

    // Carrito de compras y totales
    public $cart = [];
    public $total = 0.00;

    // Control de Pagos Combinados
    public $payments_added = [];
    public $payment_method = 'punto';
    public $currency = 'VES';
    public $payment_amount = 0.00;
    public $exchange_rate = 40.50; // Puedes precargar la tasa del BCV/sistema aquí
    public $pending_amount = 0.00;
    public $input_type = 'weight'; // 'weight', 'money_bs', 'money_usd'
    public $money_input_bs = '';
    public $money_input_usd = '';
    // Ventas Diarias
    public $show_daily_totals_modal = false;
    public $daily_totals_by_method = [];
    public $grand_daily_total = 0.00;
    //Fechas
    public $date_from;
    public $date_to;
   

    public function mount()
    {
        // Asignamos la sede del usuario que está cobrando
        // Reemplaza esto con tu lógica de sesión si usas otra (ej: session('branch_id'))
        $this->branch_id = Auth::user()->branch_id ?? 1;
        // Buscamos la última tasa guardada en tu tabla de tasas de cambio
        // NOTA: Reemplaza \App\Models\ExchangeRate por tu modelo real (ej: TasaCambio)
        $tasaRecord = \App\Models\ExchangeRate::latest()->first();
    
        // Si encuentra la tasa en la BD, la usa; si no, deja una por defecto para no romper el sistema
        $this->exchange_rate = $tasaRecord ? (float) $tasaRecord->rate : 40.50;
        $this->calculateTotals();
    }

    public function openDailyTotals()
    {
        $this->daily_totals_by_method = [];
        $this->grand_daily_total = 0.00;

        // Si las fechas están vacías (primera vez que abre), ponemos el día de hoy por defecto
        if (!$this->date_from) {
            $this->date_from = now()->format('Y-m-d');
        }
        if (!$this->date_to) {
            $this->date_to = now()->format('Y-m-d');
        }

        // Convertimos las fechas seleccionadas a rangos de inicio de día y fin de día absolutos
        $start = \Carbon\Carbon::parse($this->date_from)->startOfDay();
        $end = \Carbon\Carbon::parse($this->date_to)->endOfDay();

        // Consultamos los pagos asociados a las ventas de la sede dentro del rango de fechas
        $payments = \App\Models\SalePayment::whereHas('sale', function($query) use ($start, $end) {
                $query->where('branch_id', $this->branch_id)
                    ->whereBetween('created_at', [$start, $end]);
            })
            ->get();

        // Agrupamos dinámicamente según lo que consiga en la base de datos (Efectivo, Pago Móvil, Bio Pago, etc.)
        $grouped = $payments->groupBy('payment_method');

        foreach ($grouped as $method => $allPayments) {
            $this->daily_totals_by_method[$method] = $allPayments->sum('amount');
        }

        // Sumatoria total global del período seleccionado
        $this->grand_daily_total = $payments->sum('amount');

        $this->show_daily_totals_modal = true;
    }

    public function updatedDateFrom()
        {
            $this->openDailyTotals();
        }

    public function updatedDateTo()
        {
            $this->openDailyTotals();
        }

    public function updatedSearch()
    {
        // Aquí manejaremos el autocompletado de productos
    }

    public function updatedMoneyInputBs()
    {
        if (!$this->selected_product_id || (float)$this->money_input_bs <= 0) {
            $this->quantity = 0.000;
            return;
        }

        $product = Product::find($this->selected_product_id);
        $priceInBs = ((float)$product->cost_usd * (1 + ((float)$product->profit_margin / 100))) * $this->exchange_rate;

        // Cantidad = Dinero / Precio por KG
        $this->quantity = round((float)$this->money_input_bs / $priceInBs, 3);
    }

    public function updatedMoneyInputUsd()
    {
        if (!$this->selected_product_id || (float)$this->money_input_usd <= 0) {
            $this->quantity = 0.000;
            return;
        }

        $product = Product::find($this->selected_product_id);
        $priceInUsd = (float)$product->cost_usd * (1 + ((float)$product->profit_margin / 100));

        // Cantidad = Dinero / Precio por KG
        $this->quantity = round((float)$this->money_input_usd / $priceInUsd, 3);
    }

    // Limpiar los campos de dinero al cambiar de tipo de entrada
    public function updatedInputType()
    {
        $this->reset(['money_input_bs', 'money_input_usd']);
        $this->quantity = ($this->unit_type === 'KG') ? 0.000 : 1;
    }

    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        $this->selected_product_id = $product->id;
        $this->unit_type = $product->unit_type ?? 'UND'; // KG o UND

        // Buscamos el stock real en la tabla product_branches que corregimos antes
        $stockRecord = ProductBranch::where('product_id', $product->id)
                                    ->where('branch_id', $this->branch_id)
                                    ->first();

        $this->current_stock = $stockRecord ? (float) $stockRecord->stock : 0.000;
        
        // Si es por KG sugerimos 0, si es Unidad sugerimos 1
        $this->stock = ($this->unit_type === 'KG') ? 0.000 : 1;
        $this->search = '';
        $this->input_type = 'weight';
        $this->reset(['money_input_bs', 'money_input_usd']);
    }

    public function addToCart()
    {
        // 1. Validar que seleccionó un producto
        if (!$this->selected_product_id) return;

        $product = Product::find($this->selected_product_id);

        // 2. Validar stock disponible
        if ((float)$this->quantity > $this->current_stock) {
            session()->flash('error', "Stock insuficiente en esta sede. Disponible: " . number_format($this->current_stock, 3));
            return;
        }
        
        // 1. Convertimos los valores de la BD a números flotantes por seguridad
        $costUsd = (float) $product->cost_usd;
        $profitMargin = (float) $product->profit_margin; // Ejemplo: 30 (significa 30%)

        // 2. CALCULO 1: Determinar el precio de venta en DÓLARES aplicando el margen
        $priceInUsd = $costUsd * (1 + ($profitMargin / 100));

        // 3. CÁLCULO 2: Convertir ese precio en dólares a BOLÍVARES usando la tasa del día
        $priceInBolivares = $priceInUsd * $this->exchange_rate;

        // 4. Calcular el subtotal de la línea en Bolívares
        $subtotal = (float)$this->quantity * $priceInBolivares;

        // 5. Guardamos el ítem estructurado en el carrito
        $this->cart[$product->id] = [
            'product_id'   => $product->id,
            'name'         => $product->name,
            'quantity'     => (float)$this->quantity,
            'unit_type'    => $this->unit_type,
            'price_cost'   => $costUsd,          // Guardamos el costo base por si acaso
            'price_usd'    => $priceInUsd,        // Precio de venta en $ (con ganancia)
            'price'        => $priceInBolivares,  // Precio de venta en Bs (con ganancia y tasa)
            'subtotal'     => $subtotal
        ];

        // 4. Limpiar buscador para el siguiente artículo
        $this->reset(['search', 'selected_product_id', 'quantity', 'current_stock']);
        $this->calculateTotals();
    }

    public function removeItem($productId)
    {
        unset($this->cart[$productId]);
        $this->calculateTotals();
    }

    public function addPayment()
    {
        if ((float)$this->payment_amount <= 0) return;

        // Si pagan en dólares, convertimos el abono a la moneda base (Bolívares) para el total
        $amountInBaseCurrency = (float)$this->payment_amount;
        if ($this->currency === 'USD') {
            $amountInBaseCurrency = (float)$this->payment_amount * (float)$this->exchange_rate;
        }

        $this->payments_added[] = [
            'payment_method' => $this->payment_method,
            'currency' => $this->currency,
            'amount_received' => (float)$this->payment_amount, // Lo que entregó físicamente
            'exchange_rate' => $this->currency === 'USD' ? (float)$this->exchange_rate : null,
            'amount' => $amountInBaseCurrency // Su valor equivalente en Bs
        ];

        $this->reset(['payment_amount']);
        $this->calculateTotals();
    }

    public function removePayment($index)
    {
        unset($this->payments_added[$index]);
        $this->payments_added = array_values($this->payments_added); // Reindexar array
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        // Sumar subtotales del carrito
        $this->total = collect($this->cart)->sum('subtotal');

        // Sumar lo que ya han abonado en los métodos de pago
        $totalPaid = collect($this->payments_added)->sum('amount');

        // Calcular cuánto falta por pagar
        $this->pending_amount = $this->total - $totalPaid;
    }

    public function saveSale()
    {
        // El botón de cobrar llamará a esto
        if ($this->pending_amount > 0.01) {
            session()->flash('error', 'Aún queda un saldo pendiente por cubrir.');
            return;
        }

        if (count($this->cart) === 0) return;

        DB::transaction(function () {
            // 1. Generar número correlativo automático (puedes mejorar esta lógica después)
            $invoiceNumber = 'V-' . str_pad(Sale::count() + 1, 6, '0', STR_PAD_LEFT);

            // 2. Crear cabecera
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'branch_id' => $this->branch_id,
                'user_id' => Auth::id() ?? 1, // Por si estás probando sin login
                'client_name' => $this->client_name,
                'client_id_number' => $this->client_id_number,
                'total' => $this->total,
            ]);

            // 3. Guardar artículos y restar stock de la sede
            foreach ($this->cart as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_type' => $item['unit_type'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Descontar de la sede correspondiente
                ProductBranch::where('product_id', $item['product_id'])
                    ->where('branch_id', $this->branch_id)
                    ->decrement('stock', $item['quantity']);
            }

            // 4. Guardar los múltiples métodos de pago aplicados
            foreach ($this->payments_added as $payment) {
                $sale->payments()->create([
                    'payment_method' => $payment['payment_method'],
                    'currency' => $payment['currency'],
                    'amount' => $payment['amount'], // Guardamos el equivalente en Bs
                    'exchange_rate' => $payment['exchange_rate'],
                ]);
            }
        });

        session()->flash('success', 'Venta procesada con éxito.');
        return redirect()->route('sales.create'); // O limpiar el componente para otra venta
    }

    public function render()
    {
       return view('livewire.sales.create-sale');
    }
}