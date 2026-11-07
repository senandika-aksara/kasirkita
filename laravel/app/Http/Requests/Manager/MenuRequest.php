<?php

namespace App\Http\Requests\Manager;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isManagerial();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $this->merge(['price' => Money::parse($this->input('price'))]);
        }
    }

    public function rules(): array
    {
        $menuId = $this->route('menu')?->id;

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('menus', 'name')->ignore($menuId),
            ],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:1', 'max:10000000'],
            'status' => ['required', 'in:tersedia,habis'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Sudah ada menu dengan nama tersebut.',
            'price.min' => 'Harga menu minimal Rp1.',
            'price.max' => 'Harga menu maksimal Rp10.000.000.',
            'image.max' => 'Ukuran foto menu maksimal 1 MB.',
            'image.mimes' => 'Foto menu harus berformat JPG, PNG, atau WEBP.',
        ];
    }
}
