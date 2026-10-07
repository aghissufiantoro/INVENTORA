<?php

namespace App\Http\Controllers;

use App\Models\Pos;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Illuminate\Support\Collection;

class LaporanPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pos::with(['details.product', 'user'])->orderBy('tanggal', 'desc');

        // 🔍 Filter tanggal
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        // 🔍 Pencarian invoice atau nama kasir
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // ⬆️⬇️ Sorting
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'tanggal_asc':
                    $query->orderBy('tanggal', 'asc');
                    break;
                case 'total_desc':
                    $query->orderBy('total_amount', 'desc');
                    break;
                case 'total_asc':
                    $query->orderBy('total_amount', 'asc');
                    break;
                default:
                    $query->orderBy('tanggal', 'desc');
            }
        }

        $penjualan = $query->get();

        // 💰 Hitung total transaksi dan total omzet
        $totalTransaksi = $penjualan->count();
        $totalOmzet = $penjualan->sum('total_amount');

        return view('laporan_penjualan.penjualan', compact('penjualan', 'totalTransaksi', 'totalOmzet'));
    }

    public function exportPdf(Request $request)
    {
        $query = Pos::with(['details.product', 'user'])->orderBy('tanggal', 'desc');

        // Filter sesuai tampilan
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $penjualan = $query->get();

        // Gunakan view milikmu langsung
        $pdf = Pdf::loadView('laporan_penjualan.pdf_penjualan', compact('penjualan'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan_penjualan.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Pos::with(['details.product', 'user'])->orderBy('tanggal', 'desc');

        // Filter sesuai tampilan
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $penjualan = $query->get();

        // 📊 Ubah data ke bentuk Collection untuk Excel
        $data = new Collection();

        foreach ($penjualan as $p) {
            foreach ($p->details as $detail) {
                $data->push([
                    'Invoice' => $p->invoice_number,
                    'Tanggal' => $p->tanggal,
                    'Kasir' => $p->user->name ?? '-',
                    'Produk' => $detail->product->nama ?? '-',
                    'Jumlah' => $detail->jumlah,
                    'Harga Satuan (Rp)' => $detail->harga,
                    'Subtotal (Rp)' => $detail->subtotal,
                    'Total Transaksi (Rp)' => $p->total_amount,
                ]);
            }
        }

        // 🧾 Export langsung tanpa file export terpisah
        return Excel::download(new class ($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $data;
            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'Invoice',
                    'Tanggal',
                    'Kasir',
                    'Produk',
                    'Jumlah',
                    'Harga Satuan (Rp)',
                    'Subtotal (Rp)',
                    'Total Transaksi (Rp)',
                ];
            }
        }, 'laporan_penjualan.xlsx', ExcelFormat::XLSX);
    }
}
