<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Pos;
use App\Models\DetailPos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // === KPI 1: Total Penjualan Hari Ini ===
        $penjualanHariIni = Pos::whereDate('tanggal', $today)->sum('total_amount');

        // Bandingkan dengan kemarin
        $penjualanKemarin = Pos::whereDate('tanggal', Carbon::yesterday())->sum('total_amount');
        $persentasePenjualan = $penjualanKemarin > 0
            ? round((($penjualanHariIni - $penjualanKemarin) / $penjualanKemarin) * 100, 2)
            : 0;

        // === KPI 2: Jumlah Transaksi Hari Ini ===
        $jumlahTransaksi = Pos::whereDate('tanggal', $today)->count();
        $rataTransaksiMingguan = Pos::whereBetween('tanggal', [Carbon::now()->subDays(7), Carbon::now()])
            ->count() / 7;

        // === KPI 3: Total Barang Terjual Hari Ini ===
        $totalBarangTerjual = DetailPos::whereHas('pos', function ($q) use ($today) {
            $q->whereDate('tanggal', $today);
        })->sum('jumlah');

        // Bandingkan dengan kemarin (opsional)
        $kemarin = Carbon::yesterday();
        $totalBarangKemarin = DetailPos::whereHas('pos', function ($q) use ($kemarin) {
            $q->whereDate('tanggal', $kemarin);
        })->sum('jumlah');

        $persentaseBarangTerjual = $totalBarangKemarin > 0
            ? round((($totalBarangTerjual - $totalBarangKemarin) / $totalBarangKemarin) * 100, 2)
            : 0;

        // === KPI 4: Stok Kritis ===
        $stokKritis = Product::whereColumn('stok', '<=', 'rop')->count();
        $detailStokKritis = Product::whereColumn('stok', '<=', 'rop')->get(['nama', 'stok', 'rop'])->map(function ($item) {
            $item->reorder_qty = $item->rop - $item->stok;
            return $item;
        });

        // === Grafik 1: Tren Penjualan (7 hari terakhir) ===
        $labelsTrenPenjualan = [];
        $dataTrenPenjualan = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labelsTrenPenjualan[] = $date->format('d M');
            $dataTrenPenjualan[] = Pos::whereDate('tanggal', $date)->sum('total_amount');
        }

        // === Grafik 2: Top 5 Produk Terlaris ===
        $produkTerlaris = DetailPos::select('product_id', DB::raw('SUM(jumlah) as total_jumlah'))
            ->groupBy('product_id')
            ->orderByDesc('total_jumlah')
            ->take(5)
            ->with('product')
            ->get();

        $labelsTopProduk = $produkTerlaris->pluck('product.nama');
        $dataTopProduk = $produkTerlaris->pluck('total_jumlah');

        // === Grafik 3: Status Stok Berdasarkan Kategori ===
        $inStock = Product::whereColumn('stok', '>', 'rop')->count();
        $lowStock = Product::whereColumn('stok', '<=', 'rop')->where('stok', '>', 0)->count();
        $outStock = Product::where('stok', '=', 0)->count();
        $dataStatusStok = [$inStock, $lowStock, $outStock];

        return view('dashboard/index', compact(
            'penjualanHariIni',
            'persentasePenjualan',
            'jumlahTransaksi',
            'rataTransaksiMingguan',
            'totalBarangTerjual',
            'persentaseBarangTerjual',
            'stokKritis',
            'detailStokKritis',
            'labelsTrenPenjualan',
            'dataTrenPenjualan',
            'labelsTopProduk',
            'dataTopProduk',
            'dataStatusStok'
        ));

    }
}
