<?php
namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                => ['required', 'array', 'min:1', 'max:20'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id'   => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.size_id'      => ['nullable', 'integer', 'exists:product_variant_sizes,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1', 'max:100'],
            'customer_name'        => ['required', 'string', 'max:255'],
            'customer_email'       => ['required', 'email', 'max:255'],
            'customer_phone'       => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'shipping_address'     => ['required', 'string', 'max:1000'],
            'shipping_area_id'     => ['nullable', 'string', 'max:100'],
            // shipping_cost tidak lagi diterima dari client — diambil dari cache server-side
            // Client wajib kirim shipping_cache_key + courier yang dipilih
            'shipping_cache_key'   => ['required', 'string', 'size:41'], // "shipping:" + 32 char md5
            'shipping_courier'     => ['required', 'string', 'max:50'],
            'shipping_service'     => ['required', 'string', 'max:50'],
            'destination_lat'      => ['nullable', 'numeric', 'between:-90,90'],
            'destination_lng'      => ['nullable', 'numeric', 'between:-180,180'],
            'payment_method'       => ['nullable', 'string', 'in:cod,bank_transfer,qris'],
            'selected_bank'        => ['nullable', 'string', 'max:100'],
            'coupon_code'          => ['nullable', 'string', 'max:50'],
        ];
    }
}
