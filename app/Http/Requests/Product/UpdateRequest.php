<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'car_id' => ['nullable', 'exists:cars,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'color_id' => ['nullable', 'exists:colors,id'],     
            'position_id' => ['nullable', 'exists:positions,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'markup' => ['required', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название товара обязательно',
            'title.max' => 'Название не может быть длиннее 255 символов',
            'category_id.required' => 'Выберите категорию',
            'category_id.exists' => 'Выбранная категория не существует',
            'cost_price.required' => 'Укажите себестоимость',
            'cost_price.numeric' => 'Себестоимость должна быть числом',
            'cost_price.min' => 'Себестоимость не может быть отрицательной',
            'markup.required' => 'Укажите наценку',
            'markup.numeric' => 'Наценка должна быть числом',
            'markup.min' => 'Наценка не может быть отрицательной',
            'quantity.integer' => 'Количество должно быть целым числом',
            'quantity.min' => 'Количество не может быть отрицательным',
            'image.image' => 'Файл должен быть изображением',
            'image.max' => 'Размер изображения не должен превышать 4 МБ',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cost_price' => str_replace(' ', '', (string) $this->input('cost_price')),
            'markup' => str_replace(' ', '', (string) $this->input('markup')),
            'quantity' => str_replace(' ', '', (string) $this->input('quantity')),
        ]);
    }
}