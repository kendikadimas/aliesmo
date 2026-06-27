<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'gateway', 'gateway_transaction_id',
        'gateway_reference', 'amount', 'status', 'raw_payload'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => \App\Enums\PaymentStatus::class,
            'raw_payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
