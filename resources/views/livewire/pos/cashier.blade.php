<div class="flex h-[calc(100vh-4rem)] bg-gray-100">
    {{-- Left Panel: Products --}}
    <div class="flex-1 flex flex-col p-6 overflow-hidden">
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Kasir - Toko Karomah</h2>

            {{-- Search Bar --}}
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari produk atau scan barcode..."
                    class="w-full px-4 py-3 pl-12 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                    autofocus
                >
                <svg class="w-6 h-6 absolute left-4 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            {{-- Product Search Results Modal --}}
            @if($showProductModal && $searchResults->count() > 0)
                <div class="absolute z-10 w-[calc(100%-3rem)] mt-2 bg-white border border-gray-200 rounded-lg shadow-xl max-h-96 overflow-y-auto">
                    @foreach($searchResults as $product)
                        <div wire:click="selectProduct({{ $product->id }})"
                             class="p-4 border-b border-gray-100 hover:bg-blue-50 cursor-pointer flex justify-between items-center">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $product->nama }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-blue-600">Rp {{ number_format($product->harga, 0, ',', '.') }}</div>
                                <div class="text-sm text-gray-500">Stok: {{ $product->stok }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Quick Product Grid (Optional - can be populated with popular items) --}}
        <div class="flex-1 overflow-y-auto">
            <div class="grid grid-cols-3 gap-4">
                @php
                    $popularProducts = \App\Models\Product::where('stok', '>', 0)->limit(9)->get();
                @endphp
                @foreach($popularProducts as $product)
                    <div wire:click="addToCart({{ $product->id }})"
                         class="bg-white p-4 rounded-lg shadow hover:shadow-lg cursor-pointer transition-shadow border-2 border-transparent hover:border-blue-500">
                        <div class="font-semibold text-gray-800 mb-2">{{ $product->nama }}</div>
                        <div class="text-sm text-gray-500 mb-2">Stok: {{ $product->stok }}</div>
                        <div class="text-lg font-bold text-blue-600">Rp {{ number_format($product->harga, 0, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right Panel: Cart --}}
    <div class="w-96 bg-white shadow-lg flex flex-col">
        <div class="p-4 border-b border-gray-200 bg-blue-600 text-white">
            <h3 class="text-xl font-bold">Keranjang</h3>
            <p class="text-sm text-blue-100">{{ count($cart) }} item</p>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto p-4">
            @if(empty($cart))
                <div class="text-center text-gray-400 py-12">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p>Keranjang kosong</p>
                </div>
            @else
                @foreach($cart as $item)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800">{{ $item['nama'] }}</h4>
                                <p class="text-sm text-gray-500">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                            </div>
                            <button wire:click="removeFromCart({{ $item['id'] }})"
                                    class="text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <button wire:click="decrementQuantity({{ $item['id'] }})"
                                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                </button>
                                <input type="number"
                                       value="{{ $item['quantity'] }}"
                                       wire:change="updateQuantity({{ $item['id'] }}, $event.target.value)"
                                       class="w-16 text-center border border-gray-300 rounded"
                                       min="1">
                                <button wire:click="incrementQuantity({{ $item['id'] }})"
                                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="font-bold text-blue-600">
                                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Cart Summary --}}
        <div class="border-t border-gray-200 p-4 bg-gray-50">
            <div class="flex justify-between mb-2 text-lg">
                <span class="font-semibold">Total:</span>
                <span class="font-bold text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <div class="space-y-2">
                <button wire:click="openCheckout"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors {{ empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ empty($cart) ? 'disabled' : '' }}>
                    Bayar (F12)
                </button>
                <button wire:click="clearCart"
                        class="w-full bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    Kosongkan Keranjang
                </button>
            </div>
        </div>
    </div>

    {{-- Checkout Modal --}}
    @if($showCheckoutModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-2xl font-bold mb-4">Checkout</h3>

                <div class="mb-4">
                    <div class="flex justify-between text-xl mb-4">
                        <span class="font-semibold">Total:</span>
                        <span class="font-bold text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Bayar</label>
                        <input type="number"
                               wire:model="paidAmount"
                               class="w-full px-4 py-3 text-xl border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                               autofocus>
                    </div>

                    <div class="flex justify-between text-xl mb-4 p-4 bg-gray-100 rounded-lg">
                        <span class="font-semibold">Kembalian:</span>
                        <span class="font-bold {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($change, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Quick Cash Buttons --}}
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        @php
                            $quickCash = [
                                $total,
                                ceil($total / 10000) * 10000,
                                ceil($total / 50000) * 50000,
                                ceil($total / 100000) * 100000,
                            ];
                            $quickCash = array_unique($quickCash);
                        @endphp
                        @foreach(array_slice($quickCash, 0, 6) as $amount)
                            <button wire:click="$set('paidAmount', {{ $amount }})"
                                    class="bg-gray-200 hover:bg-gray-300 py-2 rounded-lg font-semibold">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button wire:click="checkout"
                            class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 {{ $change < 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $change < 0 ? 'disabled' : '' }}>
                        Proses Transaksi
                    </button>
                    <button wire:click="$set('showCheckoutModal', false)"
                            class="px-6 bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-300">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif
</div>
