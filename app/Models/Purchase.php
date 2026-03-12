<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\PurchaseDetail;
use App\Models\Provider;
use App\Models\Branch;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'branch_id',
        'invoice_number',
        'purchase_date',
        'total_usd',

    ];

    public function details() {
    return $this->hasMany(PurchaseDetail::class);
    }

    public function provider() {
        return $this->belongsTo(Provider::class);
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }
}
