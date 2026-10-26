<?php

namespace App\Http\Requests\Cashier;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;

class PayTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('pay_amount')) {
            $raw = $this->input('pay_amount');
            $this->merge([
                'pay_amount' => ($raw === null || $raw === '') ? null : Money::parse($raw),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'pay_amount' => ['nullable', 'integer', 'min:0', 'max:999999999'],
        ];
    }
}
