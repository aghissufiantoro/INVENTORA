# Baseline Audit — INVENTORA

## Ringkasan
Aplikasi hasil salinan Laravel 12 dengan POS berbasis Blade dan SQLite. Sebelum migrasi ke PostgreSQL dan Livewire, dilakukan perbaikan migration, model, seeder, dan route yang bermasalah.

## Prasyarat lokal
- PHP >= 8.2 dengan ekstensi: bcmath, ctype, curl, dom, fileinfo, intl, mbstring, openssl, pdo_pgsql, tokenizer, xml, zip
- Composer 2.x
- Node.js >= 20 dan npm
- PostgreSQL >= 14
- Laragon (Windows) atau setara

## Perintah setup
```bash
cp .env.example .env
# Edit DB_USERNAME dan DB_PASSWORD sesuai PostgreSQL lokal
# Buat database PostgreSQL: CREATE DATABASE inventora;
php artisan key:generate
composer install
npm install
php artisan migrate:fresh --seed
npm run dev
php artisan serve
```

## Akun seed default
- Owner: username `owner`, email `owner@karomah.test`, password `password`
- Karyawan: username `karyawan`, email `karyawan@karomah.test`, password `password`

## Perbaikan yang telah dilakukan

### Migration
1. Hapus duplikat `sessions` (sudah ada di `0001_000000`)
2. Hapus duplikat `kategoris` (sudah ada di `2025_07_21_152305`)
3. Hapus duplikat `pos` skema lama (sudah diganti `2025_10_16_055343`)
4. Hapus duplikat `rop` (sudah ada di `2025_08_15_150223`)
5. Hapus migration zombie `transaksis` (tabel tidak dipakai, FK ke `suppliers` salah urutan)
6. Hapus migration orphan `sales_visits` (tabel tidak dipakai)
7. Fix `2025_11_05_081001` — `kategori_id` tidak ada di `products`, sekarang add kolom baru dengan nullable
8. Fix `2025_11_01_071205` — `harga_beli` tidak ada di `barang_masuk`, sekarang add kolom baru dengan guard `hasColumn`
9. Fix `2025_10_16_051106` — `down()` drop nama tabel salah (`transactions_item` → `transaction_items`)
10. Fix `2025_10_27_131947` — wrap semua kolom dengan guard `hasColumn` untuk mencegah duplikat `safety_stock`
11. Rename file `2025_10_27_...php.php` → `2025_10_27_...php` (ekstensi ganda)

### Model
1. `Product.php` — hapus `maximum_stock` dari fillable dan method `getRecommendedOrderQuantity`
2. `Karyawan.php` — hapus `kehadiran`, `gaji`, `absen_today`, `is_sunday` dari fillable (modul absensi & penggajian dihapus)
3. `AktivitasUser.php` — ganti `'aksi'` → `'aktivitas'`

### Seeder & Factory
1. `KategoriSeeder.php` — fix class name `kategori` → `Kategori`, kolom `nama_kategori` → `nama`
2. `UserFactory.php` — tambah `username` dan `role` ke definition
3. `DatabaseSeeder.php` — buat 2 user default (owner & karyawan) dan panggil `KategoriSeeder`

### Route
1. Hapus duplikat route name `pos.history`
2. Hapus debug route `/debug-detail-pos` dengan SQL MySQL-specific
3. Hapus semua route absensi dan penggajian karyawan
4. Tambahkan middleware `role:owner` pada route inventory-approval dan karyawan management

### Policy (Otorisasi Resource-Level)
1. `BarangMasukPolicy` — karyawan hanya bisa edit miliknya yang belum verified, hanya owner yang bisa hapus/verifikasi
2. `ProductPolicy` — hanya owner/admin yang bisa create/update/adjust stock, hanya owner yang bisa delete
3. `ReturPolicy` — hanya owner yang bisa approve/reject
4. `BarangRusakPolicy` — hanya owner yang bisa approve/reject/delete

### Modul yang Dihapus
1. **Absensi karyawan** — hapus method `absensi()`, `absensiStore()` dari KaryawanController
2. **Penggajian** — hapus method `slipGaji()`, `slipPdf()` dari KaryawanController
3. **View absensi** — hapus `absensi.blade.php`, `slip.blade.php`, `slip_pdf.blade.php`
4. **Migration kehadiran/gaji** — hapus migration `2025_09_30_135716_add_kehadiran_gaji_from_karyawans_table.php`
5. **Migration absen column** — hapus migration `2025_12_04_204122_add_absen_column_to_karyawans.php`

