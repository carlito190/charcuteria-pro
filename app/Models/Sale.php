<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_sale',
        'status',
        'invoice_number',
        'branch_id',
        'user_id',
        'client_id',
        'client_name',
        'client_id_number',
        'total'
    ];

    protected $casts = [
    'date_sale' => 'date', // Así Laravel lo trata automáticamente como una fecha limpia
    ];

    // Relación con los artículos del carrito
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Relación con los pagos combinados
    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    // Quién realizó la venta
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // En qué sede se realizó
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
