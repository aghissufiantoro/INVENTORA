<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangMasuk;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiBarangMasukController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = BarangMasuk::with(['product', 'user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->user()->role === 'karyawan') {
            $query->where('user_id', $request->user()->id);
        }

        $barangMasuk = $query->latest()->paginate($request->per_page ?? 15);

        return $this->successResponse($barangMasuk);
    }

    public function show(BarangMasuk $barangMasuk): JsonResponse
    {
        $this->authorize('view', $barangMasuk);

        $barangMasuk->load('product', 'user');

        return $this->successResponse($barangMasuk);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
            'harga_beli' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        $barangMasuk = BarangMasuk::create($validated);

        return $this->successResponse(
            $barangMasuk->load('product'),
            'Incoming goods record created successfully',
            201
        );
    }

    public function update(Request $request, BarangMasuk $barangMasuk): JsonResponse
    {
        $this->authorize('update', $barangMasuk);

        $validated = $request->validate([
            'jumlah' => 'sometimes|integer|min:1',
            'harga_beli' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $barangMasuk->update($validated);

        return $this->successResponse(
            $barangMasuk->load('product'),
            'Incoming goods record updated successfully'
        );
    }

    public function verify(Request $request, BarangMasuk $barangMasuk): JsonResponse
    {
        $this->authorize('verify', $barangMasuk);

        if (! $barangMasuk->canBeVerified()) {
            return $this->errorResponse('Cannot verify: price must be set first', 422);
        }

        DB::beginTransaction();

        try {
            $product = $barangMasuk->product;
            $stockBefore = $product->stok;

            $product->increment('stok', $barangMasuk->jumlah);

            $product->stockMovements()->create([
                'type' => 'incoming',
                'transaction_type' => 'purchase',
                'quantity' => $barangMasuk->jumlah,
                'stock_before' => $stockBefore,
                'stock_after' => $product->stok,
                'price' => $barangMasuk->harga_beli,
                'notes' => $barangMasuk->keterangan ?? 'Barang Masuk',
                'user_id' => $request->user()->id,
                'transaction_date' => now(),
            ]);

            if ($barangMasuk->harga_beli > 0) {
                $product->update(['harga_beli' => $barangMasuk->harga_beli]);
            }

            $barangMasuk->update(['status' => 'verified']);

            DB::commit();

            return $this->successResponse(
                $barangMasuk->fresh()->load('product'),
                'Incoming goods verified successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function updateHarga(Request $request, BarangMasuk $barangMasuk): JsonResponse
    {
        $this->authorize('update', $barangMasuk);

        $validated = $request->validate([
            'harga_beli' => 'required|numeric|min:0',
        ]);

        $barangMasuk->update(['harga_beli' => $validated['harga_beli']]);

        return $this->successResponse(
            $barangMasuk->load('product'),
            'Purchase price updated successfully'
        );
    }

    public function destroy(BarangMasuk $barangMasuk): JsonResponse
    {
        $this->authorize('delete', $barangMasuk);

        $barangMasuk->delete();

        return $this->successResponse(null, 'Incoming goods record deleted successfully');
    }
}
