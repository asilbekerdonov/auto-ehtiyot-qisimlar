<?php

namespace App\Http\Controllers;

use App\Models\Quantity;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::orderBy('title')->get();

        $selectedWarehouseId = $request->query('warehouse', optional($warehouses->first())->id);

        // with('product') — товар для каждой строки остатка грузится одним запросом,
        // а не отдельным запросом в цикле
        $stockItems = Quantity::with('product')
            ->where('warehouse_id', $selectedWarehouseId)
            ->get();

        return view('pages.stock', [
            'warehouses' => $warehouses,
            'selectedWarehouseId' => $selectedWarehouseId,
            'stockItems' => $stockItems,
        ]);
    }
}