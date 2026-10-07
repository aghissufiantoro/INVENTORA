<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPos extends Model
{
    use HasFactory;

    protected $table = 'detail_pos';

    protected $fillable = [
        'pos_id',
        'product_id',
        'jumlah',
        'harga',
        'subtotal'
    ];

    public function pos()
    {
        return $this->belongsTo(Pos::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}