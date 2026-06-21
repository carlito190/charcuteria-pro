<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'Código de Barras',
            'Producto / Artículo',
            'Categoría',
            'Marca',
            'Costo ($)',
            'Precio Venta ($)',
            'Precio Venta (Bs)'
        ];
    }

    public function map($product): array
    {
        return [
            $product->barcode,
            strtoupper($product->name),
            strtoupper($product->category->name ?? 'N/A'),
            strtoupper($product->brand->name ?? 'GENÉRICO'),
            $product->cost_usd,
            $product->selling_price_usd,
            $product->price_bs,
        ];
    }
}
