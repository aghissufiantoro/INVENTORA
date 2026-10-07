<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockMovementService
{
    public function recordMovement(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $type = $this->determineType($data['transaction_type']);
            $quantity = (int) $data['quantity'];

            $stockBefore = (int) $product->stok;

            if ($type === 'out') {
                if ($stockBefore < $quantity) {
                    throw new \Exception("Stok tidak mencukupi. Stok saat ini: {$stockBefore}, diminta: {$quantity}");
                }
                $stockAfter = $stockBefore - $quantity;
                $product->stok = $stockAfter;
            } else {
                $stockAfter = $stockBefore + $quantity;
                $product->stok = $stockAfter;
            }

            $product->save();
            $product->updateStockStatus();

            $movement = StockMovement::create([
                'product_id' => $data['product_id'],
                'type' => $type,
                'transaction_type' => $data['transaction_type'],
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_no' => $data['reference_no'] ?? null,
                'price' => $data['price'] ?? $product->harga ?? 0,
                'notes' => $data['notes'] ?? null,
                'user_id' => $data['user_id'] ?? auth()->id(),
                'transaction_date' => $data['transaction_date'] ?? now(),
            ]);

            Log::info('Stock Movement Recorded', [
                'product' => $product->nama,
                'type' => $type,
                'quantity' => $quantity,
                'before' => $stockBefore,
                'after' => $stockAfter,
            ]);

            return $movement;
        });
    }

    public function recordBatchMovements(array $items, callable $beforeCallback = null)
    {
        return DB::transaction(function () use ($items, $beforeCallback) {
            if ($beforeCallback) {
                $beforeCallback();
            }

            $movements = [];
            foreach ($items as $item) {
                $movements[] = $this->recordMovement($item);
            }

            return $movements;
        });
    }

    private function determineType($transactionType)
    {
        $inTypes = ['purchase', 'return_from_customer', 'adjustment_plus'];
        $outTypes = ['sale', 'adjustment_minus', 'return_to_supplier', 'damage'];

        if (in_array($transactionType, $inTypes)) {
            return 'in';
        }

        if (in_array($transactionType, $outTypes)) {
            return 'out';
        }

        throw new \Exception('Transaction type tidak valid: ' . $transactionType);
    }

    public function getMovements($filters = [])
    {
        $query = StockMovement::with(['product', 'user'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($filters['start_date'])) {
            $query->whereDate('transaction_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('transaction_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        return $query->get();
    }

    public function getStockCard($productId, $startDate = null, $endDate = null)
    {
        $query = StockMovement::with(['user'])
            ->where('product_id', $productId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        return $query->get();
    }

    public function getStockSummary($startDate = null, $endDate = null)
    {
        $query = StockMovement::with('product')
            ->select('product_id', 'type', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(quantity * COALESCE(price, 0)) as total_value'))
            ->groupBy('product_id', 'type');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        return $query->get();
    }
}
