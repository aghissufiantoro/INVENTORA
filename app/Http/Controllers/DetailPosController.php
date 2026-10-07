<?php
// app/Http/Controllers/DetailPosController.php

namespace App\Http\Controllers;

use App\Models\Pos;

class DetailPosController extends Controller
{
    // Tampilkan detail transaksi (Struk)
    public function show($id)
    {
        // Load pos dengan detail dan produk terkait
        $pos = Pos::with(['details.product'])->findOrFail($id);
        return view('pos.receipt', compact('pos'));
    }
    
    // Opsional: Tampilkan semua riwayat
    public function history()
    {
        $transactions = Pos::orderBy('created_at', 'desc')->get();
        return view('history_transaksi.index', compact('transactions'));
    }
}