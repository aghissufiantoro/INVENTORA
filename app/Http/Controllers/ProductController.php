<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use illuminate\Support\Carbon;
use App\Models\Product;
use App\Models\Kategori;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryCalculationRequest;
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{
    protected $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = Product::with(['kategori', 'pendingCalculation']);

        if ($request->has('status') && $request->status != '') {
            $query->where('stock_status', $request->status);
        }

        if ($request->has('kategori_id') && $request->kategori_id != '') {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $products = $query->get();
        $kategori = Kategori::all();

        $stats = [
            'total' => Product::count(),
            'normal' => Product::where('stock_status', 'normal')->count(),
            'reorder' => Product::where('stock_status', 'reorder')->count(),
            'low' => Product::where('stock_status', 'low')->count(),
            'critical' => Product::where('stock_status', 'critical')->count(),
            'total_value' => Product::sum(DB::raw('stok * harga')),
        ];

        return view('product.index', compact('products', 'kategori', 'stats'));
    }

    public function create()
    {
        $kategori = Kategori::all();
        return view('product.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_product' => 'nullable|string|max:50|unique:products,kode_product',
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0'
        ]);

        $product = Product::create([
            'kode_product' => $request->kode_product,
            'nama' => $request->nama,
            'kategori_id' => $request->kategori_id,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'safety_stock' => 0,
            'rop' => 0,
        ]);

        if ($request->stok > 0) {
            try {
                $this->stockService->recordMovement([
                    'product_id' => $product->id,
                    'transaction_type' => 'adjustment_plus',
                    'quantity' => $request->stok,
                    'price' => $request->harga,
                    'notes' => 'Stok awal saat product dibuat',
                    'reference_no' => 'PRD-INIT-' . $product->id,
                    'user_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to record stock movement: ' . $e->getMessage());
            }
        }

        $product->updateStockStatus();

        return redirect()->route('product.index')
            ->with('success', "✅ Product '{$product->nama}' berhasil ditambahkan.");
    }

    public function show(string $id)
    {
        $product = Product::with('kategori')->findOrFail($id);

        $info = [
            'days_until_stockout' => $product->getDaysUntilStockout(),
            'recommended_order_qty' => $product->getRecommendedOrderQuantity(),
            'stock_value' => $product->stok * $product->harga,
        ];

        return view('product.show', compact('product', 'info'));
    }

    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $kategori = Kategori::all();
        return view('product.edit', compact('product', 'kategori'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'kode_product' => 'nullable|string|max:50|unique:products,kode_product,' . $id,
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        $oldStock = $product->stok;
        $newStock = $request->stok;

        $product->update([
            'kode_product' => $request->kode_product,
            'nama' => $request->nama,
            'kategori_id' => $request->kategori_id,
            'harga' => $request->harga,
            'stok' => $newStock,
        ]);

        if ($oldStock != $newStock) {
            $difference = $newStock - $oldStock;
            $this->stockService->recordMovement([
                'product_id' => $product->id,
                'transaction_type' => $difference > 0 ? 'adjustment_plus' : 'adjustment_minus',
                'quantity' => abs($difference),
                'price' => $request->harga,
                'notes' => 'Adjustment stok manual',
                'reference_no' => 'PRD-EDIT-' . now()->timestamp,
                'user_id' => auth()->id(),
            ]);
        }

        $product->updateStockStatus();

        return redirect()->route('product.index')
            ->with('success', "✅ Product '{$product->nama}' berhasil diupdate.");
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $nama = $product->nama;

        if ($product->stok > 0) {
            try {
                $this->stockService->recordMovement([
                    'product_id' => $product->id,
                    'transaction_type' => 'adjustment_minus',
                    'quantity' => $product->stok,
                    'price' => $product->harga,
                    'notes' => "Product dihapus dari sistem. Stok terakhir: {$product->stok}",
                    'reference_no' => 'PRD-DEL-' . $product->id . '-' . now()->timestamp,
                    'user_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to record stock movement on delete: ' . $e->getMessage());
            }
        }

        $product->delete();

        return redirect()->route('product.index')
            ->with('success', "🗑️ Product '{$nama}' berhasil dihapus.");
    }

    public function reorderAlert()
    {
        $products = Product::with('kategori')
            ->needsReorder()
            ->orderBy('stock_status', 'desc')
            ->orderBy('stok', 'asc')
            ->get();

        $stats = [
            'reorder' => Product::where('stock_status', 'reorder')->count(),
            'critical' => Product::where('stock_status', 'critical')->count(),
            'low' => Product::where('stock_status', 'low')->count(),
        ];

        return view('product.reorder-alert', compact('products', 'stats'));
    }

    public function adjustStock(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|not_in:0',
            'type' => 'required|in:add,subtract',
            'notes' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($id);
        $this->authorize('adjustStock', $product);

        try {
            $quantity = abs($request->quantity);
            $transactionType = $request->type === 'add' ? 'adjustment_plus' : 'adjustment_minus';

            $this->stockService->recordMovement([
                'product_id' => $product->id,
                'transaction_type' => $transactionType,
                'quantity' => $quantity,
                'price' => $product->harga,
                'notes' => $request->notes ?? 'Manual stock adjustment',
                'reference_no' => 'ADJ-' . $product->id . '-' . now()->timestamp,
                'user_id' => auth()->id(),
            ]);

            $product->refresh();
            $action = $request->type === 'add' ? 'ditambah' : 'dikurangi';
            $message = "Stok '{$product->nama}' berhasil {$action} {$quantity} unit. Stok sekarang: {$product->stok}";

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function calculateInventoryMetrics($productId, $startDate = null, $endDate = null)
    {
        try {
            $quantityColumn = $this->detectQuantityColumn();
            $productIdColumn = $this->detectProductIdColumn();

            \Log::info("=== START CALCULATION FOR PRODUCT {$productId} ===");

            $query = DB::table('detail_pos')
                ->where($productIdColumn, $productId);

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            $rawSales = $query->select($quantityColumn, 'created_at')->get();

            if ($rawSales->isEmpty()) {
                return [
                    'success' => false,
                    'error' => "Tidak ada data transaksi",
                ];
            }

            $periodStart = $startDate
                ? Carbon::parse($startDate)->startOfDay()
                : Carbon::parse($rawSales->min('created_at'))->startOfDay();

            $periodEnd = $endDate
                ? Carbon::parse($endDate)->endOfDay()
                : Carbon::parse($rawSales->max('created_at'))->endOfDay();

            $totalDays = $periodStart->diffInDays($periodEnd) + 1;

            $dailySales = $rawSales->groupBy(function ($row) {
                return Carbon::parse($row->created_at)->format('Y-m-d');
            })->map(function ($items) use ($quantityColumn) {
                return $items->sum($quantityColumn);
            });

            $salesDays = $dailySales->count();
            $totalSales = $dailySales->sum();
            $maxDailySales = $salesDays ? $dailySales->max() : 0;
            $avgDailyDemand = $totalDays > 0 ? $totalSales / $totalDays : 0;

            $product = Product::find($productId);
            $maxLeadTime = $product->max_lead_time_days ?? 4;
            $avgLeadTime = round($product->lead_time_days ?? 3);

            $safetyStockExact = ($maxDailySales * $maxLeadTime) - ($avgDailyDemand * $avgLeadTime);
            $safetyStock = max(1, round($safetyStockExact));

            $reorderPointExact = ($avgDailyDemand * $avgLeadTime) + $safetyStock;
            $reorderPoint = max(1, round($reorderPointExact));

            return [
                'success' => true,
                'avg_daily_sales' => round($avgDailyDemand, 2),
                'max_daily_sales' => $maxDailySales,
                'min_daily_sales' => $dailySales->min() ?? 0,
                'total_sold' => $totalSales,
                'safety_stock' => $safetyStock,
                'reorder_point' => $reorderPoint,
                'lead_time' => $avgLeadTime,
                'total_days' => $totalDays,
                'active_days' => $salesDays,
                'active_ratio' => round(($salesDays / $totalDays) * 100, 1),
                'first_sale' => $dailySales->keys()->first(),
                'last_sale' => $dailySales->keys()->last(),
            ];

        } catch (\Exception $e) {
            \Log::error("ERROR compute inventory metrics: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function detectQuantityColumn()
    {
        try {
            $columns = DB::select('DESCRIBE detail_pos');
            $columnNames = array_map(function ($col) {
                return $col->Field;
            }, $columns);

            $possibleNames = ['jumlah', 'qty', 'quantity', 'jumlah_barang', 'qty_sold', 'amount'];

            foreach ($possibleNames as $name) {
                if (in_array($name, $columnNames)) {
                    return $name;
                }
            }

            return 'jumlah';
        } catch (\Exception $e) {
            return 'jumlah';
        }
    }

    private function detectProductIdColumn()
    {
        try {
            $columns = DB::select('DESCRIBE detail_pos');
            $columnNames = array_map(function ($col) {
                return $col->Field;
            }, $columns);

            $possibleNames = ['product_id', 'produk_id', 'barang_id', 'id_product', 'id_produk'];

            foreach ($possibleNames as $name) {
                if (in_array($name, $columnNames)) {
                    return $name;
                }
            }

            return 'product_id';
        } catch (\Exception $e) {
            return 'product_id';
        }
    }

    /**
     * 🆕 KARYAWAN ONLY - Hitung SS & ROP semua produk (CREATE REQUEST)
     */
    public function autoCalculateAll()
    {
        $user = auth()->user();

        // 🔒 Proteksi role - HANYA KARYAWAN
        if ($user->role !== 'karyawan') {
            return back()->with('error', '❌ Aksi ini hanya dapat dilakukan oleh karyawan.');
        }

        $products = Product::all();
        $results = [
            'requested' => [],
            'failed' => [],
            'skipped' => [],
        ];

        $totalSalesRecords = DB::table('detail_pos')->count();

        if ($totalSalesRecords === 0) {
            return back()->with(
                'warning',
                '⚠️ <strong>Tidak ada data penjualan</strong><br>
                Tabel <code>detail_pos</code> masih kosong. Silakan lakukan transaksi penjualan terlebih dahulu.'
            );
        }

        foreach ($products as $product) {
            $hasSales = DB::table('detail_pos')
                ->where($this->detectProductIdColumn(), $product->id)
                ->exists();

            if (!$hasSales) {
                $results['skipped'][] = $product->nama;
                continue;
            }

            $result = $this->calculateInventoryMetrics($product->id);

            if (isset($result['success']) && $result['success']) {
                // 📝 Simpan sebagai request PENDING
                InventoryCalculationRequest::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'status' => 'pending',
                    ],
                    [
                        'avg_daily_usage' => $result['avg_daily_sales'],
                        'max_daily_usage' => $result['max_daily_sales'],
                        'lead_time_days' => $product->lead_time_days ?? 3,
                        'safety_stock' => $result['safety_stock'],
                        'rop' => $result['reorder_point'],
                        'requested_by' => $user->id,
                    ]
                );

                $results['requested'][] = $product->nama;
            } else {
                $results['failed'][] = $product->nama;
            }
        }

        // 🧾 Pesan feedback
        $message = "";

        if (count($results['requested']) > 0) {
            $message .= "<div class='alert alert-warning'>";
            $message .= "<h5 class='alert-heading'>⏳ " . count($results['requested']) . " Perhitungan Diajukan</h5>";
            $message .= "<p class='mb-0'>Menunggu persetujuan owner.</p>";
            $message .= "</div>";
        }

        if (count($results['failed']) > 0) {
            $message .= "<div class='alert alert-danger mt-2'>";
            $message .= "<strong>❌ Gagal:</strong> " . count($results['failed']) . " produk";
            $message .= "</div>";
        }

        if (count($results['skipped']) > 0) {
            $message .= "<div class='alert alert-secondary mt-2'>";
            $message .= "<strong>⏭️ Dilewati:</strong> " . count($results['skipped']) . " produk (belum pernah terjual)";
            $message .= "</div>";
        }

        return back()->with('warning', $message);
    }
}