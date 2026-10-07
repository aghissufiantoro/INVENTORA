<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pergerakan Stok</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        p {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        td {
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            font-size: 11px;
            text-align: right;
        }
    </style>
</head>

<body>

    <h2>LAPORAN PERGERAKAN STOK</h2>
    <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Tipe Transaksi</th>
                <th>Jumlah</th>
                <th>Harga (Rp)</th>
                <th>Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalMasuk = 0;
                $totalKeluar = 0;
                $nilaiMasuk = 0;
                $nilaiKeluar = 0;
            @endphp

            @foreach ($movements as $i => $item)
                @php
                    $nilai = ($item->quantity ?? 0) * ($item->price ?? 0);
                    if ($item->type === 'in') {
                        $totalMasuk += $item->quantity;
                        $nilaiMasuk += $nilai;
                    } elseif ($item->type === 'out') {
                        $totalKeluar += $item->quantity;
                        $nilaiKeluar += $nilai;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->product->nama ?? '-' }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                    <td class="text-center">
                        {{ $item->type == 'in' ? 'Masuk' : 'Keluar' }}
                    </td>
                    <td class="text-center">{{ ucfirst($item->transaction_type ?? '-') }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($nilai, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr>
                <th colspan="5" class="text-right">Total Masuk</th>
                <td class="text-center">{{ $totalMasuk }}</td>
                <td colspan="2" class="text-right">Rp {{ number_format($nilaiMasuk, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th colspan="5" class="text-right">Total Keluar</th>
                <td class="text-center">{{ $totalKeluar }}</td>
                <td colspan="2" class="text-right">Rp {{ number_format($nilaiKeluar, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ auth()->user()->nama ?? 'Administrator' }}</p>
    </div>

</body>

</html>
