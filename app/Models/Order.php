<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'city',
        'subtotal',
        'shipping',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'قيد الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped'    => 'تم الشحن',
            'delivered'  => 'تم التسليم',
            'cancelled'  => 'ملغي',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'orange',
            'processing' => 'blue',
            'shipped'    => 'purple',
            'delivered'  => 'green',
            'cancelled'  => 'red',
            default      => 'gray',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'unpaid'   => 'غير مدفوع',
            'paid'     => 'مدفوع',
            'refunded' => 'مسترد',
            default    => $this->payment_status,
        };
    }

    public static function generateOrderNumber(): string
    {
        return 'urca-' . strtoupper(uniqid());
    }
}
