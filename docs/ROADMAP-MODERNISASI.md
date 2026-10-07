# Roadmap Modernisasi INVENTORA

## Aturan utama
Jangan mengubah fitur dan infrastruktur sekaligus. Setiap fase menghasilkan aplikasi yang bisa dijalankan, diuji, dan direview sebelum fase berikutnya dimulai. Buat commit kecil setelah setiap fase selesai.

## Fase 0 — Baseline dan keputusan (wajib sebelum coding)
1. Inisialisasi Git lokal; buat branch `main` dan branch kerja per fase. Hubungkan GitHub setelah repository bersih dan `.gitignore` diverifikasi.
2. Salin database SQLite saat ini sebagai backup read-only. Inventarisasikan data produksi yang akan dimigrasikan.
3. Buat `.env.example`, tentukan PHP, Composer, Node, PostgreSQL, serta konfigurasi mail/queue yang dibutuhkan.
4. Putuskan tiga hal bersama owner: ukuran cetak nota final, apakah modul gaji tetap ada tanpa absensi, dan data historis mana yang wajib dibawa.
5. Jalankan aplikasi lama dan dokumentasikan alur kritis: login, POS, stok, PO/barang masuk, laporan.

**Gate:** baseline aplikasi dan backup tersedia; keputusan bisnis di atas sudah dijawab.

## Fase 1 — Stabilkan fondasi
1. Audit dan perbaiki migration yang duplikat/bentrok; skema harus dapat dibuat dari nol.
2. Perbaiki integritas stok: satu service/action transaksional untuk semua perubahan stok dan stock movement; hapus atau selaraskan observer yang tumpang tindih.
3. Tambahkan foreign key, index, unique constraint nomor invoice, dan policy/middleware role di semua rute.
4. Hapus debug route dan tulis test regresi untuk login, otorisasi, POS, stok, dan PO.

**Gate:** `migrate:fresh --seed` dan test inti lulus pada database sementara.

## Fase 2 — Migrasi PostgreSQL
1. Buat database dan role PostgreSQL khusus aplikasi dengan hak minimum.
2. Sesuaikan migration/model/query dari SQLite ke PostgreSQL; hindari SQL spesifik SQLite.
3. Tulis command migrasi data yang idempotent: extract SQLite → transform → load PostgreSQL, termasuk mapping foreign key dan validasi jumlah record.
4. Jalankan dry-run pada salinan data, rekonsiliasi tabel dan stok, kemudian siapkan rollback ke backup SQLite.

**Gate:** data hasil dry-run tervalidasi, aplikasi berjalan pada PostgreSQL, dan rollback terdokumentasi.

## Fase 3 — API dan kontrak domain
1. Instal/configure Sanctum, versioning `/api/v1`, format respons/error, rate-limit, dan dokumentasi OpenAPI/Postman.
2. Ekstrak aturan bisnis dari controller lama ke Action/Service yang dipakai Livewire dan API.
3. Buat trait `ApiResponse` dan, hanya jika diperlukan integrasi pihak ketiga, trait `InteractsWithExternalApi`; jangan letakkan aturan bisnis transaksi di trait.
4. Implementasikan endpoint produk, kategori, stok, dan POS beserta test auth, validation, authorization, dan transaction.

**Gate:** API terdokumentasi serta test endpoint dan domain lulus.

## Fase 4 — UI Livewire dan kasir
1. Instal Livewire 3, Tailwind 4, Flux UI; buat layout dan design token dasar.
2. Migrasikan navigasi dan dashboard tanpa mengubah proses bisnis.
3. Bangun ulang kasir sebagai Livewire component dengan dukungan barcode/keyboard, pencarian, keranjang, checkout atomik, struk, dan cetak ulang.
4. Uji langsung di browser menggunakan skenario transaksi cepat, stok habis, nominal kurang, scan berulang, dan refresh halaman.

**Gate:** pegawai dapat menyelesaikan transaksi end-to-end lebih cepat tanpa regresi stok.

## Fase 5 — Rapikan modul dan dokumen
1. Hapus absensi secara terkendali; lakukan perubahan modul gaji hanya sesuai keputusan Fase 0.
2. Implementasikan template nota pembelian dan stylesheet cetak sesuai ukuran media final.
3. Migrasikan dashboard owner dan karyawan, lalu hapus dependensi UI lama setelah tidak dipakai.

**Gate:** kriteria penerimaan PRD terpenuhi dan pengguna menyetujui preview/cetak.

## Fase 6 — UAT dan rilis
1. Jalankan seluruh test, static analysis/lint, dan uji browser manual.
2. UAT dengan owner dan kasir menggunakan salinan data; catat defect sebagai issue terpisah.
3. Backup production, aktifkan maintenance window, migrasikan data final, validasi stok/record, lalu rilis.
4. Pantau error, transaksi, dan stok pada hari pertama; siapkan rollback berdasarkan backup.

## Urutan kerja untuk AI agent
AI hanya boleh mengerjakan satu fase atau satu sub-tugas kecil per prompt. Sebelum edit, agent wajib membaca PRD, roadmap, file terkait, dan menjalankan test relevan. Sesudah edit, agent wajib melaporkan file berubah, perintah verifikasi, hasilnya, risiko tersisa, dan tidak boleh membuat keputusan bisnis yang belum dikonfirmasi.
