<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Retur;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiReturController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Retur::with(['product', 'user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->user()->role === 'karyawan') {
            $query->where('user_id', $request->user()->id);
        }

        $retur = $query->latest()->paginate($request->per_page ?? 15);

        return $this->successResponse($retur);
    }

    public function show(Retur $retur): JsonResponse
    {
        $retur->load('product', 'user');

        return $this->successResponse($retur);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
            'alasan' => 'required|string',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'menunggu';

        $retur = Retur::create($validated);

        return $this->successResponse(
            $retur->load('product'),
            'Return request created successfully',
            201
        );
    }

    public function approve(Request $request, Retur $retur): JsonResponse
    {
        $this->authorize('approve', $retur);

        if ($retur->status !== 'menunggu') {
            return $this->errorResponse('Can only approve pending requests', 422);
        }

        DB::beginTransaction();

        try {
            $product = $retur->product;
            $stockBefore = $product->stok;

            $product->increment('stok', $retur->jumlah);

            $product->stockMovements()->create([
                'type' => 'incoming',
                'transaction_type' => 'return_from_customer',
                'quantity' => $retur->jumlah,
                'stock_before' => $stockBefore,
                'stock_after' => $product->stok,
                'notes' => 'Retur: ' . $retur->alasan,
                'user_id' => $request->user()->id,
                'transaction_date' => now(),
            ]);

            $retur->update(['status' => 'disetujui']);

            DB::commit();

            return $this->successResponse(
                $retur->fresh()->load('product'),
                'Return request approved successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function reject(Request $request, Retur $retur): JsonResponse
    {
        $this->authorize('reject', $retur);

        if ($retur->status !== 'menunggu') {
            return $this->errorResponse('Can only reject pending requests', 422);
        }

        $retur->update(['status' => 'ditolak']);

        return $this->successResponse(
            $retur->load('product'),
            'Return request rejected'
        );
    }
}
