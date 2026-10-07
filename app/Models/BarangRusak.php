<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangRusak extends Model
{
    protected $table = 'barang_rusak';

    protected $fillable = [
        'produk_id',
        'user_id',
        'jumlah',
        'alasan',
        'foto',
        'status'
    ];

    public function produk()
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
