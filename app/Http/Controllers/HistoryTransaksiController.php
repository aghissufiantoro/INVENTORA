<?php

namespace App\Http\Controllers;

use App\Models\Pos;
use Carbon\Carbon;

class HistoryTransaksiController extends Controller
{
    public function index()
    {
        // Mendapatkan tanggal 7 hari yang lalu
        $startDate = Carbon::now()->subDays(7)->startOfDay();

        // Ambil semua transaksi yang dibuat dari $startDate sampai hari ini, dengan user (kasir)
        $transaksis = Pos::with('user')
            // Tambahkan batasan: created_at harus lebih besar atau sama dengan $startDate
            ->where('created_at', '>=', $startDate)
            ->latest()
            ->get();

        return view('history_transaksi.index', compact('transaksis'));
    }


    public function show($id)
    {
        // Ambil detail transaksi + semua item di dalamnya
        $transaksi = Pos::with(['details.product', 'user'])->findOrFail($id);
        return view('history_transaksi.show', compact('transaksi'));
    }
}
