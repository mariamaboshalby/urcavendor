<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'vendor_id',
        'product_name',
        'price',
        'quantity',
        'total',
        'status',
        'commission_rate',
        'commission_amount',
        'vendor_earning',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'total' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'vendor_earning' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'قيد الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped'    => 'تم الشحن',
            'delivered'  => 'تم التسليم',
            'cancelled'  => 'ملغي',
            default      => $this->status,
        };
    }
}
