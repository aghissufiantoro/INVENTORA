<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $purchaseOrder->kode_po }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h2 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #555; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .info { margin-bottom: 10px; }
        .info p { margin: 4px 0; }
        .footer { text-align: right; margin-top: 40px; }
    </style>
</head>
<body>
    <h2>Purchase Order (PO)</h2>

    <div class="info">
        <p><strong>Kode PO:</strong> {{ $purchaseOrder->kode_po }}</p>
        <p><strong>Tanggal:</strong> {{ $purchaseOrder->tanggal }}</p>
        <p><strong>Dibuat oleh:</strong> {{ $purchaseOrder->user->name }}</p>
        <p><strong>Status:</strong> {{ ucfirst($purchaseOrder->status) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->product->nama}}</td>
                    <td>{{ $detail->jumlah }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>
        <p><em>Terima kasih.</em></p>
    </div>
</body>
</html>
