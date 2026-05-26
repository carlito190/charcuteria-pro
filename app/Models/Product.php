<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\ExchangeRate;

class Product extends Model
{
    protected $fillable = ['name','unit_type', 'category_id', 'cost_usd', 'profit_margin', 'barcode', 'is_active'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Accesor para el precio de venta en Dólares
    public function getSellingPriceUsdAttribute()
    {
        return $this->cost_usd * (1 + ($this->profit_margin / 100));
    }

    // Accesor para el precio de venta en Bolívares (usando la tasa que creamos)
    public function getPriceBsAttribute()
    {
        $rate = ExchangeRate::latest()->first()?->rate ?? 1;
        return $this->selling_price_usd * $rate;
    }

    public function branches() {

    return $this->hasMany(ProductBranch::class);

    }
}
