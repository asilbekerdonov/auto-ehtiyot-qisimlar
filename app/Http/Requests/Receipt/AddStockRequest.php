<?php

namespace App\Http\Requests\Receipt;

use Illuminate\Foundation\Http\FormRequest;

class AddStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Выберите товар',
            'product_id.exists' => 'Товар не найден',
            'warehouse_id.required' => 'Выберите склад',
            'warehouse_id.exists' => 'Склад не найден',
            'quantity.required' => 'Укажите количество',
            'quantity.integer' => 'Количество должно быть целым числом',
            'quantity.min' => 'Количество должно быть больше 0',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity' => str_replace(' ', '', (string) $this->input('quantity')),
        ]);
    }
}