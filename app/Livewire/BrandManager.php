<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Brand;
use Livewire\WithPagination;

class BrandManager extends Component
{
    use WithPagination;

    public $search = '';
    public $brand_id, $name;
    public $is_open = false;

    // Reglas de validación dinámicas
    protected function rules()
    {
        return [
            'name' => 'required|min:2|unique:brands,name,' . $this->brand_id,
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre de la marca es obligatorio.',
        'name.min' => 'El nombre debe tener al menos 2 caracteres.',
        'name.unique' => 'Esta marca ya se encuentra registrada.',
    ];

    public function render()
    {
        $brands = Brand::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('livewire.brand-manager', [
            'brands' => $brands
        ]);
    }

    public function create()
    {
        $this->resetFields();
        $this->openModal();
    }

    public function openModal() { $this->is_open = true; }
    
    public function closeModal() 
    { 
        $this->is_open = false; 
        $this->resetValidation();
    }

    private function resetFields()
    {
        $this->name = '';
        $this->brand_id = null;
    }

    public function store()
    {
        $this->validate();

        Brand::updateOrCreate(['id' => $this->brand_id], [
            'name' => trim($this->name),
        ]);

        session()->flash('message', $this->brand_id ? 'Marca actualizada con éxito.' : 'Marca registrada con éxito.');
        
        $this->closeModal();
        $this->resetFields();
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        $this->brand_id = $id;
        $this->name = $brand->name;
        
        $this->openModal();
    }

    public function delete($id)
    {
        $brand = Brand::findOrFail($id);
        
        // Verificamos si la marca tiene productos asociados antes de borrar
        if ($brand->products()->count() > 0) {
            session()->flash('error', "No se puede eliminar la marca '{$brand->name}' porque tiene productos vinculados en el inventario.");
            return;
        }

        $brand->delete();
        session()->flash('message', 'Marca eliminada correctamente.');
    }
}
