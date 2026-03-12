<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'address', 'phone'];

    public function purchases() {
        return $this->hasMany(Purchase::class);
    }
}
