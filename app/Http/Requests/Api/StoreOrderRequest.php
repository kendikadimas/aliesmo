<?php
namespace App\Http\Requests\Api;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'              => ['required', 'array', 'min:1', 'max:20'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1', 'max:100'],
            'customer_name'      => ['required', 'string', 'max:255'],
            'customer_email'     => ['required', 'email', 'max:255'],
            'customer_phone'     => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'shipping_address'   => ['required', 'string', 'max:1000'],
            'shipping_cost'      => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'payment_method'     => ['nullable', 'string', 'in:cod,whatsapp'],
            'coupon_code'        => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $validator->errors()->any()) {
                foreach ($this->items as $index => $item) {
                    $product = Product::find($item['product_id']);
                    if ($product && $product->stock < $item['quantity']) {
                        $validator->errors()->add(
                            "items.{$index}.quantity",
                            "Stok '{$product->name}' tidak cukup. Tersedia: {$product->stock}, diminta: {$item['quantity']}."
                        );
                    }
                    if ($product && !$product->is_active) {
                        $validator->errors()->add(
                            "items.{$index}",
                            "Produk '{$product->name}' sedang tidak tersedia."
                        );
                    }
                }
            }
        });
    }
}
