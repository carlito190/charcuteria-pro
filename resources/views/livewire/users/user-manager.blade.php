<div class="p-6 max-w-7xl mx-auto">
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg font-medium text-sm">
            ✅ {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded-lg font-medium text-sm">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-black text-gray-800">👥 Control de Personal y Accesos</h2>
            <p class="text-xs text-gray-500">Administra los accesos de cajeros y encargados por cada sede del Market.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow transition">
            ➕ Registrar Nuevo Usuario
        </button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow border mb-6">
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar usuario por nombre o correo..." class="w-full md:w-1/3 text-sm px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="bg-white rounded-xl shadow border overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800 text-white text-sm font-bold">
                    <th class="p-4 bg-slate-800 text-white">Nombre Completo</th>
                    <th class="p-4 bg-slate-800 text-white">Correo Electrónico</th>
                    <th class="p-4 bg-slate-800 text-white text-center">Nivel / Rol</th>
                    <th class="p-4 bg-slate-800 text-white">Sede Asignada</th>
                    <th class="p-4 bg-slate-800 text-white text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                @forelse($users as $usr)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 font-bold text-slate-900">{{ $usr->name }}</td>
                        <td class="p-4 font-medium text-gray-600">{{ $usr->email }}</td>
                        <td class="p-4 text-center">
                            @if($usr->role === 'admin_global')
                                <span class="bg-purple-100 text-purple-800 text-xs font-black px-2.5 py-1 rounded border border-purple-200 uppercase">Admin Global</span>
                            @elseif($usr->role === 'admin_branch')
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-1 rounded border border-blue-200 uppercase">Encargado Sede</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-1 rounded border border-gray-200 uppercase">Cajero / Trabajador</span>
                            @endif
                        </td>
                        <td class="p-4 font-semibold text-gray-800">
                            {{ $usr->branch->name ?? 'Todas las Sedes (Global)' }}
                        </td>
                        <td class="p-4 text-center space-x-2 whitespace-nowrap">
                            <button type="button" wire:click="openEditModal({{ $usr->id }})" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-2.5 py-1.5 rounded text-xs transition border border-slate-300">
                                ✏️ Editar
                            </button>
                            <button type="button" onclick="confirm('¿Estás seguro de quitar el acceso a este usuario?') || event.stopImmediatePropagation()" wire:click="deleteUser({{ $usr->id }})" class="bg-red-50 hover:bg-red-100 text-red-600 font-bold px-2.5 py-1.5 rounded text-xs transition border border-red-200">
                                🗑️ Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400 italic">No hay usuarios registrados que coincidan con los criterios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 bg-gray-50 border-t">
            {{ $users->links() }}
        </div>
    </div>

    @if($is_modal_open)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none">
            <div class="fixed inset-0 bg-black opacity-50" wire:click="closeModal"></div>
            
            <div class="relative w-full max-w-md mx-auto my-6 z-50 p-4">
                <form wire:submit.prevent="saveUser" class="relative flex flex-col w-full bg-white border-0 rounded-xl shadow-2xl overflow-hidden">
                    
                    <div class="flex items-center justify-between p-4 bg-slate-800 text-white">
                        <h3 class="text-base font-black">{{ $is_editing ? '✏️ Modificar Usuario' : '➕ Registrar Nuevo Personal' }}</h3>
                        <button type="button" wire:click="closeModal" class="text-white hover:text-gray-300 font-bold text-lg">✕</button>
                    </div>
                    
                    <div class="p-6 space-y-4 text-sm text-gray-700">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                            <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('name') <span class="text-xs text-red-600 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Correo Electrónico (Usuario)</label>
                            <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('email') <span class="text-xs text-red-600 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nivel de Acceso (Rol)</label>
                            <select wire:model.live="role" class="w-full px-3 py-2 border rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="worker_branch">Cajero / Trabajador de Sede</option>
                                <option value="admin_branch">Encargado / Administrador de Sede</option>
                                <option value="admin_global">Dueño / Administrador Global</option>
                            </select>
                            @error('role') <span class="text-xs text-red-600 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        @if($role !== 'admin_global')
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Asignar Sucursal (Sede)</label>
                                <select wire:model="branch_id" class="w-full px-3 py-2 border rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Seleccione una Sede --</option>
                                    @foreach($branches as $br)
                                        <option value="{{ $br->id }}">{{ $br->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <span class="text-xs text-red-600 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contraseña</label>
                            <input type="password" wire:model="password" placeholder="{{ $is_editing ? 'Dejar en blanco si no deseas cambiarla' : 'Mínimo 6 caracteres' }}" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('password') <span class="text-xs text-red-600 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="p-4 bg-gray-50 border-t flex justify-end gap-2">
                        <button type="button" wire:click="closeModal" class="bg-gray-400 hover:bg-gray-500 text-white text-xs font-bold px-4 py-2 rounded-lg transition">
                            Cancelar
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 py-2 rounded-lg shadow transition">
                            {{ $is_editing ? 'Actualizar Datos' : 'Registrar Personal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>