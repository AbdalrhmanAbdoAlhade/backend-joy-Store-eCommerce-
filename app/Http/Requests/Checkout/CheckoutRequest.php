<?php
// app/Http/Requests/Checkout/CheckoutRequest.php
namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:cod,paymob,fawry,wallet'],
            'shipping_method' => ['required', 'string', 'in:standard,express,pickup'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'points_to_redeem' => ['nullable', 'integer', 'min:0'],
            'is_gift' => ['boolean'],
            'gift_message' => ['nullable', 'string', 'max:500'],
            'gift_wrap_amount' => ['nullable', 'numeric', 'min:0'],
            'customer_note' => ['nullable', 'string', 'max:1000'],

            'shipping_address' => ['required', 'array'],
            'shipping_address.governorate' => ['required', 'string'],
            'shipping_address.city' => ['required', 'string'],
            'shipping_address.area' => ['nullable', 'string'],
            'shipping_address.street' => ['required', 'string'],
            'shipping_address.building' => ['nullable', 'string'],
            'shipping_address.floor' => ['nullable', 'string'],
            'shipping_address.apartment' => ['nullable', 'string'],
            'shipping_address.landmark' => ['nullable', 'string'],
            'shipping_address.phone' => ['nullable', 'string'],

            'billing_address' => ['nullable', 'array'],
            'pickup_branch_id' => ['required_if:shipping_method,pickup', 'nullable', 'exists:pickup_branches,id'],
        ];
    }
}
