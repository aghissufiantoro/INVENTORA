<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{
    protected $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('details.product', 'user')
            ->latest()
            ->get();

        return view('purchase-order.index', compact('purchaseOrders'));
    }

    public function create()
    {
        // Ambil semua produk yang stoknya < rop
        $recommendedProducts = Product::whereColumn('stok', '<=', 'rop')->get();
        $allProducts = Product::all();

        return view('purchase-order.create', compact('recommendedProducts', 'allProducts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jumlah.*' => 'nullable|integer|min:1',
        ]);

        $kodePO = 'PO-' . strtoupper(Str::random(6));
        $tanggal = Carbon::now()->toDateString();

        $po = PurchaseOrder::create([
            'kode_po' => $kodePO,
            'tanggal' => $tanggal,
            'user_id' => Auth::id(),
            'status' => 'draft',
        ]);

        foreach ($request->product_id as $key => $productId) {

            // Ambil qty-nya
            $qty = $request->jumlah[$key] ?? null;

            // Skip kalau qty kosong atau 0
            if (!$qty || $qty < 1) {
                continue;
            }

            PurchaseOrderDetail::create([
                'purchase_order_id' => $po->id,
                'product_id' => $productId,
                'jumlah' => $qty,
            ]);
        }



        return redirect()->route('purchase-order.show', $po->id)->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('details.product', 'user');
        return view('purchase-order.show', compact('purchaseOrder'));
    }

    public function downloadPDF(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('details.product', 'user');

        $pdf = Pdf::loadView('purchase-order.pdf', compact('purchaseOrder'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('PurchaseOrder_' . $purchaseOrder->kode_po . '.pdf');
    }

    /**
     * Tampilkan halaman untuk konfirmasi penerimaan barang
     */
    public function receive($id)
    {
        $purchaseOrder = PurchaseOrder::with('details.product')->findOrFail($id);

        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-order.index')
                ->with('error', '❌ Purchase Order ini sudah diselesaikan.');
        }

        return view('purchase-order.receive', compact('purchaseOrder'));
    }

    /**
     * Proses penerimaan barang dan update stok
     */
    public function processReceive(Request $request, $id)
    {
        $request->validate([
            'received_qty.*' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $purchaseOrder = PurchaseOrder::with('details.product')->findOrFail($id);

        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-order.index')
                ->with('error', '❌ Purchase Order ini sudah diselesaikan.');
        }

        try {
            DB::beginTransaction();

            $totalReceived = 0;

            // Loop setiap detail PO
            foreach ($purchaseOrder->details as $detail) {
                $receivedQty = $request->input('received_qty.' . $detail->id, 0);

                if ($receivedQty > 0) {
                    // Update stok produk dan catat movement
                    $this->stockService->recordMovement([
                        'product_id' => $detail->product_id,
                        'transaction_type' => 'purchase',
                        'quantity' => $receivedQty,
                        'price' => $detail->product->harga,
                        'reference_no' => $purchaseOrder->kode_po,
                        'notes' => $request->notes ?? "Penerimaan barang dari PO {$purchaseOrder->kode_po}",
                        'user_id' => auth()->id(),
                        'transaction_date' => now(),
                    ]);

                    $totalReceived += $receivedQty;
                }
            }

            // Update status PO menjadi selesai
            $purchaseOrder->update([
                'status' => 'selesai',
            ]);

            DB::commit();

            return redirect()->route('purchase-order.index')
                ->with('success', "✅ Purchase Order {$purchaseOrder->kode_po} berhasil diselesaikan. Total {$totalReceived} item diterima.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('PO Receive Error: ' . $e->getMessage());

            return back()->with('error', '❌ Gagal memproses penerimaan: ' . $e->getMessage());
        }
    }

    /**
     * Redirect ke halaman receive
     */
    public function markAsCompleted($id)
    {
        return redirect()->route('purchase-order.receive', $id);
    }
}