<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'nama_sales',       // Nama sales yang datang
        'perusahaan',       // Nama perusahaan atau supplier
        'tanggal_kunjungan',// Tanggal kunjungan
        'tujuan',           // Tujuan kunjungan (contoh: menawarkan produk, ambil PO, follow-up)
        'hasil_kunjungan',  // Ringkasan hasil atau catatan kunjungan
        'kontak_sales',     // Nomor WA / HP sales
        'user_id',          // Siapa yang mencatat kunjungan (owner/karyawan)
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
