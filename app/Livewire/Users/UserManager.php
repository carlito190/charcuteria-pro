<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Branch; // Asumiendo que tu modelo de sedes se llama Branch
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManager extends Component
{
    use WithPagination;

    // Propiedades de búsqueda y control visual
    public $search = '';
    public $is_modal_open = false;
    public $is_editing = false;

    // Propiedades del Formulario (Campos del Usuario)
    public $user_id;
    public $name;
    public $email;
    public $password;
    public $role = 'worker_branch'; // Rol por defecto
    public $branch_id = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Abre el modal para crear un usuario nuevo
    public function openCreateModal()
    {
        $this->resetForm();
        $this->is_editing = false;
        $this->is_modal_open = true;
    }

    // Abre el modal cargando los datos del usuario a editar
    public function openEditModal($userId)
    {
        $this->resetForm();
        $user = User::findOrFail($userId);
        
        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->branch_id = $user->branch_id ?? '';
        
        $this->is_editing = true;
        $this->is_modal_open = true;
    }

    public function closeModal()
    {
        $this->is_modal_open = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->user_id = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'worker_branch';
        $this->branch_id = '';
        $this->resetErrorBag();
    }

    // Guarda o Actualiza el registro
    public function saveUser()
    {
        // Reglas de validación dinámicas
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user_id)],
            'role' => 'required|in:admin_global,admin_branch,worker_branch',
            // El branch_id es requerido a menos que sea Admin Global, que puede ser nulo
            'branch_id' => $this->role === 'admin_global' ? 'nullable' : 'required',
        ];

        // La contraseña solo es obligatoria si es un usuario nuevo
        if (!$this->is_editing) {
            $rules['password'] = 'required|min:6';
        } else {
            $rules['password'] = 'nullable|min:6';
        }

        $this->validate($rules);

        if ($this->is_editing) {
            // EDICIÓN
            $user = User::findOrFail($this->user_id);
            $user->name = $this->name;
            $user->email = $this->email;
            $user->role = $this->role;
            $user->branch_id = $this->role === 'admin_global' ? null : $this->branch_id;
            
            // Solo actualiza la contraseña si escribieron algo en el campo
            if (!empty($this->password)) {
                $user->password = Hash::make($this->password);
            }
            $user->save();
            
            session()->flash('message', '¡Usuario actualizado con éxito!');
        } else {
            // CREACIÓN NUEVA
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'branch_id' => $this->role === 'admin_global' ? null : $this->branch_id,
                'password' => Hash::make($this->password),
            ]);
            
            session()->flash('message', '¡Usuario registrado correctamente!');
        }

        $this->closeModal();
    }

    // Eliminar un usuario de forma segura
    public function deleteUser($userId)
    {
        // Evitar que te auto-elimines
        if ($userId === auth()->user()->id) {
            session()->flash('error', 'No puedes eliminar tu propio usuario logueado.');
            return;
        }

        User::findOrFail($userId)->delete();
        session()->flash('message', 'Usuario eliminado del sistema.');
    }

    public function render()
    {
        // Traemos los usuarios que coincidan con la búsqueda
        $users = User::with('branch')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        // Traemos todas las sedes disponibles para el menú desplegable del formulario
        $branches = Branch::all();

        return view('livewire.users.user-manager', [
            'users' => $users,
            'branches' => $branches
        ]);
    }
}
