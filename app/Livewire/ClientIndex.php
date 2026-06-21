<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use Livewire\WithPagination;

class ClientIndex extends Component
{
    use WithPagination;

    public $search = '';

    // Propiedades para ver el estado de cuenta de un cliente específico en un modal
    public $selected_client;
    public $show_statement_modal = false;

    // Resetear la paginación cuando se escribe en el buscador
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Carga el estado de cuenta detallado de un cliente
     */
    public function viewStatement($clientId)
    {
        $this->selected_client = \App\Models\Client::with(['sales' => function($q) {
        // 1. Buscamos en la relación 'payments' (ajusta el nombre si en tu modelo Sale se llama diferente, ej: salesPayments)
        $q->whereHas('payments', function($query) {
            $query->where('payment_method', 'credito');
        })
        // 2. Las ordenamos por fecha, de la más reciente a la más vieja
        ->orderBy('date_sale', 'desc');
        }])->findOrFail($clientId);

        $this->show_statement_modal = true;
    }

    /**
     * Genera la URL de WhatsApp con un mensaje personalizado de cobro
     */
    public function getWhatsAppLink($clientId)
    {
        $client = \App\Models\Client::findOrFail($clientId);

        $phone = preg_replace('/[^0-9]/', '', $client->phone);

        if (empty($phone)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'El cliente no tiene un teléfono válido.']);
            return '#';
        }

        if (strpos($phone, '58') !== 0) {
            $phone = '58' . ltrim($phone, '0');
        }

        // Usamos tu campo real: current_balance
        $debtUsd = number_format($client->current_balance ?? 0, 2);

        // SOLUCIÓN AL ERROR: Separamos el texto de la variable para que Laravel no se confunda
        $texto = "Hola *{$client->name}*, un saludo de parte de nuestra administración. Te escribimos para recordarte que tu estado de cuenta actual presenta un saldo pendiente de *Ref. " . $debtUsd . "* (o al cambio del día en Bs). Si deseas realizar un abono o pago móvil, nos avisas por esta vía. ¡Feliz día! 🧀🥖";

        $mensajeUrl = urlencode($texto);

        return "https://wa.me/{$phone}?text={$mensajeUrl}";
    }

    public function render()
    {
       // Cambiamos la búsqueda para que use 'id_number' en vez de 'dni'
        $clients = \App\Models\Client::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('id_number', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.client-index', [
            'clients' => $clients
        ]);
    }
}
