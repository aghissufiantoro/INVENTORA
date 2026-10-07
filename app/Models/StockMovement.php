<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'transaction_type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_no',
        'price',
        'notes',
        'user_id',
        'transaction_date'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'price' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function transactionTypeLabels()
    {
        return [
            'purchase' => 'Pembelian/Barang Masuk',
            'sale' => 'Penjualan',
            'return_from_customer' => 'Return dari Customer',
            'adjustment_plus' => 'Penyesuaian (+)',
            'adjustment_minus' => 'Penyesuaian (-)',
        ];
    }

    public function getTransactionTypeLabelAttribute()
    {
        return self::transactionTypeLabels()[$this->transaction_type] ?? $this->transaction_type;
    }
}