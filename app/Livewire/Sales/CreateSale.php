<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\Sale;
use App\Models\User;
use App\Models\Client;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class CreateSale extends Component
{
    // Datos del cliente y la sede
    public $client_name;
    public $client_id_number = null;
    public $branch_id;
    public $branch_name;
    public $is_global = false;
    public $branches = []; // Para guardar la lista de sedes si eres admin

    // Buscador de clientes
    public $search_client = '';
    public $client_results = [];
    public $selected_client_id = null;
    public $client_allow_credit = false;
    public $new_client_allow_credit = false;
    // 2. Propiedades para el formulario del nuevo cliente
    public $new_client_name;
    public $new_client_id_number; // Tu campo real
    public $new_client_phone;
    public $new_client_email;
    public $new_client_address;

    public $show_client_modal = false;

    // Buscador de productos
    public $search = '';
    public $products = [];
    public $selected_product_id = null;
    public $quantity = 1;
    public $current_stock = 0.000;
    public $unit_type = 'UND';
    public $stock = 1;

    // Carrito de compras y totales
    public $cart = [];
    public $total = 0.00;

    // Control de Pagos Combinados
    public $payments_added = [];
    public $payment_method = 'Efectivo'; // Valor inicial seguro
    public $currency = 'VES';
    public $payment_amount = '';
    public $exchange_rate = 40.50;
    public $credit_exchange_rate = 40.50; // Tasa específica para el crédito
    public $pending_amount = 0.00;
    public $input_type = 'weight';
    public $money_input_bs = '';
    public $money_input_usd = '';

    // Ventas Diarias / Arqueos
    public $show_daily_totals_modal = false;
    public $daily_totals_by_method = [];
    public $grand_daily_total = 0.00;
    public $date_from;
    public $date_to;
    public $date_sale;
    public $editing_sale_id = null;
    public $credit_amount_usd;



    public function mount()
    {
        // Cargar tasa de cambio oficial del sistema
        $tasaRecord = \App\Models\ExchangeRate::latest()->first();
        $this->exchange_rate = $tasaRecord ? (float) $tasaRecord->rate : 40.50;
        $this->credit_exchange_rate = $this->exchange_rate;

        $user = auth()->user();

        if ($user && $user->branch) {
            // Usuario normal con sede fija
            $this->branch_id = $user->branch_id;
            $this->branch_name = $user->branch->name;
            $this->is_global = false;
        } else {
            // Usuario Global / Administrador
            $this->is_global = true;
            $this->branches = Branch::all(); // Traemos todas las sedes (Principal, Industrial, etc.)

            // Asignamos la primera sede por defecto para que no empiece vacío
            $firstBranch = $this->branches->first();
            $this->branch_id = $firstBranch ? $firstBranch->id : null;
            $this->branch_name = $firstBranch ? $firstBranch->name : 'Sede Principal';
        }

        $this->date_sale = now()->format('Y-m-d H:i:s');
        //$this->date_sale = '2026-06-08';
        // 👈 Revisamos si viene un ID por la URL para editar
      if (request()->has('edit_id') && !request()->routeIs('livewire.update')) {
            $this->loadSaleForEditing(request('edit_id'));
        }
    }

    public function openClientModal()
    {
        $this->reset(['new_client_name', 'new_client_id_number', 'new_client_phone', 'new_client_email', 'new_client_address']);
        $this->show_client_modal = true;
    }

    /**
     * Registra al cliente en la base de datos y lo amarra a la venta actual
     */
    public function saveClient()
    {
        // Validaciones con tus campos reales
        $this->validate([
            'new_client_id_number' => 'required|string|unique:clients,id_number',
            'new_client_name'      => 'required|string|max:255',
            'new_client_phone'     => 'nullable|string',
            'new_client_email'     => 'nullable|email',
            'new_client_address'   => 'nullable|string',
        ], [
            'new_client_id_number.required' => 'La cédula o RIF es obligatoria.',
            'new_client_id_number.unique'   => 'Esta cédula o RIF ya está registrada.',
            'new_client_name.required'      => 'El nombre es obligatorio.',
            'new_client_email.email'        => 'El formato del correo no es válido.',
        ]);

        // Creamos el registro en la tabla de clientes
        $client = \App\Models\Client::create([
            'id_number'       => $this->new_client_id_number,
            'name'            => $this->new_client_name,
            'phone'           => $this->new_client_phone,
            'email'           => $this->new_client_email,
            'address'         => $this->new_client_address,
            'allow_credit'    => $this->new_client_allow_credit,
            'credit_limit'    => $this->new_client_allow_credit ? 50.00 : 0.00,
            'current_balance' => 0.00,
        ]);

        // Asignamos el ID del cliente recién creado a la venta actual
        // Cambia 'selected_client_id' por el nombre exacto de tu variable de cliente en la venta
        $this->selected_client_id = $client->id;

        // Opcional: si tienes un buscador de texto de cliente, puedes poner su nombre de una vez
        $this->search_client = $client->name;

        // Cerramos el modal
        $this->show_client_modal = false;

        // Notificación de éxito para el cajero
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Cliente registrado y seleccionado con éxito.']);
    }

    /**
     * PROPIEDAD COMPUTADA: Buscar productos y procesar sus precios
     * En Livewire se accede en la vista como $this->products
     */
    #[Computed]
    public function products()
    {
        if (empty(trim($this->search))) {
            return [];
        }

        // Forzamos que la tasa no sea cero
        if ((float) $this->exchange_rate <= 0) {
            $tasaRecord = \App\Models\ExchangeRate::latest()->first();
            $this->exchange_rate = $tasaRecord ? (float) $tasaRecord->rate : 40.50;
        }

        // Retornamos los productos limpios y directos de la base de datos
        return \App\Models\Product::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('barcode', 'like', '%' . $this->search . '%')
            ->with('brand')
            ->take(10)
            ->get();
    }

        // Al cambiar de sede en el selector, actualizamos el nombre dinámicamente
        public function updatedBranchId($value)
        {
            $branch = Branch::find($value);
            if ($branch) {
                $this->branch_name = $branch->name;
                // Aquí puedes agregar lógica si necesitas refrescar los stocks del producto al cambiar de sede
            }
            // 2. REFRESCAR EL STOCK: Si ya hay un producto seleccionado en el formulario,
            // volvemos a calcular su inventario basándonos en la nueva sede ($value).
            if ($this->selected_product_id) {
                $this->updateProductStock(); // <--- Llama aquí a tu función actual de calcular stock
            }

    }

    public function updateProductStock()
    {
        if (!$this->selected_product_id || !$this->branch_id) {
            $this->current_stock = 0;
            return;
        }

        // Supongamos que manejas una tabla pivote o un modelo 'Stock'
        // que relaciona producto y sucursal:
        $stockData = DB::table('product_branches') // o el nombre de tu tabla de inventario
            ->where('product_id', $this->selected_product_id)
            ->where('branch_id', $this->branch_id)
            ->first();

        // Actualizamos la propiedad que lee la vista
        $this->current_stock = $stockData ? $stockData->stock : 0;
    }

    // 1. Hook que se activa automáticamente al cambiar la tasa del crédito
    public function updatedCreditExchangeRate($value)
    {
        $this->recalculateCreditUSD($value);
    }

    // 2. Hook que se activa automáticamente si añaden/quitan cosas y cambia el total de la venta
    public function updatedTotal()
    {
        $this->recalculateCreditUSD($this->credit_exchange_rate);
    }

    // 3. Método auxiliar centralizado para hacer la matemática limpia
    private function recalculateCreditUSD($rateValue)
    {
        if ($this->payment_method === 'credito') {
            $rate = (float)$rateValue;

            // 💡 En lugar del total plano, usamos lo que falte por cobrar en bolívares
            // Si es el único pago, esto equivale al total de la venta (ej: 2449.41)
            $remainingBs = (float)$this->pending_amount;

            if ($rate > 0 && $remainingBs > 0) {
                // Calculamos los dólares puros con alta precisión
                $calculatedUSD = $remainingBs / $rate;

                // OPCIÓN DE ORO: Para evitar que el "Restante" quede con diferencias en Bs,
                // asignamos directamente los bolívares pendientes al pago en tu lógica interna de agregar pago,
                // pero en la interfaz mostramos el equivalente visual en dólares.
                $this->payment_amount = number_format($calculatedUSD, 2, '.', '');

                // 💡 TRUCO: Si tu método 'addPayment' usa la propiedad $this->payment_amount como los "Bolívares"
                // cuando procesa el carro, asegúrate de que se capturen los Bs exactos.
            } else {
                $this->payment_amount = 0.00;
            }
        }
    }

    // --- FLUJO DE CLIENTES ---
    public function updatedSearchClient()
    {
        if (strlen($this->search_client) < 2) {
            $this->client_results = [];
            return;
        }

        $this->client_results = Client::where('name', 'like', '%' . $this->search_client . '%')
            ->orWhere('id_number', 'like', '%' . $this->search_client . '%')
            ->take(5)
            ->get();
    }

    public function selectClient($clientId)
    {
        $client = Client::find($clientId);
        if (!$client) return;

        $this->selected_client_id = $client->id;
        $this->client_name = $client->name;
        $this->client_id_number = $client->id_number;
        $this->client_allow_credit = (bool) $client->allow_credit;

        $this->search_client = '';
        $this->client_results = [];

        // Reset seguro del método de pago al cambiar cliente
        $this->resetPaymentForm();
    }

    public function resetClient()
    {
        $this->selected_client_id = null;
        $this->client_name = 'Cliente Frecuente';
        $this->client_id_number = null;
        $this->client_allow_credit = false;
        $this->search_client = '';
        $this->client_results = [];

        $this->resetPaymentForm();
    }

    private function resetPaymentForm()
    {
        $this->payment_method = 'punto';
        $this->currency = 'VES';
        $this->payment_amount = '';
        $this->credit_exchange_rate = $this->exchange_rate;
    }

    // --- FLUJO DE PRODUCTOS ---
    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->products = [];
            return;
        }

        $this->products = Product::with('brand')
        ->search($this->search) // 👈 ¡Invocamos tu nuevo buscador centralizado!
        ->orderBy('name', 'asc')
        ->take(10)
        ->get();
    }

    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        $this->selected_product_id = $product->id;
        $this->unit_type = $product->unit_type ?? 'UND';

        $stockRecord = ProductBranch::where('product_id', $product->id)
                                    ->where('branch_id', $this->branch_id)
                                    ->first();

        $this->current_stock = $stockRecord ? (float) $stockRecord->stock : 0.000;
        $this->quantity = ($this->unit_type === 'KG') ? 0.000 : 1;

        $this->search = '';
        $this->products = [];
        $this->input_type = 'weight';
        $this->reset(['money_input_bs', 'money_input_usd']);
    }

    public function updatedMoneyInputBs()
    {
        if (!$this->selected_product_id || (float)$this->money_input_bs <= 0) {
            $this->quantity = 0.000;
            return;
        }

        $product = Product::find($this->selected_product_id);
        $priceInBs = ((float)$product->cost_usd * (1 + ((float)$product->profit_margin / 100))) * $this->exchange_rate;
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
        $this->quantity = round((float)$this->money_input_usd / $priceInUsd, 3);
    }

    public function updatedInputType()
    {
        $this->reset(['money_input_bs', 'money_input_usd']);
        $this->quantity = ($this->unit_type === 'KG') ? 0.000 : 1;
    }

    public function updatedPaymentMethod($value)
    {
        // Si es Divisas o Crédito, la moneda por defecto es Dólares (USD)
        if ($value === 'Divisas' || $value === 'credito') {
            $this->currency = 'USD';
        } else {
            $this->currency = 'VES'; // Efectivo, Pago Móvil, Bio Pago, Punto -> Bolívares
        }
    }

    public function addToCart()
    {
        if (!$this->selected_product_id) return;

        $product = Product::find($this->selected_product_id);

        if ((float)$this->quantity > $this->current_stock) {
            session()->flash('error', "Stock insuficiente. Disponible: " . number_format($this->current_stock, 3));
            return;
        }

        $priceInUsd = (float)$product->cost_usd * (1 + ((float)$product->profit_margin / 100));
        $priceInBolivares = $priceInUsd * $this->exchange_rate;
        $subtotal = (float)$this->quantity * $priceInBolivares;

        $this->cart[$product->id] = [
            'product_id'   => $product->id,
            'name'         => $product->name,
            'brand_name' => $product->brand ? $product->brand->name : null,
            'quantity'     => (float)$this->quantity,
            'unit_type'    => $this->unit_type,
            'price_cost'   => (float)$product->cost_usd,
            'price_usd'    => $priceInUsd,
            'price'        => $priceInBolivares,
            'subtotal'     => $subtotal
        ];

        $this->reset(['search', 'selected_product_id', 'quantity', 'current_stock', 'products']);
        $this->calculateTotals();
    }

    public function removeItem($index)
    {
        // Verificamos si existe la posición dentro del carrito
        if (isset($this->cart[$index])) {
            unset($this->cart[$index]);

            // 👈 REINDEXAMOS para que las llaves vuelvan a ser consecutivas (0, 1, 2...)
            $this->cart = array_values($this->cart);
        }

        // Recalculamos los totales al tiro
        $this->calculateTotals();
    }

    // --- PROCESAMIENTO DE PAGOS Y COBROS ---
    public function addPayment()
    {
        if ((float)$this->payment_amount <= 0) return;

        if ($this->payment_method === 'credito') {
            if (!$this->selected_client_id || !$this->client_allow_credit) {
                session()->flash('error', 'El cliente seleccionado no cuenta con autorización de crédito.');
                return;
            }
            $this->currency = 'USD'; // Fuerza indexación
        }

        $activeRate = ($this->payment_method === 'credito') ? (float)$this->credit_exchange_rate : (float)$this->exchange_rate;

        // -------------------------------------------------------------
        // 🔥 CORRECCIÓN DEL REDONDEO CRUZADO PARA CRÉDITO
        // -------------------------------------------------------------
        if ($this->payment_method === 'credito') {
            // En crédito, obligamos a que el monto en Bolívares sea EXACTAMENTE lo que falta por pagar
            // Así el restante baja a Bs. 0.00 de inmediato sin dejar residuos por decimales truncados.
            $amountInBaseCurrency = (float)$this->pending_amount;

            // El 'amount_received' conservará los dólares que calculó el hook (ej: 4.20)
            $amountReceived = (float)$this->payment_amount;
        } else {
            // Lógica normal para los demás métodos de pago (Efectivo, punto, etc.)
            $amountInBaseCurrency = (float)$this->payment_amount;
            if ($this->currency === 'USD') {
                $amountInBaseCurrency = (float)$this->payment_amount * $activeRate;
            }
            $amountReceived = (float)$this->payment_amount;
        }

        // Insertamos en el arreglo de pagos agregados
        $this->payments_added[] = [
            'payment_method' => $this->payment_method,
            'currency' => $this->currency,
            'amount_received' => $amountReceived, // Los dólares redondeados para el balance del cliente
            'exchange_rate' => $activeRate,
            'amount' => $amountInBaseCurrency // Los bolívares exactos para matar la factura (ej: 2449.41)
        ];

        // Limpieza de campos
        $this->payment_amount = '';

        // Si manejas una variable para la tasa del crédito en el formulario, la limpiamos también
        if (property_exists($this, 'credit_exchange_rate')) {
            $this->credit_exchange_rate = '';
        }

        $this->calculateTotals();
    }

    public function removePayment($index)
    {
        unset($this->payments_added[$index]);
        $this->payments_added = array_values($this->payments_added);
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        $this->total = collect($this->cart)->sum('subtotal');
        $totalPaid = collect($this->payments_added)->sum('amount');
        $this->pending_amount = $this->total - $totalPaid;
    }

    public function saveSale()
    {
        if ($this->pending_amount > 0.99) {
        session()->flash('error', 'Aún queda un saldo pendiente por cubrir.');
        return;
        }

        if (count($this->cart) === 0) return;

        DB::transaction(function () {

            // -------------------------------------------------------------
            // 1. SI ESTAMOS EDITANDO, REVERTIMOS INVENTARIO Y CRÉDITOS ANTERIORES
            // -------------------------------------------------------------
            if (!empty($this->editing_sale_id)) {
                $sale = Sale::with(['items', 'payments'])->findOrFail($this->editing_sale_id);

                // A) Devolver el stock viejo a su respectiva sucursal original
                foreach ($sale->items as $oldItem) {
                    ProductBranch::where('product_id', $oldItem->product_id)
                        ->where('branch_id', $sale->branch_id)
                        ->increment('stock', $oldItem->quantity);
                }

                // B) Revertir los saldos de crédito viejos del cliente
                foreach ($sale->payments as $oldPayment) {
                    if ($oldPayment->payment_method === 'credito') {
                        $clientOld = Client::where('id', $sale->client_id)
                            ->orWhere('id_number', $sale->client_id_number)
                            ->first();

                        if ($clientOld) {
                            $oldAmountInBs = (float)$oldPayment->amount;
                            $oldRate = (float)$oldPayment->exchange_rate;
                            $oldAmountInUSD = $oldRate > 0 ? ($oldAmountInBs / $oldRate) : 0;

                            $clientOld->decrement('current_balance', round($oldAmountInUSD, 2));
                        }
                    }
                }

                // Limpiamos las relaciones viejas (items y pagos) para meter los nuevos del carrito
                $sale->items()->delete();
                $sale->payments()->delete();

                $invoiceNumber = $sale->invoice_number;

            } else {
                // MODO NUEVO: Instanciamos un modelo limpio y generamos correlativo
                $sale = new Sale();
                $latestId = Sale::max('id') ?? 0;
                $invoiceNumber = 'V-' . str_pad($latestId + 1, 6, '0', STR_PAD_LEFT);
            }

            // -------------------------------------------------------------
            // 2. DETERMINAR EL ESTATUS DE LA VENTA DINÁMICAMENTE
            // -------------------------------------------------------------
            $saleStatus = 'completada';
            foreach ($this->payments_added as $payment) {
                if ($payment['payment_method'] === 'credito') {
                    $saleStatus = 'credito';
                    break;
                }
            }

            // Rescate inteligente de cliente
            $finalClientId = $this->selected_client_id;
            if (empty($finalClientId) && !empty($this->client_id_number)) {
                $onlyNumbers = preg_replace('/[^0-9]/', '', $this->client_id_number);
                $dbClient = Client::where('id_number', $this->client_id_number)
                    ->orWhere('id_number', 'LIKE', '%' . $onlyNumbers . '%')
                    ->first();

                if ($dbClient) {
                    $finalClientId = $dbClient->id;
                }
            }

            // -------------------------------------------------------------
            // 3. GUARDAR O ACTUALIZAR EL REGISTRO DE LA VENTA
            // -------------------------------------------------------------
            $sale->date_sale        = $this->date_sale;
            $sale->invoice_number   = $invoiceNumber;
            $sale->branch_id        = $this->branch_id;
            $sale->user_id          = Auth::id() ?? 1;
            $sale->client_id        = $finalClientId;
            $sale->client_name      = $this->client_name;
            $sale->client_id_number = $this->client_id_number;
            $sale->total            = $this->total;
            $sale->status           = $saleStatus; // Guardará 'credito' o 'completada' dinámicamente
            $sale->save(); // 👈 El método inteligente: Si tiene ID hace UPDATE, si no hace INSERT

            // -------------------------------------------------------------
            // 4. AGREGAR LOS NUEVOS ITEMS Y PAGOS ACTUALIZADOS
            // -------------------------------------------------------------
            foreach ($this->cart as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_type'  => $item['unit_type'],
                    'price'      => $item['price'],
                    'subtotal'   => $item['subtotal'],
                ]);

                ProductBranch::where('product_id', $item['product_id'])
                    ->where('branch_id', $this->branch_id)
                    ->decrement('stock', $item['quantity']);
            }

            foreach ($this->payments_added as $payment) {
                $sale->payments()->create([
                    'payment_method' => $payment['payment_method'],
                    'currency'       => $payment['currency'],
                    'amount'         => $payment['amount'],
                    'exchange_rate'  => $payment['exchange_rate'],
                ]);

                // Sumar la nueva deuda en dólares al cliente si aplica
                if ($payment['payment_method'] === 'credito') {
                    $client = Client::where('id', $this->selected_client_id)
                        ->orWhere('id_number', $this->client_id_number)
                        ->first();

                    if ($client) {
                        $amountInBs  = (float)$payment['amount'];
                        $rate        = (float)$payment['exchange_rate'];
                        $amountInUSD = $rate > 0 ? ($amountInBs / $rate) : 0;

                        $client->increment('current_balance', round($amountInUSD, 2));
                    }
                }
            }

            $this->date_sale = now()->format('Y-m-d H:i:s');
        });

        $message = !empty($this->editing_sale_id)
            ? 'Venta modificada con éxito. El inventario, los artículos y el crédito fueron recalculados sobre la misma factura.'
            : 'Venta procesada con éxito.';

        $this->editing_sale_id = null;

        session()->flash('success', $message);
        return redirect()->route('sales.create');
    }

    public function loadSaleForEditing($saleId)
    {
        $sale = Sale::with('items.product.brand')->findOrFail($saleId);
        $this->editing_sale_id = $sale->id;
        $this->selected_client_id = $sale->client_id;
        $this->client_name = $sale->client_name;
        $this->client_id_number = $sale->client_id_number;
        $this->branch_id = $sale->branch_id;

        // Limpiamos el carrito de la caja y cargamos los artículos viejos
        $this->cart = [];
        foreach ($sale->items as $item) {
            $this->cart[] = [
                'product_id' => $item->product_id,
                'name'       => $item->product->name,
                'brand_name' => $item->product->brand ? $item->product->brand->name : null,
                'price'      => $item->price,
                'quantity'   => $item->quantity,
                'unit_type'  => $item->unit_type ?? 'Unidad', // Asegúrate de incluir los campos obligatorios
                'subtotal'   => $item->price * $item->quantity,
            ];
        }
        // 👈 TAMBIÉN PODEMOS CARGAR LOS MÉTODOS DE PAGO VIEJOS SI LO DESEAS
        $this->payments_added = [];
        foreach ($sale->payments as $payment) {
            $this->payments_added[] = [
                'payment_method'  => $payment->payment_method,
                'currency'        => $payment->currency,
                'amount'          => (float)$payment->amount,
                'exchange_rate'   => (float)$payment->exchange_rate,
                'amount_received' => $payment->currency === 'USD' ? (float)$payment->amount : null, // Ajusta según tu estructura
            ];
        }
        $this->calculateTotals();
    }


    // --- ARQUEOS Y FECHAS ---
    public function openDailyTotals()
    {
        $this->daily_totals_by_method = [];
        $this->grand_daily_total = 0.00;

        if (!$this->date_from) $this->date_from = now()->format('Y-m-d');
        if (!$this->date_to) $this->date_to = now()->format('Y-m-d');

        $start = \Carbon\Carbon::parse($this->date_from)->startOfDay();
        $end = \Carbon\Carbon::parse($this->date_to)->endOfDay();

            // Buscamos los pagos cuyo modelo Sale asociado cumpla con los filtros
            $payments = \App\Models\SalePayment::whereHas('sale', function($query) use ($start, $end) {
            $query->where('branch_id', $this->branch_id)
                  ->whereBetween('date_sale', [$start, $end])
                  ->where(function($q) {
                      $q->where('status', 'completada')
                        ->orWhere('status', 'credito') // 👈 AGREGA ESTA LÍNEA
                        ->orWhereNull('status');
                  });
        })->get();

        $grouped = $payments->groupBy('payment_method');
        foreach ($grouped as $method => $allPayments) {
            $this->daily_totals_by_method[$method] = $allPayments->sum('amount');
        }

        $this->grand_daily_total = $payments->sum('amount');
        $this->show_daily_totals_modal = true;
    }

    public function updatedDateFrom() { $this->openDailyTotals(); }
    public function updatedDateTo() { $this->openDailyTotals(); }

    public function render()
    {
        return view('livewire.sales.create-sale');
    }
}
