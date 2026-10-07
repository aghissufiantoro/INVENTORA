<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $pos->invoice_number }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            width: 58mm;
            padding: 3mm;
            background: #fff;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #000;
        }

        .store-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .store-info {
            font-size: 9px;
            margin-bottom: 1mm;
        }

        .invoice-info {
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #000;
        }

        .invoice-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .items-section {
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #000;
        }

        .item-row {
            margin-bottom: 2mm;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 0.5mm;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            padding-left: 2mm;
        }

        .totals-section {
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #000;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .total-row.grand-total {
            font-size: 13px;
            font-weight: bold;
            margin-top: 2mm;
            padding-top: 2mm;
            border-top: 1px dashed #000;
        }

        .payment-section {
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #000;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 3mm;
        }

        .footer p {
            margin-bottom: 1mm;
        }

        @media screen {
            body {
                margin: 10px auto;
                border: 1px solid #ccc;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }

            .no-print {
                position: fixed;
                top: 10px;
                right: 10px;
                display: flex;
                gap: 10px;
                z-index: 1000;
            }

            .no-print button,
            .no-print a {
                padding: 8px 16px;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                font-family: Arial, sans-serif;
                font-size: 14px;
            }

            .no-print button:hover,
            .no-print a:hover {
                background: #2563eb;
            }

            .no-print .btn-secondary {
                background: #8b5cf6;
            }

            .no-print .btn-secondary:hover {
                background: #7c3aed;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                width: 58mm;
                padding: 3mm;
            }
        }
    </style>
</head>
<body>
    <!-- Action Buttons (Screen Only) -->
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
        <a href="{{ route('pos.index') }}" class="btn-secondary">+ Transaksi Baru</a>
    </div>

    <!-- Receipt Content -->
    <div class="receipt-header">
        <div class="store-name">TOKO KAROMAH</div>
        <div class="store-info">Jl. Raya Karomah</div>
        <div class="store-info">Telp: 0xxx-xxxx-xxxx</div>
    </div>

    <div class="invoice-info">
        <div class="invoice-row">
            <span>No:</span>
            <span>{{ $pos->invoice_number }}</span>
        </div>
        <div class="invoice-row">
            <span>Tgl:</span>
            <span>{{ date('d/m/Y H:i', strtotime($pos->created_at)) }}</span>
        </div>
        <div class="invoice-row">
            <span>Kasir:</span>
            <span>{{ $pos->user->name ?? 'Admin' }}</span>
        </div>
    </div>

    <div class="items-section">
        @foreach ($pos->details as $detail)
            <div class="item-row">
                <div class="item-name">{{ $detail->product->nama_barang }}</div>
                <div class="item-details">
                    <span>{{ $detail->jumlah }} x {{ number_format($detail->harga, 0, ',', '.') }}</span>
                    <span>{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="totals-section">
        <div class="total-row grand-total">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($pos->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="payment-section">
        <div class="total-row">
            <span>Bayar:</span>
            <span>Rp {{ number_format($pos->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Kembali:</span>
            <span>Rp {{ number_format($pos->change_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="footer">
        <p>TERIMA KASIH</p>
        <p>Atas Kunjungan Anda</p>
        <p style="margin-top: 2mm;">*** {{ $pos->invoice_number }} ***</p>
    </div>

    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
