<?php

namespace App\Http\Requests\Sale;

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
            'status' => ['required', 'in:оплачено,долг'],
            'customer_name' => ['required_if:status,долг', 'nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'prices' => ['sometimes', 'array'],
            'prices.*' => ['numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Выберите статус продажи',
            'status.in' => 'Неверный статус продажи',
            'customer_name.required_if' => 'Для долга укажите имя клиента',
            'customer_name.max' => 'Имя клиента не может быть длиннее 255 символов',
            'customer_phone.max' => 'Телефон не может быть длиннее 50 символов',
            'prices.*.numeric' => 'Цена должна быть числом',
            'prices.*.min' => 'Цена не может быть отрицательной',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('prices')) {
            $this->merge([
                'prices' => collect($this->input('prices', []))
                    ->map(fn($value) => str_replace(' ', '', (string) $value))
                    ->all(),
            ]);
        }
    }
}