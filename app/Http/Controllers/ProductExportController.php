<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;

class ProductExportController extends Controller
{
    // Generar y descargar el PDF
    public function pdf(Request $request)
    {
        $search = $request->query('search', '');

        $products = Product::with(['category', 'brand'])
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%')
                             ->orWhere('barcode', 'like', '%' . $search . '%');
            })
            ->get();

        $pdf = Pdf::loadView('exports.products-pdf', [
            'products' => $products,
            'date' => now()->format('d/m/Y h:i A')
        ])->setPaper('letter', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Lista_Precios_Jireh_Market.pdf');
    }

    // Generar y descargar el Excel
    public function excel(Request $request)
    {
        $search = $request->query('search', '');

        $products = Product::with(['category', 'brand'])
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->get();

        return Excel::download(new ProductsExport($products), 'Inventario_Jireh_Market.xlsx');
    }
}
