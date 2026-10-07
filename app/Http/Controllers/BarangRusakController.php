<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\BarangRusak;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangRusakController extends Controller
{
    protected $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        return view('barang_rusak.index', [
            'products' => Product::all(),
            'barangRusak' => BarangRusak::with(['produk', 'user'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
            'alasan' => 'required',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $fotoName = null;

        if ($request->hasFile('foto')) {
            $fotoName = time() . '.' . $request->foto->extension();
            $request->foto->move(public_path('uploads/barang_rusak'), $fotoName);
        }

        BarangRusak::create([
            'produk_id' => $request->produk_id,
            'user_id' => Auth::id(),
            'jumlah' => $request->jumlah,
            'alasan' => $request->alasan,
            'foto' => $fotoName,
        ]);

        return back()->with('success', 'Pengajuan barang rusak berhasil dikirim.');
    }

    public function approve($id)
    {
        $rusak = BarangRusak::findOrFail($id);
        $this->authorize('approve', $rusak);

        $produk = Product::findOrFail($rusak->produk_id);

        if ($produk->stok < $rusak->jumlah) {
            return back()->with('error', 'Stok produk tidak mencukupi untuk dikurangi.');
        }

        DB::beginTransaction();
        try {
            $rusak->update(['status' => 'disetujui']);

            $this->stockService->recordMovement([
                'product_id' => $rusak->produk_id,
                'transaction_type' => 'damage',
                'quantity' => $rusak->jumlah,
                'reference_no' => 'RUSAK-' . $rusak->id,
                'notes' => "Barang rusak: {$rusak->alasan}",
                'user_id' => $rusak->user_id,
            ]);

            DB::commit();
            return back()->with('success', 'Barang rusak berhasil disetujui & stok diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui barang rusak: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $rusak = BarangRusak::findOrFail($id);
        $this->authorize('reject', $rusak);

        $rusak->update(['status' => 'ditolak']);

        return back()->with('success', 'Barang rusak ditolak.');
    }

    public function destroy($id)
    {
        $rusak = BarangRusak::findOrFail($id);
        $this->authorize('delete', $rusak);

        if ($rusak->foto && file_exists(public_path('uploads/barang_rusak/' . $rusak->foto))) {
            unlink(public_path('uploads/barang_rusak/' . $rusak->foto));
        }

        $rusak->delete();

        return back()->with('success', 'Data barang rusak berhasil dihapus.');
    }
}
