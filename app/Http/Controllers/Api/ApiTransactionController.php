<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pos;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiTransactionController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Pos::with(['user', 'details.product']);

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('tanggal', [$request->date_from, $request->date_to]);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $transactions = $query->latest('tanggal')->paginate($request->per_page ?? 15);

        return $this->successResponse($transactions);
    }

    public function show(Pos $pos): JsonResponse
    {
        $pos->load('user', 'details.product.kategori');

        return $this->successResponse($pos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $items = [];
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stok < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->nama_barang}");
                }

                $subtotal = $product->harga_jual * $item['quantity'];
                $totalAmount += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'jumlah' => $item['quantity'],
                    'harga' => $product->harga_jual,
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stok', $item['quantity']);

                $product->stockMovements()->create([
                    'type' => 'sale',
                    'quantity' => $item['quantity'],
                    'stock_before' => $product->stok + $item['quantity'],
                    'stock_after' => $product->stok,
                    'notes' => 'POS Transaction',
                    'user_id' => $request->user()->id,
                ]);
            }

            $changeAmount = $validated['paid_amount'] - $totalAmount;

            if ($changeAmount < 0) {
                throw new \Exception('Paid amount is less than total amount');
            }

            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $pos = Pos::create([
                'invoice_number' => $invoiceNumber,
                'tanggal' => now()->format('Y-m-d'),
                'total_amount' => $totalAmount,
                'paid_amount' => $validated['paid_amount'],
                'change_amount' => $changeAmount,
                'user_id' => $request->user()->id,
            ]);

            foreach ($items as $item) {
                $item['pos_id'] = $pos->id;
                $pos->details()->create($item);
            }

            DB::commit();

            $pos->load('details.product');

            return $this->successResponse($pos, 'Transaction created successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function todaySummary(Request $request): JsonResponse
    {
        $today = now()->format('Y-m-d');

        $query = Pos::where('tanggal', $today);

        if ($request->user()->role !== 'owner') {
            $query->where('user_id', $request->user()->id);
        }

        $summary = [
            'total_transactions' => $query->count(),
            'total_revenue' => $query->sum('total_amount'),
            'total_paid' => $query->sum('paid_amount'),
        ];

        return $this->successResponse($summary);
    }
}
