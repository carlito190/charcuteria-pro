<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory;

    // Declaramos de forma explícita el nombre de la tabla por el plural en inglés
    protected $table = 'sales_payments';

    protected $fillable = [
        'sale_id',
        'payment_method',
        'currency',
        'amount',
        'exchange_rate'
    ];

    // Pertenece a una venta
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
