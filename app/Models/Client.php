<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'id_number',
        'phone',
        'email',
        'address',
        'allow_credit',
        'credit_limit',
        'current_balance'
    ];

    /**
     * Relación: Un cliente puede tener muchas ventas
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relación: Un cliente puede tener muchas cuentas por cobrar (debts / credits)
     * La crearemos más adelante, pero es bueno tener la visión clara.
     */
    public function accountsReceivable()
    {
        // return $this->hasMany(AccountReceivable::class);
    }
}
