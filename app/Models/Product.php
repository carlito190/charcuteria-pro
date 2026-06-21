<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\ExchangeRate;

class Product extends Model
{
    protected $fillable =
    [
        'name',
        'unit_type',
        'category_id',
        'cost_usd',
        'profit_margin',
        'barcode',
        'is_active',
        'brand_id',
        'image_path'
    ];

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

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Scope para buscar productos por nombre, código de barras o marca.
     */
    public function scopeSearch($query, $term)
    {
        // Si el término de búsqueda está vacío, retornamos el query sin modificarlo
        if (empty($term)) {
            return $query;
        }

        return $query->where(function($q) use ($term) {
            $searchTerm = '%' . $term . '%';

            $q->where('name', 'like', $searchTerm)
            ->orWhere('barcode', 'like', $searchTerm)
            ->orWhereHas('brand', function($brandQuery) use ($searchTerm) {
                $brandQuery->where('name', 'like', $searchTerm);
            })->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
              $categoryQuery->where('name', 'like', $searchTerm);
            });
        });
    }
}
