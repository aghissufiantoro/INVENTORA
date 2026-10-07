<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'nama',
        'kategori',
        'kategori_id',
        'harga',
        'harga_beli',
        'stok',
        'rop',
        'safety_stock',
        'lead_time_days',
        'avg_daily_usage',
        'max_daily_usage',
        'stock_status'
    ];

    // Relasi
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function detail_pos()
    {
        return $this->hasMany(DetailPos::class, 'product_id');
    }

    // ========== INVENTORY MANAGEMENT METHODS ==========

    /**
     * Hitung ROP otomatis berdasarkan formula
     * ROP = (Average Daily Usage × Lead Time) + Safety Stock
     */
    public function calculateReorderPoint()
    {
        return ($this->avg_daily_usage * $this->lead_time_days) + $this->safety_stock;
    }

    /**
     * Update ROP dan simpan ke database
     */
    public function updateReorderPoint()
    {
        $this->rop = $this->calculateReorderPoint();
        $this->save();
        return $this->rop;
    }

    /**
     * Update status stok berdasarkan level saat ini
     */
    public function updateStockStatus()
    {
        if ($this->stok <= 0) {
            $this->stock_status = 'critical';
        } elseif ($this->stok <= $this->safety_stock) {
            $this->stock_status = 'critical';
        } elseif ($this->stok <= $this->rop) {
            $this->stock_status = 'reorder';
        } elseif ($this->stok <= ($this->rop * 1.2)) {
            $this->stock_status = 'low';
        } else {
            $this->stock_status = 'normal';
        }

        $this->save();
        return $this->stock_status;
    }

    /**
     * Cek apakah produk perlu reorder
     */
    public function needsReorder()
    {
        return $this->stok <= $this->rop;
    }

    /**
     * Cek apakah produk dalam kondisi kritis
     */
    public function isCritical()
    {
        return $this->stok <= $this->safety_stock;
    }

    /**
     * Kurangi stok (untuk penjualan)
     */
    public function decreaseStock($quantity)
    {
        if ($this->stok < $quantity) {
            throw new \Exception("Stok tidak mencukupi! Stok tersedia: {$this->stok}");
        }

        $this->stok -= $quantity;
        $this->save();
        $this->updateStockStatus();

        return $this;
    }

    /**
     * Tambah stok (untuk pembelian/restock)
     */
    public function increaseStock($quantity)
    {
        $this->stok += $quantity;
        $this->save();
        $this->updateStockStatus();

        return $this;
    }

    /**
     * Hitung estimasi hari sampai stok habis
     */
    public function getDaysUntilStockout()
    {
        if ($this->avg_daily_usage <= 0) {
            return null;
        }

        return round($this->stok / $this->avg_daily_usage, 1);
    }

    /**
     * Hitung rekomendasi jumlah order
     */
    public function getRecommendedOrderQuantity()
    {
        return $this->rop * 2;
    }

    /**
     * Accessor untuk badge status HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'normal' => '<span class="badge bg-success">Normal</span>',
            'low' => '<span class="badge bg-warning">Low Stock</span>',
            'reorder' => '<span class="badge bg-warning text-dark">⚠ Reorder Point</span>',
            'critical' => '<span class="badge bg-danger">🚨 Critical</span>',
        ];

        return $badges[$this->stock_status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Accessor untuk warna badge (untuk Chart/UI)
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'normal' => 'success',
            'low' => 'warning',
            'reorder' => 'warning',
            'critical' => 'danger',
        ];

        return $colors[$this->stock_status] ?? 'secondary';
    }

    /**
     * Scope untuk filter produk yang perlu reorder
     */
    public function scopeNeedsReorder($query)
    {
        return $query->whereColumn('stok', '<=', 'rop')
            ->orWhere('stock_status', 'reorder')
            ->orWhere('stock_status', 'critical');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('stock_status', $status);
    }

    /**
     * Scope untuk produk normal
     */
    public function scopeNormalStock($query)
    {
        return $query->where('stock_status', 'normal');
    }

    /**
     * Scope untuk produk kritis
     */
    public function scopeCriticalStock($query)
    {
        return $query->where('stock_status', 'critical');
    }

    public function calculationRequests()
    {
        return $this->hasMany(InventoryCalculationRequest::class);
    }

    public function pendingCalculation()
    {
        return $this->hasOne(InventoryCalculationRequest::class)
            ->where('status', 'pending');
    }

    public function hasPendingCalculation()
    {
        return $this->pendingCalculation()->exists();
    }

}