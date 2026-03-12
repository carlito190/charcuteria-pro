<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['rate', 'source'];

    // Para obtener la tasa actual: ExchangeRate::current()
    public function scopeCurrent($query)
    {
        return $query->latest()->first()?->rate ?? 0;
    }
}
