<?php

namespace App\Http\Controllers;

use App\Models\InventoryCalculationRequest;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryApprovalController extends Controller
{
    public function index()
    {
        // 🔐 Proteksi role owner
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Unauthorized');
        }

        $requests = InventoryCalculationRequest::with(['product', 'requester'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('inventory-approval.index', compact('requests'));
    }

    public function approve($id)
    {
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Unauthorized');
        }

        $request = InventoryCalculationRequest::with('product')->findOrFail($id);

        // ⛔ Cegah double approval
        if ($request->status !== 'pending') {
            return back()->with('warning', 'Perhitungan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request) {

            $product = $request->product;

            $product->update([
                'safety_stock' => $request->safety_stock,
                'rop' => $request->rop,
                'avg_daily_usage' => $request->avg_daily_usage,
                'max_daily_usage' => $request->max_daily_usage,
            ]);

            $product->updateStockStatus();

            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', '✅ Perhitungan berhasil disetujui dan diterapkan ke produk.');
    }

    public function reject($id)
    {
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Unauthorized');
        }

        $request = InventoryCalculationRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            return back()->with('warning', 'Perhitungan ini sudah diproses sebelumnya.');
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('warning', '❌ Perhitungan ditolak.');
    }
}

