<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;

class ExchangeRateManager extends Component
{
    public $currentRate;
    public $manualRate;

    public function mount()
    {
            $lastRecord = ExchangeRate::latest()->first();
        $this->currentRate = $lastRecord ? $lastRecord->rate : 0;

        // Al cargar la página, el input ya tendrá el valor actual
        $this->manualRate = $this->currentRate;
    }

    // Opción A: Buscar en la API (BCV)
    public function fetchFromApi()
    {
        try {
            // Usamos la URL oficial que pasaste antes
            $response = Http::get('https://ve.dolarapi.com/v1/dolares/oficial');

            if ($response->successful()) {
                $data = $response->json();

                // Aquí está el truco: 'promedio' es la clave del JSON que nos interesa
                // Al hacer esto, el 0 del input cambiará al valor real (ej. 419.98)
                $this->manualRate = $data['promedio'];

                session()->flash('message', 'Tasa obtenida de la API: ' . $this->manualRate);
            }
        } catch (\Exception $e) {
            session()->flash('message', 'Error: No se pudo conectar con la API.');
        }
    }

    // Opción B: Guardar manual
    public function saveManual()
    {
        $this->validate(['manualRate' => 'required|numeric|min:1']);
        $this->saveRate($this->manualRate);
        $this->manualRate = null;
        session()->flash('message', 'Tasa actualizada manualmente.');
    }

    private function saveRate($value)
    {
        ExchangeRate::create(['rate' => $value]);
        $this->currentRate = $value;

        // Esto avisa a otros componentes (como la tabla de productos) que la tasa cambió
        $this->dispatch('rate-updated');
    }

    public function render()
    {
        return view('livewire.exchange-rate-manager');
    }
}
