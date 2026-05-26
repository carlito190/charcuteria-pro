<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_type',
        'price',
        'subtotal'
    ];

    // Pertenece a una venta
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // Qué producto se vendió
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
