<?php

namespace App\Http\Controllers;

use App\Models\Retur;
use App\Models\Product;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    protected $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        $returs = Retur::with('product', 'user')->latest()->get();
        $products = Product::all();
        return view('retur.index', compact('returs', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
            'alasan' => 'required|string|max:255',
        ]);

        Retur::create([
            'product_id' => $request->product_id,
            'jumlah' => $request->jumlah,
            'alasan' => $request->alasan,
            'user_id' => Auth::id(),
            'status' => 'menunggu',
        ]);

        return redirect()->back()->with('success', 'Pengajuan retur berhasil dikirim, menunggu persetujuan owner.');
    }

    public function approve(Retur $retur)
    {
        $this->authorize('approve', $retur);

        DB::beginTransaction();
        try {
            $retur->update(['status' => 'disetujui']);

            $this->stockService->recordMovement([
                'product_id' => $retur->product_id,
                'transaction_type' => 'return_from_customer',
                'quantity' => $retur->jumlah,
                'reference_no' => 'RETUR-' . $retur->id,
                'notes' => "Retur: {$retur->alasan}",
                'user_id' => $retur->user_id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Retur disetujui dan stok telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyetujui retur: ' . $e->getMessage());
        }
    }

    public function reject(Retur $retur)
    {
        $this->authorize('reject', $retur);

        $retur->update(['status' => 'ditolak']);
        return redirect()->back()->with('success', 'Retur ditolak.');
    }
}
