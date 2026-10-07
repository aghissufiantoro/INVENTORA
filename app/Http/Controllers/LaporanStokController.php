<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class LaporanStokController extends Controller
{
    protected $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Halaman utama laporan stok movement
     */
    public function index(Request $request)
    {
        // Ambil filter dari request
        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'product_id' => $request->product_id,
            'type' => $request->type,
            'transaction_type' => $request->transaction_type,
        ];

        // Ambil data pergerakan stok dari service
        $movements = $this->stockService->getMovements($filters);

        // Ambil data produk untuk dropdown filter
        $products = Product::all();

        // Hitung total
        $totalMasuk = $movements->where('type', 'in')->sum('quantity');
        $totalKeluar = $movements->where('type', 'out')->sum('quantity');
        $nilaiMasuk = $movements->where('type', 'in')->sum(fn($m) => $m->quantity * ($m->price ?? 0));
        $nilaiKeluar = $movements->where('type', 'out')->sum(fn($m) => $m->quantity * ($m->price ?? 0));

        return view('laporan_stok.index', compact(
            'movements',
            'products',
            'totalMasuk',
            'totalKeluar',
            'nilaiMasuk',
            'nilaiKeluar'
        ));
    }



    /**
     * Kartu stok per produk
     */
    public function stockCard(Request $request)
    {
        $products = Product::all();
        $movements = null;
        $product = null;

        if ($request->product_id) {
            $product = Product::findOrFail($request->product_id);
            $movements = $this->stockService->getStockCard(
                $request->product_id,
                $request->start_date,
                $request->end_date
            );
        }

        return view('laporan.kartu_stok', compact('movements', 'products', 'product'));
    }

    /**
     * Ringkasan pergerakan stok
     */
    public function summary(Request $request)
    {
        $summary = $this->stockService->getStockSummary(
            $request->start_date,
            $request->end_date
        );

        return view('laporan.ringkasan_stok', compact('summary'));
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'product_id' => $request->product_id,
        ];

        $movements = $this->stockService->getMovements($filters);

        $pdf = Pdf::loadView('laporan.pdf_stock_movement', compact('movements'));
        return $pdf->download('laporan_pergerakan_stok.pdf');
    }
    public function exportExcel(Request $request)
    {
        // Ambil data sesuai filter (sama seperti data di PDF)
        $query = \App\Models\StockMovement::with('product');

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->tanggal_awal);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->tanggal_akhir);
        }

        $movements = $query->orderBy('created_at', 'desc')->get();

        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Judul
        $sheet->setCellValue('A1', 'LAPORAN PERGERAKAN STOK');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A2', 'Tanggal Cetak: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Header tabel
        $headers = [
            'No',
            'Nama Barang',
            'Tanggal',
            'Jenis',
            'Tipe Transaksi',
            'Jumlah',
            'Harga (Rp)',
            'Nilai (Rp)'
        ];
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:H4')->getFont()->setBold(true);
        $sheet->getStyle('A4:H4')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A4:H4')->getBorders()->getAllBorders()->setBorderStyle('thin');

        // Isi data
        $row = 5;
        $totalMasuk = $totalKeluar = $nilaiMasuk = $nilaiKeluar = 0;

        foreach ($movements as $i => $item) {
            $nilai = ($item->quantity ?? 0) * ($item->price ?? 0);

            if ($item->type === 'in') {
                $totalMasuk += $item->quantity;
                $nilaiMasuk += $nilai;
            } elseif ($item->type === 'out') {
                $totalKeluar += $item->quantity;
                $nilaiKeluar += $nilai;
            }

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $item->product->nama ?? '-');
            $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($item->created_at)->format('d/m/Y'));
            $sheet->setCellValue('D' . $row, $item->type === 'in' ? 'Masuk' : 'Keluar');
            $sheet->setCellValue('E' . $row, ucfirst($item->transaction_type ?? '-'));
            $sheet->setCellValue('F' . $row, $item->quantity);
            $sheet->setCellValue('G' . $row, $item->price);
            $sheet->setCellValue('H' . $row, $nilai);

            $row++;
        }

        // Total
        $sheet->setCellValue('A' . $row, 'Total Masuk');
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue('F' . $row, $totalMasuk);
        $sheet->setCellValue('G' . $row, '');
        $sheet->setCellValue('H' . $row, $nilaiMasuk);
        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Keluar');
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue('F' . $row, $totalKeluar);
        $sheet->setCellValue('G' . $row, '');
        $sheet->setCellValue('H' . $row, $nilaiKeluar);
        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
        $row += 2;

        // Footer
        $sheet->setCellValue("A{$row}", 'Dicetak oleh: ' . (Auth::user()->nama ?? 'Administrator'));
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('right');

        // Border semua data
        $sheet->getStyle('A4:H' . ($row - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle('thin');

        // Auto width
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download file
        $fileName = 'Laporan_Pergerakan_Stok_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $excelOutput = ob_get_clean();

        return Response::make($excelOutput, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\""
        ]);
    }
}
