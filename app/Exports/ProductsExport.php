<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Product::with(['category', 'car', 'position', 'color', 'quantities'])
            ->orderBy('title')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Название',
            'Категория',
            'Марка авто',
            'Позиция',
            'Цвет',
            'Себестоимость',
            'Наценка',
            'Цена продажи',
            'Остаток (всего)',
        ];
    }

    public function map($product): array
    {
        return [
            $product->title,
            $product->category->title ?? '',
            $product->car->title ?? '',
            $product->position->title ?? '',
            $product->color->title ?? '',
            (int) $product->cost_price,
            (int) $product->markup,
            (int) $product->cost_price + (int) $product->markup,
            $product->quantities->sum('quantity'),
        ];
    }
}