### Migrasi PostgreSQL (Fase 2)
1. Hapus semua `->after()` dari 9 migration file (PostgreSQL tidak support column ordering)
2. Fix `->date()->default(now())` → `->default(DB::raw('CURRENT_DATE'))` di `create_pos_table`
3. Hapus `->length()` dari integer column di `create_detail_pos_table`
4. Aktifkan extension `pdo_pgsql` dan `pgsql` di php.ini
5. Database `inventora` berhasil dibuat di PostgreSQL
6. 35 migration berhasil dijalankan tanpa error
7. Seeder (KategoriSeeder) berhasil

### REST API Foundation (Fase 3)
1. **Laravel Sanctum v4.3.3** terinstall dan terkonfigurasi
2. **HasApiTokens trait** ditambahkan ke User model
3. **ApiResponse trait** dibuat untuk standardisasi response API
4. **7 API Controllers** dibuat:
   - `ApiAuthController` — login, logout, token management
   - `ApiProductController` — products CRUD, stock adjustment, barcode lookup
   - `ApiCategoryController` — categories CRUD
   - `ApiTransactionController` — POS transactions, today summary
   - `ApiStockMovementController` — stock movements history
   - `ApiBarangMasukController` — incoming goods with verify workflow
   - `ApiBarangRusakController` — damaged goods with approve/reject workflow
   - `ApiReturController` — returns with approve/reject workflow
5. **39 API routes** terdaftar dengan prefix `/api/v1/`
6. **Authentication** menggunakan Bearer token (Sanctum)
7. **Authorization** terintegrasi dengan Laravel Policies
8. **API Documentation** lengkap di `docs/API-DOCUMENTATION.md`

### Rebuild Cashier dengan Livewire (Fase 4)
1. **Livewire v4.4.7** terinstall
2. **Livewire Pos Component** dibuat (`app/Livewire/Pos/Cashier.php`):
   - Real-time cart management dengan session
   - Product search dengan barcode support
   - Quick product grid untuk akses cepat
   - Quantity increment/decrement tanpa reload
   - Checkout modal dengan quick cash buttons
   - Auto-calculate kembalian
   - Stock validation real-time
3. **Modern UI** dengan Tailwind CSS:
   - Split-panel layout (products left, cart right)
   - Responsive design
   - Flash messages untuk feedback
   - Keyboard-friendly (autofocus pada search)
4. **Route updated**: `/pos` sekarang menggunakan Livewire component
5. **Layout updated**: Livewire styles dan scripts ditambahkan ke `app.blade.php`

### Cetak Nota Thermal 58x80mm (Fase 5)
1. **Thermal receipt view** dibuat (`resources/views/pos/receipt-thermal.blade.php`):
   - `@page` CSS size: 58mm auto untuk thermal printer
   - Font monospace (Courier New), ukuran 11px
   - Layout compact dengan dashed borders
   - Header toko, invoice number, daftar item, total, pembayaran, footer
   - `@media print` rules untuk menyembunyikan tombol cetak
   - Tombol "Cetak" dan "Transaksi Baru" untuk layar
2. **PosController updated**: method `receipt()` sekarang menggunakan view thermal

### Redesign Dashboard (Fase 5)
1. **Dashboard owner** (`resources/views/dashboard/index.blade.php`):
   - 4 gradient KPI cards: penjualan hari ini, transaksi, barang terjual, stok kritis
   - Indikator persentase vs kemarin (hijau/merah)
   - Tren Penjualan 7 Hari (line chart, Chart.js v4.4.0)
   - Top 5 Produk Terlaris (bar chart horizontal)
   - Status Stok (doughnut chart: aman/rendah/habis)
   - Detail Stok Kritis table dengan link restock
2. **Dashboard karyawan**:
   - 3 stat cards: transaksi, penjualan, barang terjual
   - Quick Actions grid: Kasir, Barang Masuk, Barang Rusak, Retur
   - Alert stok kritis (conditional, hanya muncul jika ada)
3. **Chart.js v4.4.0** via CDN, responsive, format Rupiah pada axis

## Risiko dan ketergantungan

### Kritis
1. **Integritas stok**: `StockMovementService::recordMovement()` menghitung `stockAfter` tetapi tidak pernah menetapkan `$product->stok = $stockAfter`. Penerimaan PO dan retur berpotensi hanya mencatat movement tanpa mengubah kuantitas stok. Perlu diperbaiki di Fase 1.
2. **Observer POS**: Observer berjalan ketika header POS baru dibuat, sebelum detail dibuat. Controller melakukan pengurangan stok dan pencatatan manual. Boundary transaksi perlu disatukan.
3. **Otorisasi**: Middleware role tersedia tetapi tidak dipasang pada route. Banyak modul berpotensi dapat diakses semua user terautentikasi.

