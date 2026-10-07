<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use App\Models\Pos;
use App\Services\StockMovementService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Cashier extends Component
{
    public $cart = [];
    public $search = '';
    public $selectedProduct = null;
    public $quantity = 1;
    public $paidAmount = 0;
    public $showCheckoutModal = false;
    public $showProductModal = false;

    protected $listeners = ['productSelected' => 'addToCart'];

    public function mount()
    {
        $this->cart = session()->get('cart', []);
    }

    public function updatedSearch()
    {
        $this->showProductModal = strlen($this->search) >= 2;
    }

    public function getSearchResultsProperty()
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        return Product::where(function ($q) {
            $q->where('nama', 'like', "%{$this->search}%");
        })
            ->where('stok', '>', 0)
            ->limit(10)
            ->get();
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->quantity = 1;
        $this->showProductModal = false;
        $this->search = '';
    }

    public function addToCartFromModal()
    {
        if (! $this->selectedProduct) {
            return;
        }

        $this->addToCart($this->selectedProduct->id, $this->quantity);
        $this->selectedProduct = null;
        $this->quantity = 1;
    }

    public function addToCart($productId, $quantity = 1)
    {
        $product = Product::find($productId);

        if (! $product) {
            session()->flash('error', 'Produk tidak ditemukan!');
            return;
        }

        if (isset($this->cart[$productId])) {
            $newQuantity = $this->cart[$productId]['quantity'] + $quantity;
        } else {
            $newQuantity = $quantity;
        }

        if ($newQuantity > $product->stok) {
            session()->flash('error', "Stok {$product->nama} tidak mencukupi! (Stok: {$product->stok})");
            return;
        }

        $this->cart[$productId] = [
            'id' => $product->id,
            'nama' => $product->nama,
            'quantity' => $newQuantity,
            'harga' => $product->harga,
            'subtotal' => $product->harga * $newQuantity,
        ];

        $this->saveCart();
        session()->flash('success', 'Produk ditambahkan ke keranjang!');
    }

    public function updateQuantity($productId, $quantity)
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $product = Product::find($productId);

        if ($quantity > $product->stok) {
            session()->flash('error', "Stok {$product->nama} tidak mencukupi! (Stok: {$product->stok})");
            return;
        }

        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        $this->cart[$productId]['quantity'] = $quantity;
        $this->cart[$productId]['subtotal'] = $this->cart[$productId]['harga'] * $quantity;

        $this->saveCart();
    }

    public function incrementQuantity($productId)
    {
        $newQty = $this->cart[$productId]['quantity'] + 1;
        $this->updateQuantity($productId, $newQty);
    }

    public function decrementQuantity($productId)
    {
        $newQty = $this->cart[$productId]['quantity'] - 1;
        $this->updateQuantity($productId, $newQty);
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        $this->cart = array_values($this->cart);
        $this->cart = collect($this->cart)->keyBy('id')->toArray();

        $this->saveCart();
        session()->flash('success', 'Produk dihapus dari keranjang!');
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->saveCart();
        session()->flash('success', 'Keranjang dikosongkan!');
    }

    public function getTotalProperty()
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getChangeProperty()
    {
        return $this->paidAmount - $this->total;
    }

    public function openCheckout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang kosong!');
            return;
        }

        $this->paidAmount = $this->total;
        $this->showCheckoutModal = true;
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang kosong!');
            return;
        }

        if ($this->paidAmount < $this->total) {
            session()->flash('error', 'Jumlah bayar kurang dari total!');
            return;
        }

        DB::beginTransaction();

        try {
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $pos = Pos::create([
                'invoice_number' => $invoiceNumber,
                'tanggal' => now()->toDateString(),
                'total_amount' => $this->total,
                'paid_amount' => $this->paidAmount,
                'change_amount' => $this->change,
                'user_id' => auth()->id(),
            ]);

            $stockService = app(StockMovementService::class);
            $notificationService = app(NotificationService::class);

            foreach ($this->cart as $item) {
                $pos->details()->create([
                    'product_id' => $item['id'],
                    'jumlah' => $item['quantity'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                $stockService->recordMovement([
                    'product_id' => $item['id'],
                    'transaction_type' => 'sale',
                    'quantity' => $item['quantity'],
                    'price' => $item['harga'],
                    'reference_no' => $invoiceNumber,
                    'notes' => "Penjualan via POS - Invoice: {$invoiceNumber}",
                    'user_id' => auth()->id(),
                ]);

                $product = Product::find($item['id']);
                try {
                    $notificationService->checkAndCreateStockNotification($product, auth()->id());
                } catch (\Exception $e) {
                    \Log::error("Failed to create notification: " . $e->getMessage());
                }
            }

            DB::commit();

            $this->clearCart();
            $this->showCheckoutModal = false;
            $this->paidAmount = 0;

            return redirect()->route('pos.receipt', $pos->id);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout Error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    protected function saveCart()
    {
        session()->put('cart', $this->cart);
    }

    public function render()
    {
        return view('livewire.pos.cashier', [
            'searchResults' => $this->searchResults,
            'total' => $this->total,
            'change' => $this->change,
        ]);
    }
}
