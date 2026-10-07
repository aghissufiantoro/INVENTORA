<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Pos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\StockMovementService;
use App\Services\NotificationService;

class PosController extends Controller
{
    protected $stockService;
    protected $notificationService;

    public function __construct(
        StockMovementService $stockService,
        NotificationService $notificationService
    ) {
        $this->stockService = $stockService;
        $this->notificationService = $notificationService;
    }

    // 1. TAMPILAN UTAMA (MENGELOLA SESSION CART)
    public function index()
    {
        $products = Product::all(); // Data produk
        $cart = session()->get('cart', []); // Data keranjang

        $totalAmount = 0;
        if (!empty($cart)) {
            $totalAmount = array_sum(array_column($cart, 'subtotal'));
        }

        return view('pos.index', compact('products', 'cart', 'totalAmount'));
    }

    // 2. TAMBAH/UPDATE KE KERANJANG (SESSION)
    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);

        $product_id = $product->id;
        $quantity = $request->input('quantity', 1);

        // Jika produk sudah ada di keranjang, tambahkan jumlah
        if (isset($cart[$product_id])) {
            $newQuantity = $cart[$product_id]['quantity'] + $quantity;
        } else {
            $newQuantity = $quantity;
        }

        // 🔥 Validasi stok
        if ($newQuantity > $product->stok) {
            return redirect()->route('pos.index')->with(
                'error',
                "Stok {$product->nama} tidak mencukupi! (Stok tersedia: {$product->stok})"
            );
        }

        // Simpan perubahan ke keranjang
        $cart[$product_id] = [
            "id" => $product->id,
            "nama" => $product->nama,
            "quantity" => $newQuantity,
            "harga" => $product->harga,
            "subtotal" => $product->harga * $newQuantity,
        ];

        session()->put('cart', $cart);

        return redirect()->route('pos.index')->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    // 2.5. UPDATE QUANTITY DI KERANJANG
    public function updateCart(Request $request, $productId)
    {
        $cart = session()->get('cart', []);

        if (!isset($cart[$productId])) {
            return redirect()->route('pos.index')->with('error', 'Produk tidak ditemukan di keranjang!');
        }

        $product = Product::findOrFail($productId);
        $newQuantity = $request->input('quantity', 1);

        // Validasi stok
        if ($newQuantity > $product->stok) {
            return redirect()->route('pos.index')->with(
                'error',
                "Stok {$product->nama} tidak mencukupi! (Stok tersedia: {$product->stok})"
            );
        }

        if ($newQuantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = $newQuantity;
            $cart[$productId]['subtotal'] = $product->harga * $newQuantity;
        }

        session()->put('cart', $cart);
        return redirect()->route('pos.index')->with('success', 'Keranjang berhasil diupdate!');
    }

    // 2.6. REMOVE DARI KERANJANG
    public function removeFromCart($productId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            return redirect()->route('pos.index')->with('success', 'Produk berhasil dihapus dari keranjang!');
        }

        return redirect()->route('pos.index')->with('error', 'Produk tidak ditemukan!');
    }

    // 3. PROSES CHECKOUT (PINDAH DARI SESSION KE DATABASE) - VERSI LENGKAP
    public function checkout(Request $request)
    {
        $cart = session()->get('cart');

        if (empty($cart)) {
            return redirect()->route('pos.index')->with('error', 'Keranjang belanja kosong!');
        }

        $totalAmount = array_sum(array_column($cart, 'subtotal'));
        $paidAmount = (float) $request->paid_amount;
        $changeAmount = $paidAmount - $totalAmount;

        if ($changeAmount < 0) {
            return redirect()->back()->with('error', 'Jumlah bayar kurang dari total belanja!');
        }

        DB::beginTransaction();
        try {
            // A. SIMPAN HEADER TRANSAKSI (Pos)
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . rand(1000, 9999);

            $pos = Pos::create([
                'invoice_number' => $invoiceNumber,
                'tanggal' => now()->toDateString(),
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'user_id' => auth()->id(),
            ]);

            // B. SIMPAN DETAIL ITEM & UPDATE STOK
            foreach ($cart as $item) {
                // Simpan detail transaksi
                $pos->details()->create([
                    'product_id' => $item['id'],
                    'jumlah' => $item['quantity'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Catat stock movement (sekalian update stok produk)
                $this->stockService->recordMovement([
                    'product_id' => $item['id'],
                    'transaction_type' => 'sale',
                    'quantity' => $item['quantity'],
                    'price' => $item['harga'],
                    'reference_no' => $invoiceNumber,
                    'notes' => "Penjualan via POS - Invoice: {$invoiceNumber}",
                    'user_id' => auth()->id(),
                ]);

                // Refresh data produk terbaru
                $product = Product::find($item['id']);

                // Cek dan buat notifikasi jika stok rendah
                try {
                    $this->notificationService->checkAndCreateStockNotification($product, auth()->id());
                } catch (\Exception $e) {
                    Log::error("Failed to create notification: " . $e->getMessage());
                }
            }

            DB::commit();

            // C. KOSONGKAN KERANJANG
            session()->forget('cart');

            return redirect()->route('pos.receipt', $pos->id)
                ->with('success', 'Transaksi berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // 4. TAMPILKAN STRUK/RECEIPT
    public function receipt($id)
    {
        $pos = Pos::with(['details.product', 'user'])->findOrFail($id);
        return view('pos.receipt-thermal', compact('pos'));
    }

    // 5. CLEAR CART
    public function clearCart()
    {
        session()->forget('cart');
        return redirect()->route('pos.index')->with('success', 'Keranjang berhasil dikosongkan!');
    }
}