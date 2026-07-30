<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
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
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'car_id' => ['required', 'exists:cars,id'],
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
            'quantity.min' => 'Количество должно быть больше 0',
            'price_per_unit.required' => 'Укажите цену',
            'price_per_unit.numeric' => 'Цена должна быть числом',
            'price_per_unit.min' => 'Цена не может быть отрицательной',
            'car_id.required' => 'Выберите машину',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'price_per_unit' => str_replace(' ', '', (string) $this->input('price_per_unit')),
        ]);
    }
}