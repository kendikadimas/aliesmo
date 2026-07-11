<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'lookup_token', 'lookup_token_expires_at', 'user_id', 'customer_name', 'customer_email',
        'customer_phone', 'shipping_address', 'subtotal', 'shipping_cost',
        'coupon_discount', 'coupon_code', 'total', 'status', 'payment_method', 'selected_bank', 'tracking_number', 'tracking_url', 'courier', 'paid_at'
    ];

    protected function casts(): array
    {
        return [
            'subtotal'                 => 'decimal:2',
            'shipping_cost'            => 'decimal:2',
            'coupon_discount'          => 'decimal:2',
            'total'                    => 'decimal:2',
            'status'                   => \App\Enums\OrderStatus::class,
            'paid_at'                  => 'datetime',
            'lookup_token_expires_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
