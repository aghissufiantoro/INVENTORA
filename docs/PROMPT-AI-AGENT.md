# Prompt Operasional untuk AI Agent

## Prompt sistem proyek (pakai di awal setiap sesi)
```text
Kamu adalah AI engineer untuk proyek Laravel INVENTORA, sistem manajemen inventori Toko Karomah. Bahasa komunikasi: Indonesia.

Baca terlebih dahulu `docs/PRD-INVENTORA.md` dan `docs/ROADMAP-MODERNISASI.md`. Jangan melompati fase roadmap. Fokus hanya pada tugas yang saya berikan pada sesi ini.

Aturan wajib:
1. Sebelum mengubah file, audit file terkait dan jelaskan rencana singkat beserta dampaknya.
2. Jangan menghapus data, migration, atau fitur yang masih dipakai tanpa persetujuan eksplisit.
3. Jangan mengubah dependency, schema, atau konfigurasi environment secara luas bila tidak diminta.
4. Semua perubahan stok harus memakai satu action/service yang berjalan di dalam database transaction dan menulis stock movement tepat sekali.
5. Terapkan authorization pada route, Livewire component, dan API. Jangan membuat debug route atau membocorkan secret.
6. Gunakan Laravel 12, PostgreSQL, Livewire 3, Tailwind 4, Flux UI. Jangan menambah Bootstrap/jQuery untuk UI baru.
7. API memakai `/api/v1`, Form Request, API Resource, Sanctum, policy, test, dan service domain bersama UI.
8. Trait hanya untuk perilaku lintas kelas yang stateless; jangan menyimpan aturan bisnis atau query transaksi penting di trait.
9. Jangan mengedit file di luar scope. Jangan commit, push, atau menghapus file kecuali saya meminta.
10. Setelah selesai, jalankan test/lint yang relevan dan laporkan: ringkasan, file berubah, perintah verifikasi+hasil, serta risiko/pekerjaan lanjutan.

Jika kebutuhan tidak lengkap atau berisiko tinggi, berhenti dan ajukan pertanyaan pilihan yang spesifik. Jangan berasumsi.
```

## Prompt fase 0 — Audit dan baseline
```text
Jalankan Fase 0 untuk INVENTORA. Jangan mengubah kode aplikasi terlebih dahulu.

1. Periksa status Git, `.gitignore`, `.env`/`.env.example`, versi PHP/Composer/Node, composer.json, package.json, config database, migrations, seeders, dan test.
2. Buat laporan `docs/BASELINE-AUDIT.md` berisi prasyarat lokal, perintah setup yang aman, risiko, modul yang ditemukan, dan daftar keputusan bisnis yang perlu saya jawab.
3. Jangan membuat atau menghapus database. Jangan menginisialisasi/push GitHub tanpa instruksi saya.
4. Validasi perintah hanya dalam mode read-only jika environment belum siap.
```

## Prompt fase 1 — Perbaiki fondasi migration dan stok
```text
Kerjakan hanya Fase 1: stabilkan migration, otorisasi, dan integritas stok.

Sebelum coding, tampilkan daftar migration duplikat/bentrok dan semua jalur yang mengubah stok. Ajukan rencana untuk skema migration bersih yang dapat `migrate:fresh --seed` tanpa kehilangan kemampuan migrasi data legacy.

Implementasikan action/service tunggal untuk mutasi stok yang atomik, validasi stok tidak negatif, dan pencatatan movement satu kali. Gunakan policy/middleware role konsisten dan hilangkan debug route terbuka. Tambahkan test regresi untuk POS, PO/barang masuk/retur yang memengaruhi stok, dan larangan akses lintas role.

Jangan melakukan migrasi PostgreSQL atau rebuild UI pada tugas ini. Jalankan test relevan dan laporkan hasil.
```

## Prompt fase 2 — PostgreSQL
```text
Kerjakan hanya Fase 2 PostgreSQL. Gunakan hasil Fase 1 sebagai baseline.

Buat `.env.example` aman dan dokumentasi setup PostgreSQL lokal. Sesuaikan migration/query untuk PostgreSQL. Buat command import data SQLite ke PostgreSQL yang idempotent dan hanya berjalan bila dipanggil eksplisit; command wajib memiliki mode dry-run serta laporan jumlah record dan validasi stok.

Jangan menjalankan import pada data asli, menghapus SQLite, atau menulis kredensial. Tambahkan test/setup database PostgreSQL yang sesuai. Laporkan perintah yang harus saya jalankan sendiri.
```

## Prompt fase 3 — API
```text
Kerjakan hanya fondasi API `/api/v1` untuk INVENTORA.

Tambahkan Sanctum, format respons JSON konsisten, Form Request, API Resource, policy, rate limit, dan dokumentasi endpoint. Buat `ApiResponse` trait khusus penyusun respons; aturan domain tetap berada dalam Action/Service. Implementasikan endpoint produk, kategori, stok, dan POS dengan Action/Service yang sama seperti UI. Sertakan test untuk auth, validation, authorization, dan transaksi atomik.

Jangan membuat endpoint untuk seluruh modul sekaligus dan jangan menggandakan aturan bisnis controller lama.
```

## Prompt fase 4 — Kasir Livewire
```text
Kerjakan ulang halaman kasir memakai Livewire 3, Tailwind 4, dan Flux UI. Jangan mengubah aturan domain checkout yang sudah teruji.

Kasir harus mendukung fokus keyboard, scan/cari barcode-SKU-nama, tambah/kurangi item, hapus, ringkasan pembayaran, validasi uang, checkout, struk, cetak ulang, dan transaksi baru. Terapkan otorisasi karyawan/owner sesuai policy.

Uji di browser: transaksi normal, scan berulang, stok habis, pembayaran kurang, refresh, dan cetak struk. Jangan menghapus halaman lama sebelum penggantinya lulus uji.
```

## Prompt fase 5 — Absensi, nota, dan dashboard
```text
Kerjakan hanya [PILIH SATU: penghapusan absensi / nota pembelian / dashboard].

Baca PRD dan identifikasi seluruh rute, menu, model, controller, view, test, API, relasi, dan data yang terdampak. Tampilkan rencana sebelum perubahan.

Untuk absensi: jangan menghapus data atau modul gaji sebelum saya mengonfirmasi dampaknya.
Untuk nota: tanyakan ukuran media pasti (58 mm, 80 mm, DL, C5, atau custom mm) jika belum diberikan; buat preview dan CSS print.
Untuk dashboard: buat tampilan terpisah owner/karyawan dengan metrik persis dari PRD dan policy yang konsisten.

Jalankan test dan uji browser setelah implementasi.
```

## Template prompt tugas kecil
```text
Scope: [satu fitur atau bug kecil].
Tujuan terukur: [kondisi selesai].
Batasan: [file/modul yang tidak boleh disentuh, keputusan bisnis].
Kriteria terima: [test dan perilaku yang harus lulus].

Mulai dengan membaca dokumen proyek dan file terkait. Berikan rencana singkat, implementasikan hanya scope ini, uji, lalu laporkan hasil tanpa commit/push.
```
