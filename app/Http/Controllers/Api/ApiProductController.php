<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Product::with('kategori');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->has('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $products = $query->latest()->paginate($request->per_page ?? 15);

        return $this->successResponse($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load('kategori', 'stockMovements');

        return $this->successResponse($product);
    }

    public function adjustStock(Request $request, Product $product): JsonResponse
    {
        $this->authorize('adjustStock', $product);

        $request->validate([
            'type' => 'required|in:add,subtract,set',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|string',
        ]);

        $currentStock = $product->stok;
        $newStock = match ($request->type) {
            'add' => $currentStock + $request->quantity,
            'subtract' => $currentStock - $request->quantity,
            'set' => $request->quantity,
        };

        if ($newStock < 0) {
            return $this->errorResponse('Stock cannot be negative', 422);
        }

        $product->update(['stok' => $newStock]);

        $product->stockMovements()->create([
            'type' => 'adjustment',
            'quantity' => $request->quantity,
            'stock_before' => $currentStock,
            'stock_after' => $newStock,
            'notes' => $request->reason,
            'user_id' => $request->user()->id,
        ]);

        return $this->successResponse($product->fresh(), 'Stock adjusted successfully');
    }

    public function byBarcode(string $barcode): JsonResponse
    {
        $product = Product::where('barcode', $barcode)->with('kategori')->first();

        if (! $product) {
            return $this->notFoundResponse('Product not found');
        }

        return $this->successResponse($product);
    }
}
