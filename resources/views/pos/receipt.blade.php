<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $pos->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .receipt-container, .receipt-container * {
                visibility: visible;
            }
            .receipt-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .receipt-content {
                max-width: 80mm;
                margin: 0 auto;
            }
        }
        
        .receipt-line {
            border-bottom: 1px dashed #ddd;
            margin: 10px 0;
        }
    </style>
</head>
<body class="bg-gray-900 min-h-screen">
    <!-- Success Alert -->
    <div class="no-print bg-green-600 text-white px-6 py-4 flex items-center justify-center space-x-3">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-bold text-lg">Pembayaran Berhasil</p>
            <p class="text-sm">Rp {{ number_format($pos->total_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="flex items-center justify-center min-h-screen py-8">
        <div class="w-full max-w-4xl px-4">
            <!-- Action Buttons (No Print) -->
            <div class="no-print flex justify-center space-x-4 mb-6">
                <button onclick="window.print()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center space-x-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak Resi Lengkap</span>
                </button>
                <a href="{{ route('pos.index') }}" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg flex items-center space-x-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Pesanan Baru</span>
                </a>
            </div>

            <div class="receipt-container bg-white rounded-lg shadow-2xl overflow-hidden">
                <div class="receipt-content p-8">
                    <!-- Header -->
                    <div class="text-center mb-6 pb-6 border-b-2 border-gray-300">
                        <div class="bg-gray-200 w-24 h-24 mx-auto mb-4 rounded flex items-center justify-center">
                            <img src="{{ asset('images/iconkaromah.png') }}" alt="Logo Toko Karomah" class="w-35 h-35">
                        </div>
                        <h1 class="text-2xl font-bold text-gray-800 mb-1">Toko Karomah</h1>
                        <p class="text-sm text-gray-600">{{ date('d/m/Y H:i', strtotime($pos->created_at)) }}</p>
                        <p class="text-sm text-gray-600">Kasir: {{ $pos->user->name ?? 'Admin' }}</p>
                    </div>

                    <!-- Invoice Number -->
                    <div class="bg-gray-100 rounded-lg p-4 mb-6 text-center">
                        <p class="text-xl font-bold text-gray-800">{{ $pos->invoice_number }}</p>
                    </div>

                    <!-- Items -->
                    <div class="mb-6">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-300">
                                    <th class="text-left py-2 text-sm font-semibold text-gray-700">Item</th>
                                    <th class="text-center py-2 text-sm font-semibold text-gray-700">Qty</th>
                                    <th class="text-right py-2 text-sm font-semibold text-gray-700">Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pos->details as $detail)
                                    <tr class="border-b border-gray-200">
                                        <td class="py-3">
                                            <p class="font-medium text-gray-800">{{ $detail->product->nama }}</p>
                                            <p class="text-xs text-gray-500">@ Rp {{ number_format($detail->harga, 0, ',', '.') }}</p>
                                        </td>
                                        <td class="py-3 text-center text-gray-700">
                                            {{ $detail->jumlah }} pcs
                                        </td>
                                        <td class="py-3 text-right font-semibold text-gray-800">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-2 mb-6 pb-6 border-b-2 border-gray-300">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($pos->total_amount / 1, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-gray-800 pt-2">
                            <span>Total</span>
                            <span>Rp {{ number_format($pos->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-gray-700">
                            <span>Bayar</span>
                            <span class="font-semibold">Rp {{ number_format($pos->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-green-600 font-bold">
                            <span>Kembalian</span>
                            <span>Rp {{ number_format($pos->change_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Toko Karomah</p>
                        <p class="text-xs text-gray-600">{{ $pos->user->email ?? 'aghiesalfamori23@gmail.com' }}</p>
                        <p class="text-xs text-gray-600 mt-1">Disiapkan oleh: Odoo</p>
                    </div>

                    <!-- Footer -->
                    <div class="text-center pt-6 border-t-2 border-gray-300">
                        <p class="text-sm font-semibold text-gray-800 mb-2">TERIMA KASIH ATAS KUNJUNGAN ANDA</p>
                        <p class="text-xs text-gray-600">Simpan struk ini sebagai bukti pembayaran yang sah</p>
                    </div>
                </div>
            </div>

            <!-- Email Modal -->
            <div id="emailModal" class="no-print hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-8 w-96 max-w-full mx-4">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Kirim Struk via Email</h3>
                    
                    <form action="#" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Email Tujuan</label>
                            <input type="email" 
                                name="email" 
                                placeholder="customer@example.com"
                                required 
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <button type="button" 
                                onclick="closeEmailModal()" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-semibold transition">
                                Batal
                            </button>
                            <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                                Kirim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sendEmail() {
            document.getElementById('emailModal').classList.remove('hidden');
        }

        function closeEmailModal() {
            document.getElementById('emailModal').classList.add('hidden');
        }

        // Auto print after 1 second (optional, bisa dihapus jika tidak perlu)
        // setTimeout(() => window.print(), 1000);
    </script>
</body>
</html>