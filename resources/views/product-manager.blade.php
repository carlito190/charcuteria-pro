<div class="p-6">
    <div class="flex justify-between mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar producto..." class="form-input rounded-md shadow-sm w-1/3">
        <button wire:click="create()" class="bg-indigo-600 text-white px-4 py-2 rounded-md">
            Nuevo Producto
        </button>
    </div>

    <table class="table-auto w-full bg-white shadow-sm rounded-lg">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left">Producto</th>
                <th class="px-4 py-2 text-left">Categoría</th>
                <th class="px-4 py-2 text-right">Costo ($)</th>
                <th class="px-4 py-2 text-right">Venta ($)</th>
                <th class="px-4 py-2 text-right text-blue-600">Venta (Bs)</th>
                <th class="px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $product->name }}</td>
                <td class="px-4 py-2">{{ $product->category->name }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($product->cost_usd, 2) }}</td>
                <td class="px-4 py-2 text-right font-bold">{{ number_format($product->selling_price_usd, 2) }}</td>
                <td class="px-4 py-2 text-right text-blue-600 font-bold">
                    {{ number_format($product->price_bs, 2) }}
                </td>
                <td class="px-4 py-2 text-center">
                    <button wire:click="edit({{ $product->id }})" class="text-indigo-600">Editar</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $products->links() }}
</div>
