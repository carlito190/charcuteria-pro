<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $fillable = ['user_id', 'from_branch_id', 'to_branch_id', 'observation'];

    // Relación con los productos del envío
    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    // Relaciones con las sucursales
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transfer) {
            // Obtenemos el ID del último registro para generar el siguiente
            $lastTransfer = self::latest('id')->first();
            $nextId = $lastTransfer ? $lastTransfer->id + 1 : 1;

            // Formato: TR-00001, TR-00002...
            $transfer->transfer_number = 'TR-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }
}
