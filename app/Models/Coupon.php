<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order', 'max_discount',
        'usage_limit', 'used_count', 'is_active', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'value'        => 'decimal:2',
            'min_order'    => 'decimal:2',
            'max_discount' => 'decimal:2',
            'is_active'    => 'boolean',
            'expires_at'   => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    public function calculateDiscount(float $orderTotal): float
    {
        if ($orderTotal < $this->min_order) return 0;

        if ($this->type === 'percent') {
            $discount = $orderTotal * ($this->value / 100);
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }
            return $discount;
        }

        return min($this->value, $orderTotal);
    }
}
