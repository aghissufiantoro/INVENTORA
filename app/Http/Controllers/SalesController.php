<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sales::with('user')->latest()->paginate(10);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sales' => 'required|string|max:255',
            'tanggal_kunjungan' => 'required|date',
        ]);

        Sales::create([
            'nama_sales' => $request->nama_sales,
            'perusahaan' => $request->perusahaan,
            'tanggal_kunjungan' => $request->tanggal_kunjungan,
            'tujuan' => $request->tujuan,
            'hasil_kunjungan' => $request->hasil_kunjungan,
            'kontak_sales' => $request->kontak_sales,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('sales.index')->with('success', 'Data kunjungan sales berhasil ditambahkan!');
    }

    public function edit(Sales $sales)
    {
        return view('sales.edit', compact('sales'));
    }

    public function update(Request $request, Sales $sales)
    {
        $request->validate([
            'nama_sales' => 'required|string|max:255',
            'tanggal_kunjungan' => 'required|date',
        ]);

        $sales->update($request->all());

        return redirect()->route('sales.index')->with('success', 'Data kunjungan sales berhasil diperbarui!');
    }

    public function destroy(Sales $sales)
    {
        $sales->delete();
        return redirect()->route('sales.index')->with('success', 'Data kunjungan sales berhasil dihapus!');
    }
}
