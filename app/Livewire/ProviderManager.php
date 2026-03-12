<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Provider;
use Livewire\WithPagination;

class ProviderManager extends Component
{
    use WithPagination;

    public $search = '';
    public $provider_id, $rif, $name, $email, $phone, $address, $contact_person;
    public $is_open = false; // Controla el modal

    public function render()
    {
        return view('livewire.provider-manager', [
            'providers' => Provider::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('rif', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10)
        ]);
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
        $this->rif = ''; $this->name = ''; $this->email = '';
        $this->phone = ''; $this->address = ''; $this->contact_person = '';
        $this->provider_id = null;
    }

    public function store()
    {
        $this->validate([
            'rif' => 'required|unique:providers,rif,' . $this->provider_id,
            'name' => 'required',
        ]);

        Provider::updateOrCreate(['id' => $this->provider_id], [
            'rif' => $this->rif,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'contact_person' => $this->contact_person,
        ]);

        session()->flash('message', $this->provider_id ? 'Proveedor Actualizado' : 'Proveedor Creado');
        $this->closeModal();
        $this->resetFields();
    }

    public function edit($id)
    {
        $provider = Provider::findOrFail($id);
        $this->provider_id = $id;
        $this->rif = $provider->rif;
        $this->name = $provider->name;
        $this->email = $provider->email;
        $this->phone = $provider->phone;
        $this->address = $provider->address;
        $this->contact_person = $provider->contact_person;
        $this->openModal();
    }
}
