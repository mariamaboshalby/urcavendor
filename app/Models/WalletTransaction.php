<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'vendor_id',
        'type',
        'amount',
        'reference',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
