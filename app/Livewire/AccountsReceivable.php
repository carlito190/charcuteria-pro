<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;


class AccountsReceivable extends Component
{
    use WithPagination;

    // Propiedades de búsqueda y visualización
    public $search = '';
    public $selected_client = null;
    public $debts = [];

    // Propiedades para el formulario de abono/pago
    public $show_payment_modal = false;
    public $payment_method = 'Efectivo';
    public $currency = 'USD';
    public $amount_received = '';
    public $exchange_rate = 59.50; // Ajusta a tu tasa del día o variable global
    public $reference = '';
    public $selected_invoice_details = null;
    public $invoice_items = [];

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Muestra el desglose de deudas de un cliente específico
     */
    public function selectClient($clientId)
    {
        // 1. Cargamos el cliente seleccionado
        $this->selected_client = Client::findOrFail($clientId);

        // 2. Buscamos las ventas cruzando filtros de forma segura
        $this->debts = DB::table('sales')
            ->join('sales_payments', 'sales.id', '=', 'sales_payments.sale_id')
            ->leftJoin('branches', 'sales.branch_id', '=', 'branches.id')
            ->where('sales_payments.payment_method', 'credito')

            // 🔥 CORRECCIÓN AQUÍ: Aceptamos ambos estados para que no se vacíe con el histórico viejo
            ->whereIn('sales.status', ['credito', 'completada'])

            ->where(function($query) {
                $query->where('sales.client_id', $this->selected_client->id)
                    ->orWhere('sales.client_id_number', $this->selected_client->id_number);
            })
            ->select(
                'sales.id',
                'sales.status',
                'sales.invoice_number',
                'sales.date_sale',
                'sales.total as total_bs',
                'sales_payments.exchange_rate',
                'branches.name as branch_name',
                // Conversión matemática exacta en base a la tasa de la transacción
                DB::raw('(sales_payments.amount / sales_payments.exchange_rate) as total_usd')
            )
            ->orderBy('sales.date_sale', 'desc')
            ->get();
    }

    public function viewInvoiceDetails($saleId)
    {
        // 1. Buscamos los datos generales de la venta
        $sale = DB::table('sales')->where('id', $saleId)->first();

        if ($sale) {
            $this->selected_invoice_details = [
                'number' => $sale->invoice_number,
                'total' => $sale->total,
                'status' => $sale->status,
            ];

            // 2. 🔥 LA CONSULTA EXACTA: Buscamos los items cruzando con la tabla de productos
            // Cambia 'products' y 'products.name' por cómo se llamen tu tabla y columna de productos reales
            $this->invoice_items = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sale_items.sale_id', $saleId)
                ->select(
                    'sale_items.quantity',   // 👈 Campo exacto de tu captura
                    'sale_items.subtotal',   // 👈 Campo exacto de tu captura
                    'products.name as product_name' // 🔥 Traemos el nombre real desde la tabla products
                )
                ->get();
        }
    }

    // 3. Método para cerrar el visor de la factura
    public function closeInvoiceDetails()
    {
        $this->selected_invoice_details = null;
        $this->invoice_items = [];
    }

    /**
     * Abre el modal de abonos
     */
    public function openPaymentModal()
    {
        if (!$this->selected_client) return;
        $this->amount_received = '';
        $this->reference = '';
        $this->show_payment_modal = true;
    }

    /**
     * Registra el abono del cliente a su deuda
     */
    public function registerAbono()
    {
        $this->validate([
            'amount_received' => 'required|numeric|min:0.01',
            'payment_method' => 'required',
            'currency' => 'required',
        ]);

        DB::transaction(function () {
            // 1. Calcular el valor del abono en USD (que es como guardas tu current_balance)
            $amountInUSD = (float)$this->amount_received;
            if ($this->currency === 'VES') {
                $amountInUSD = (float)$this->amount_received / (float)$this->exchange_rate;
            }

            $amountInUSD = round($amountInUSD, 2);

            // 2. Descontar del balance del cliente
            $client = Client::findOrFail($this->selected_client->id);
            $client->decrement('current_balance', $amountInUSD);

            // 3. Registrar el ingreso en una tabla de "payments_abonos" o "caja" (Opcional según tus modelos)
            // Aquí puedes disparar una inserción si tienes un modelo de historial de abonos de CxC.
        });

        $this->show_payment_modal = false;
        $this->selectClient($this->selected_client->id); // Refrescar datos del cliente activo
        session()->flash('success', 'Abono registrado con éxito. La deuda ha sido actualizada.');
    }

    public function render()
    {
        // Listamos los clientes que DEBEN dinero y filtramos por nombre/cédula
        $clients = Client::where('current_balance', '>', 0)
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('id_number', 'like', '%' . $this->search . '%');
            })
            ->orderBy('current_balance', 'desc')
            ->paginate(10);

        return view('livewire.accounts-receivable', [
            'clients' => $clients
        ]);
    }
}
