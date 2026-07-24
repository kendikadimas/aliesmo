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
        'gateway_reference', 'amount', 'status', 'raw_payload',
        'proof_image', 'proof_note', 'confirmed_at'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => \App\Enums\PaymentStatus::class,
            'raw_payload' => 'array',
            'confirmed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getProofImageUrlAttribute(): ?string
    {
        // File di disk private — URL lewat admin route, bukan /storage
        if (!$this->proof_image || !$this->order_id) {
            return null;
        }
        return route('admin.payment-proof', $this->order_id);
    }
}