### Sedang
1. **PostgreSQL compatibility**: Migration menggunakan `enum()` dan `->after()` yang berperilaku berbeda di PostgreSQL. Perlu review saat migrasi database.
2. **Frontend campuran**: Tailwind/Flowbite dengan Bootstrap dan berbagai CDN. Perlu konsolidasi dependency saat migrasi ke Livewire.

### Rendah
1. **Testing**: Feature test mengharapkan `/` berstatus 200, padahal `/` redirect ke login. Test akan gagal.
2. **phpunit.xml**: Masih menggunakan SQLite in-memory. Perlu diubah ke PostgreSQL untuk testing yang realistis.

## Keputusan bisnis yang telah dikonfirmasi

1. **Ukuran cetak nota final**: Thermal 58 mm x 80 mm
2. **Modul penggajian & absensi**: Hapus sepenuhnya (tidak diganti)
3. **Data historis**: Semua data dari SQLite akan dimigrasikan ke PostgreSQL

## Status
- [x] Audit migration dan skema
- [x] Perbaikan migration kritis
- [x] Perbaikan model dan seeder
- [x] Perbaikan route
- [x] Setup environment
- [x] Keputusan bisnis dikonfirmasi
- [x] Perbaikan integritas stok (Fase 1)
- [x] Terapkan otorisasi role dan policy (Fase 1)
- [x] Hapus modul absensi dan penggajian (Fase 1)
- [x] Migrasi PostgreSQL (Fase 2) — 35 migration berhasil, semua `->after()` dihapus, pdo_pgsql diaktifkan
- [x] REST API foundation (Fase 3) — Laravel Sanctum, 7 API controllers, 39 routes, dokumentasi lengkap
- [x] Rebuild cashier dengan Livewire (Fase 4) — Livewire v4.4.7, real-time cart, modern UI
- [x] Cetak nota thermal 58x80mm (Fase 5) — thermal receipt dengan @page 58mm, monospace font, compact layout
- [x] Redesign dashboard owner dan karyawan (Fase 5) — gradient KPI cards, Chart.js charts, role-based views
- [x] UAT dan release (Fase 6) — semua fitur terverifikasi: POS flow lengkap, dashboard owner & karyawan, REST API endpoints

## Hasil UAT (Fase 6)

### Perbaikan yang dilakukan selama UAT
1. **Livewire POS rendering** — layout `app.blade.php` menggunakan `@yield('content')` yang tidak kompatibel dengan Livewire full-page components. Ditambahkan `{!! $slot ?? '' !!}` sebelum `@yield('content')` untuk mendukung kedua pattern.
2. **Product model fillable** — kolom `kategori` tidak ada di fillable array, ditambahkan untuk memungkinkan seeding.
3. **Column name mismatch** — view dan component POS menggunakan `nama_barang` dan `harga_jual`, sedangkan database menggunakan `nama` dan `harga`. Diperbaiki di `Cashier.php` dan `cashier.blade.php`.
4. **Barcode column** — kolom `barcode` tidak ada di tabel products, dihapus dari search query dan view.

### Fitur terverifikasi
1. **POS Flow (Livewire)**:
   - Product grid menampilkan 5 produk dengan stok dan harga
   - Klik produk menambahkan ke keranjang
   - Cart menampilkan item dengan quantity controls (+/-)
   - Total dihitung otomatis
   - Checkout modal dengan quick cash buttons
   - Transaksi berhasil dicatat dengan invoice number
   - Stok berkurang otomatis setelah transaksi
   - Receipt page menampilkan detail transaksi

2. **Dashboard Owner**:
   - 4 KPI cards: Penjualan Hari Ini, Transaksi Hari Ini, Barang Terjual, Stok Kritis
   - Chart.js: Tren Penjualan 7 Hari, Top 5 Produk Terlaris
   - Status Stok dengan donut chart
   - Detail Stok Kritis table

3. **Dashboard Karyawan**:
   - 3 stat cards: Transaksi Hari Ini, Penjualan Hari Ini, Barang Terjual
   - Aksi Cepat: Kasir, Barang Masuk, Barang Rusak, Retur
   - Simplified view tanpa chart dan stock management

4. **REST API Endpoints**:
   - `POST /api/login` — authentication dengan email/password, returns Sanctum token
   - `GET /api/v1/products` — paginated product list dengan kategori relation
   - `GET /api/v1/transactions` — transaction list dengan details dan product info
   - `GET /api/v1/transactions/today-summary` — daily summary (total_transactions, total_revenue, total_paid)
   - `GET /api/v1/categories` — category list dengan products_count
   - Authorization menggunakan Bearer token header
