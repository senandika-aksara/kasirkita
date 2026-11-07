<?php

namespace App\Http\Requests\Cashier;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        // Masukan "35.000" atau "Rp35.000" dinormalkan menjadi integer rupiah.
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
            'table_number' => ['nullable', 'string', 'max:10'],
            'payment_status' => ['required', 'in:lunas,belum_bayar'],
            'pay_amount' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Pesanan masih kosong. Pilih minimal satu menu.',
            'items.min' => 'Pesanan masih kosong. Pilih minimal satu menu.',
            'items.max' => 'Satu transaksi maksimal berisi 30 baris menu.',
            'items.*.menu_id.exists' => 'Ada menu yang sudah tidak tersedia di katalog.',
            'items.*.qty.min' => 'Jumlah setiap menu minimal 1.',
            'items.*.qty.max' => 'Jumlah setiap menu maksimal 99.',
        ];
    }

    public function markPaid(): bool
    {
        return $this->input('payment_status') === 'lunas';
    }

    /** @return array<int, array{menu_id: int, qty: int}> */
    public function orderItems(): array
    {
        return $this->input('items', []);
    }
}
