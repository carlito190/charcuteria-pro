<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;

class CategoryManager extends Component
{
    public $name, $description, $category_id;
    public $is_open = false;

    public function render()
    {
        return view('livewire.category-manager', [
            'categories' => Category::latest()->get()
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->resetFields();
        $this->is_open = true;
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->category_id = $id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_open = true;
    }

    public function store()
    {
        $this->validate(['name' => 'required|unique:categories,name,' . $this->category_id]);

        Category::updateOrCreate(['id' => $this->category_id], [
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->is_open = false;
        $this->resetFields();
    }

    private function resetFields() {
        $this->name = ''; $this->description = ''; $this->category_id = null;
    }
}
