<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;

class BranchManager extends Component
{
    // Aquí deben estar todas las propiedades que usa la vista
    public $branch_id; // <--- ESTA ES LA QUE FALTA
    public $name;
    public $address;
    public $phone;
    public $is_open = false;

    public function render()
    {
        return view('livewire.branch-manager', [
            'branches' => Branch::all()
        ]);
    }

    public function create()
    {
        $this->resetFields();
        $this->is_open = true;
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        $this->branch_id = $id;
        $this->name = $branch->name;
        $this->address = $branch->address;
        $this->phone = $branch->phone;
        $this->is_open = true;
    }

    public function store()
    {
        $this->validate(['name' => 'required']);

        Branch::updateOrCreate(['id' => $this->branch_id], [
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
        ]);

        $this->is_open = false;
        $this->resetFields();
    }

    private function resetFields() {
        $this->name = ''; $this->address = ''; $this->phone = ''; $this->branch_id = null;
    }
}
