<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangRusak;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiBarangRusakController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = BarangRusak::with(['produk', 'user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }

        $barangRusak = $query->latest()->paginate($request->per_page ?? 15);

        return $this->successResponse($barangRusak);
    }

    public function show(BarangRusak $barangRusak): JsonResponse
    {
        $barangRusak->load('produk', 'user');

        return $this->successResponse($barangRusak);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
            'alasan' => 'required|string',
            'foto' => 'nullable|image|max:2048',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'menunggu';

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('barang-rusak', 'public');
        }

        $barangRusak = BarangRusak::create($validated);

        return $this->successResponse(
            $barangRusak->load('produk'),
            'Damaged goods record created successfully',
            201
        );
    }

    public function approve(Request $request, BarangRusak $barangRusak): JsonResponse
    {
        $this->authorize('approve', $barangRusak);

        if ($barangRusak->status !== 'menunggu') {
            return $this->errorResponse('Can only approve pending requests', 422);
        }

        DB::beginTransaction();

        try {
            $product = $barangRusak->produk;
            $stockBefore = $product->stok;

            if ($product->stok < $barangRusak->jumlah) {
                throw new \Exception('Insufficient stock');
            }

            $product->decrement('stok', $barangRusak->jumlah);

            $product->stockMovements()->create([
                'type' => 'outgoing',
                'transaction_type' => 'adjustment_minus',
                'quantity' => $barangRusak->jumlah,
                'stock_before' => $stockBefore,
                'stock_after' => $product->stok,
                'notes' => 'Barang Rusak: ' . $barangRusak->alasan,
                'user_id' => $request->user()->id,
                'transaction_date' => now(),
            ]);

            $barangRusak->update(['status' => 'disetujui']);

            DB::commit();

            return $this->successResponse(
                $barangRusak->fresh()->load('produk'),
                'Damaged goods request approved successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function reject(Request $request, BarangRusak $barangRusak): JsonResponse
    {
        $this->authorize('reject', $barangRusak);

        if ($barangRusak->status !== 'menunggu') {
            return $this->errorResponse('Can only reject pending requests', 422);
        }

        $barangRusak->update(['status' => 'ditolak']);

        return $this->successResponse(
            $barangRusak->load('produk'),
            'Damaged goods request rejected'
        );
    }

    public function destroy(BarangRusak $barangRusak): JsonResponse
    {
        $this->authorize('delete', $barangRusak);

        $barangRusak->delete();

        return $this->successResponse(null, 'Damaged goods record deleted successfully');
    }
}
