<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBranch extends Model
{
    protected $fillable = [
        'product_id',
        'branch_id',
        'stock'
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }
}
