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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'shipping_address' => ['required', 'string'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $validator->errors()->any()) {
                foreach ($this->items as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product && $product->stock < $item['quantity']) {
                        $validator->errors()->add(
                            "items.{$item['product_id']}.quantity",
                            "Insufficient stock for '{$product->name}'. Available: {$product->stock}, requested: {$item['quantity']}."
                        );
                    }
                    if ($product && !$product->is_active) {
                        $validator->errors()->add(
                            "items.{$item['product_id']}",
                            "Product '{$product->name}' is not available."
                        );
                    }
                }
            }
        });
    }
}
