<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{

    public function index()
    {
        $kategori = Kategori::all();
        return view('kategori.index', compact('kategori'));
    }
    public function create()
    {
        // Return the view for creating a new kategori
        return view('kategori.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|unique:kategoris,nama'
        ]);

        Kategori::create([
            'nama' => $request->nama
        ]);

        return redirect()->route('kategori.create')->with('success', 'Kategori berhasil ditambahkan.');
    }


    public function storeAjax(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|unique:kategoris,nama',
        ]);

        $kategori = Kategori::create([
            'nama' => $request->nama
        ]);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'kategori' => $kategori,
        ]);
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $kategori = Kategori::findOrFail($id);
        $kategori->update([
            'nama' => $request->nama,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

}

