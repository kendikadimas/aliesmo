<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id', 'name', 'stock', 'sku',
        'weight', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'stock'      => 'integer',
            'weight'     => 'integer',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
