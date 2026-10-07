<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\Product;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller
{
    protected $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->middleware('auth');
        $this->stockService = $stockService;
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'owner' || $user->role === 'admin') {
            $barangMasuk = BarangMasuk::with('product', 'user')->latest()->paginate(10);
        } else {
            $barangMasuk = BarangMasuk::with('product', 'user')
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);
        }

        $products = Product::all();
        return view('barang_masuk.index', [
            'barangMasuks' => $barangMasuk,
            'products' => $products
        ]);
    }

    public function create()
    {
        $products = Product::all();
        return view('barang_masuk.create', compact('products'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ];

        if ($user->role !== 'karyawan') {
            $rules['harga_beli'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules, [
            'product_id.required' => 'Nama barang wajib dipilih',
            'product_id.exists' => 'Barang tidak ditemukan',
            'jumlah.required' => 'Jumlah barang wajib diisi',
            'jumlah.min' => 'Jumlah minimal 1',
            'harga_beli.required' => 'Harga beli wajib diisi',
            'harga_beli.min' => 'Harga tidak boleh negatif',
        ]);

        $product = Product::find($request->product_id);
        $status = ($user->role === 'owner') ? 'verified' : 'pending';
        $harga_beli = ($user->role === 'karyawan')
            ? $product->harga_beli
            : $request->harga_beli;

        DB::beginTransaction();
        try {
            $barangMasuk = BarangMasuk::create([
                'product_id' => $request->product_id,
                'user_id' => $user->id,
                'jumlah' => $request->jumlah,
                'harga_beli' => $harga_beli,
                'keterangan' => $request->keterangan,
                'status' => $status,
            ]);

            if ($status === 'verified') {
                $product->harga_beli = $harga_beli;
                $product->save();

                $this->stockService->recordMovement([
                    'product_id' => $request->product_id,
                    'transaction_type' => 'purchase',
                    'quantity' => $request->jumlah,
                    'price' => $harga_beli,
                    'reference_no' => 'BM-' . $barangMasuk->id,
                    'notes' => $request->keterangan ?? 'Barang masuk',
                    'user_id' => $user->id,
                ]);
            }

            DB::commit();

            if ($user->role === 'karyawan') {
                $message = 'Data barang masuk berhasil ditambahkan. Menunggu owner untuk verifikasi.';
            } else {
                $message = 'Barang masuk berhasil ditambahkan dan stok telah diperbarui.';
            }

            return redirect()->route('barang_masuk.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan barang masuk: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $barangMasuk = BarangMasuk::findOrFail($id);
        $this->authorize('update', $barangMasuk);

        $products = Product::all();
        return view('barang_masuk.edit', compact('barangMasuk', 'products'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $barangMasuk = BarangMasuk::findOrFail($id);
        $this->authorize('update', $barangMasuk);

        $rules = [
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ];

        if ($user->role !== 'karyawan') {
            $rules['harga_beli'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules, [
            'product_id.required' => 'Nama barang wajib dipilih',
            'product_id.exists' => 'Barang tidak ditemukan',
            'jumlah.required' => 'Jumlah barang wajib diisi',
            'jumlah.min' => 'Jumlah minimal 1',
            'harga_beli.required' => 'Harga beli wajib diisi',
            'harga_beli.min' => 'Harga tidak boleh negatif',
        ]);

        DB::beginTransaction();
        try {
            if ($barangMasuk->status === 'verified') {
                $this->stockService->recordMovement([
                    'product_id' => $barangMasuk->product_id,
                    'transaction_type' => 'adjustment_minus',
                    'quantity' => $barangMasuk->jumlah,
                    'reference_no' => 'UPD-BM-' . $barangMasuk->id,
                    'notes' => 'Revisi barang masuk (pengembalian stok lama)',
                    'user_id' => $user->id,
                ]);
            }

            $barangMasuk->product_id = $request->product_id;
            $barangMasuk->jumlah = $request->jumlah;
            $barangMasuk->keterangan = $request->keterangan;

            if ($user->role !== 'karyawan') {
                $barangMasuk->harga_beli = $request->harga_beli;
            }

            $barangMasuk->save();

            if ($barangMasuk->status === 'verified') {
                $product = Product::find($request->product_id);
                if ($user->role !== 'karyawan') {
                    $product->harga_beli = $request->harga_beli;
                    $product->save();
                }

                $this->stockService->recordMovement([
                    'product_id' => $request->product_id,
                    'transaction_type' => 'purchase',
                    'quantity' => $request->jumlah,
                    'price' => $barangMasuk->harga_beli,
                    'reference_no' => 'UPD-BM-' . $barangMasuk->id,
                    'notes' => 'Revisi barang masuk (stok baru)',
                    'user_id' => $user->id,
                ]);
            }

            DB::commit();
            return redirect()->route('barang_masuk.index')->with('success', 'Data barang masuk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui barang masuk: ' . $e->getMessage())->withInput();
        }
    }

    public function verify($id)
    {
        $barangMasuk = BarangMasuk::findOrFail($id);
        $this->authorize('verify', $barangMasuk);

        DB::beginTransaction();
        try {
            $barangMasuk = BarangMasuk::findOrFail($id);

            if ($barangMasuk->status === 'verified') {
                throw new \Exception('Barang masuk ini sudah terverifikasi.');
            }

            if (!$barangMasuk->hasPrice()) {
                throw new \Exception('Harga beli harus diisi terlebih dahulu sebelum verifikasi.');
            }

            $product = $barangMasuk->product;
            $product->harga_beli = $barangMasuk->harga_beli;
            $product->save();

            $barangMasuk->status = 'verified';
            $barangMasuk->save();

            $this->stockService->recordMovement([
                'product_id' => $barangMasuk->product_id,
                'transaction_type' => 'purchase',
                'quantity' => $barangMasuk->jumlah,
                'price' => $barangMasuk->harga_beli,
                'reference_no' => 'BM-' . $barangMasuk->id,
                'notes' => $barangMasuk->keterangan ?? 'Barang masuk verified',
                'user_id' => $barangMasuk->user_id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Barang masuk telah diverifikasi, stok dan harga beli diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal verifikasi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $data = BarangMasuk::findOrFail($id);
        $this->authorize('delete', $data);

        DB::beginTransaction();
        try {
            if ($data->status === 'verified') {
                $this->stockService->recordMovement([
                    'product_id' => $data->product_id,
                    'transaction_type' => 'adjustment_minus',
                    'quantity' => $data->jumlah,
                    'reference_no' => 'DEL-BM-' . $data->id,
                    'notes' => 'Pembatalan barang masuk (dihapus)',
                    'user_id' => $user->id,
                ]);
            }

            $data->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Data barang masuk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus barang masuk: ' . $e->getMessage());
        }
    }

    public function updateHarga(Request $request, $id)
    {
        $barangMasuk = BarangMasuk::findOrFail($id);
        $this->authorize('updateHarga', $barangMasuk);

        $request->validate([
            'harga_beli' => 'required|numeric|min:0',
        ], [
            'harga_beli.required' => 'Harga beli wajib diisi',
            'harga_beli.min' => 'Harga tidak boleh negatif',
        ]);

        $barangMasuk = BarangMasuk::findOrFail($id);

        if ($barangMasuk->status === 'verified') {
            return redirect()->back()->with('error', 'Tidak dapat mengubah harga barang yang sudah diverifikasi.');
        }

        $barangMasuk->harga_beli = $request->harga_beli;
        $barangMasuk->save();

        return redirect()->back()->with('success', 'Harga beli berhasil diperbarui.');
    }
}
