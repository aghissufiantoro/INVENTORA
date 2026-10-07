<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'barang_masuk';

    protected $fillable = [
        'product_id',
        'user_id',
        'jumlah',
        'harga_beli',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'jumlah' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cek apakah harga beli sudah diisi
     */
    public function hasPrice()
    {
        return !is_null($this->harga_beli) && $this->harga_beli > 0;
    }

    /**
     * Cek apakah bisa diverifikasi (harus ada harga)
     */
    public function canBeVerified()
    {
        return $this->status === 'pending' && $this->hasPrice();
    }
}