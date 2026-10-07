# PRD — INVENTORA

## 1. Ringkasan
INVENTORA adalah sistem manajemen inventori untuk Toko Karomah. Modernisasi ini mempertahankan data dan proses bisnis yang masih relevan, lalu mengganti pengalaman kasir, laporan/nota, dashboard, dan fondasi teknis secara bertahap serta terukur.

## 2. Tujuan
1. Mempercepat transaksi kasir dan meminimalkan salah input.
2. Menghapus modul absensi beserta seluruh akses, menu, rute, dan relasi datanya.
3. Menjadikan dokumen pembelian rapi, konsisten, dan dapat dicetak pada printer thermal/envelope setelah ukuran media dikonfirmasi.
4. Menyediakan dashboard berbeda untuk owner dan karyawan, dengan informasi sesuai tanggung jawabnya.
5. Menyediakan REST API internal yang aman untuk integrasi masa depan, sementara UI utama memakai Livewire.
6. Memindahkan database dari SQLite ke PostgreSQL tanpa kehilangan data yang diperlukan.

## 3. Peran
| Peran | Hak utama |
|---|---|
| Owner | Dashboard bisnis, laporan, master data, persetujuan, pengaturan, seluruh transaksi |
| Karyawan | Kasir, cek stok, barang masuk sesuai izin, dashboard operasional |

Semua rute, Livewire component, dan endpoint API wajib menerapkan policy/middleware peran. Rute debug tidak boleh tersedia di produksi.

## 4. Kebutuhan fungsional

### 4.1 Kasir
- Halaman fokus keyboard: cari/scan produk, tambah kuantitas, hapus item, diskon bila memiliki izin, dan pembayaran.
- Pencarian produk melalui barcode/SKU/nama dengan hasil cepat dan stok tersedia.
- Keranjang tersimpan selama sesi kasir aktif, subtotal, total, bayar, kembalian, dan validasi nominal bayar terlihat jelas.
- Checkout atomik: nomor invoice unik, stok berubah sekali, movement stok tercatat sekali, dan transaksi gagal seluruhnya bila stok tidak cukup.
- Setelah sukses, tampilkan struk dan pilihan cetak atau transaksi baru.
- Riwayat transaksi, detail, pembatalan, dan cetak ulang dibatasi oleh peran serta tercatat auditnya.

### 4.2 Penghapusan absensi
- Hapus menu, route, controller, view, model, policy, job, test, dan endpoint absensi.
- Lepaskan ketergantungan absensi dari perhitungan slip gaji; keputusan modul penggajian harus dikonfirmasi sebelum dihapus atau diubah.
- Jangan menghapus data produksi tanpa backup dan persetujuan eksplisit.

### 4.3 Nota pembelian
- Definisikan satu template dokumen untuk purchase order dan satu untuk penerimaan/pembelian bila keduanya diperlukan.
- Isi minimal: identitas toko dan pemasok, nomor dokumen, tanggal, daftar item, qty, harga, subtotal, total, catatan, dan area tanda tangan bila diperlukan.
- Gunakan CSS print `@page` dan unit mm; font, margin, dan pemenggalan baris harus stabil.
- Konfirmasi ukuran printer final sebelum implementasi: thermal 58 mm, thermal 80 mm, atau envelope (mis. DL/C5). Istilah “printer ukuran envelop” belum cukup spesifik.
- Sediakan preview browser dan cetak PDF; uji dengan data item panjang, banyak item, dan nominal besar.

### 4.4 Dashboard
**Owner:** penjualan hari/bulan ini, jumlah transaksi, laba bila data HPP valid, stok kritis, produk terlaris, tren penjualan, purchase order menunggu, dan tindakan cepat.

**Karyawan:** transaksi hari ini, stok yang perlu perhatian, barang masuk/pesanan yang menunggu proses sesuai izin, serta tindakan cepat ke kasir dan produk.

Prinsip: metrik paling penting di atas, kartu tidak duplikat, grafik memiliki rentang waktu jelas, empty state bermakna, dan tidak menampilkan data yang tidak diizinkan.

### 4.5 API
- Prefix `/api/v1`, autentikasi Laravel Sanctum, throttle, validasi Form Request, Resource/JSON terstandar, dan error terstruktur.
- Fase awal API: autentikasi token, produk, kategori, stok, transaksi POS, dan purchase order hanya bila UI sudah stabil.
- API dan Livewire menggunakan action/service bisnis yang sama; controller API tidak boleh menduplikasi aturan stok.
- Dokumentasi endpoint melalui OpenAPI/Swagger atau koleksi Postman yang disimpan dalam repo.

## 5. Non-fungsional dan arsitektur
- Laravel 12, PHP 8.2+, PostgreSQL, Livewire 3, Tailwind CSS 4, dan Flux UI (versi gratis) sebagai komponen UI utama.
- Hapus Bootstrap, jQuery, Select2, dan Flowbite secara bertahap setelah seluruh pemakaiannya diganti; jangan mencampur framework CSS pada halaman baru.
- Struktur: Livewire/API Controller → Form Request → Action/Service → Model/Repository bila benar-benar diperlukan → database transaction. Policy untuk otorisasi.
- Traits hanya untuk perilaku lintas kelas yang stateless, misalnya `ApiResponse` untuk respons JSON dan `InteractsWithExternalApi` untuk konfigurasi HTTP client. Integrasi API eksternal wajib tetap dibungkus oleh service berkontrak/interface agar dapat diuji dan diganti.
- Gunakan database constraints, foreign key, index pencarian, unique invoice, dan transaksi PostgreSQL untuk menjaga konsistensi.
- Rahasia hanya berada di `.env`, tidak pernah di-commit. Sediakan `.env.example` tanpa nilai rahasia.

## 6. Kriteria penerimaan global
- `php artisan migrate:fresh --seed` berhasil pada PostgreSQL kosong.
- Test fitur POS membuktikan stok dan stock movement berubah tepat sekali; stok tidak dapat negatif.
- Owner dan karyawan tidak dapat mengakses resource lintas peran.
- Tidak ada route/menu/modul absensi aktif.
- Nota lulus uji preview dan cetak pada ukuran media yang telah disetujui.
- UI utama tidak mengandalkan Bootstrap/jQuery.
- Endpoint API terdokumentasi, tervalidasi, terautentikasi, dan memiliki test minimal sukses, validasi gagal, serta forbidden.
