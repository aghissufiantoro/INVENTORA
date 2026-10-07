<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>

    <h2 style="text-align:center;">LAPORAN PENJUALAN</h2>
    <p style="text-align:center;">Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Invoice</th>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Total (Rp)</th>
                <th>Dibayar (Rp)</th>
                <th>Kembalian (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTransaksi = 0;
                $totalOmzet = 0;
            @endphp

            @foreach ($penjualan as $i => $pos)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $pos->invoice_number }}</td>
                    <td class="text-center">{{ $pos->tanggal }}</td>
                    <td>{{ $pos->user->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($pos->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($pos->paid_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($pos->change_amount, 0, ',', '.') }}</td>
                </tr>

                {{-- Detail Produk --}}
                @if($pos->details && $pos->details->count() > 0)
                    <tr>
                        <td></td>
                        <td colspan="6">
                            <table width="100%" style="font-size:10px; border-collapse: collapse;">
                                <thead>
                                    <tr style="background-color:#f9f9f9;">
                                        <th style="border:1px solid #aaa;">Produk</th>
                                        <th style="border:1px solid #aaa;">Jumlah</th>
                                        <th style="border:1px solid #aaa;">Harga</th>
                                        <th style="border:1px solid #aaa;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pos->details as $detail)
                                        <tr>
                                            <td style="border:1px solid #ccc;">{{ $detail->product->nama ?? '-' }}</td>
                                            <td style="border:1px solid #ccc; text-align:center;">{{ $detail->jumlah }}</td>
                                            <td style="border:1px solid #ccc; text-align:right;">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                                            <td style="border:1px solid #ccc; text-align:right;">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endif

                @php
                    $totalTransaksi++;
                    $totalOmzet += $pos->total_amount;
                @endphp
            @endforeach

            <tr>
                <th colspan="4" class="text-right">Total Transaksi</th>
                <td colspan="3">{{ $totalTransaksi }}</td>
            </tr>
            <tr>
                <th colspan="4" class="text-right">Total Omzet</th>
                <td colspan="3" class="text-right">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="text-align:right; font-size:10px; margin-top:10px;">
        Dicetak oleh: {{ auth()->user()->name ?? 'Administrator' }}
    </p>

</body>
</html>
