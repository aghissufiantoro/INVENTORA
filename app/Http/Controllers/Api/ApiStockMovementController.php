<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiStockMovementController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::with(['product', 'user']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('transaction_date', [$request->date_from, $request->date_to]);
        }

        $movements = $query->latest('transaction_date')->paginate($request->per_page ?? 15);

        return $this->successResponse($movements);
    }

    public function show(StockMovement $stockMovement): JsonResponse
    {
        $stockMovement->load('product', 'user');

        return $this->successResponse($stockMovement);
    }

    public function productHistory(Request $request, int $productId): JsonResponse
    {
        $movements = StockMovement::where('product_id', $productId)
            ->with('user')
            ->latest('transaction_date')
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($movements);
    }
}
