<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    private $cacheKey = 'stock_notifications';
    private $cacheExpiration = 86400; // 24 jam

    /**
     * Cek dan buat notifikasi stok (disimpan di cache)
     */
    public function checkAndCreateStockNotification(Product $product, $userId = null)
    {
        if (!$this->shouldCreateNotification($product)) {
            return null;
        }

        $notification = [
            'id' => uniqid('notif_'),
            'type' => $this->determineStockType($product),
            'product_id' => $product->id,
            'product_name' => $product->nama,
            'title' => $this->generateTitle($product),
            'message' => $this->generateMessage($product),
            'current_stock' => $product->stok,
            'reorder_point' => $product->rop,
            'safety_stock' => $product->safety_stock,
            'severity' => $this->determineSeverity($product),
            'icon' => $this->getIcon($this->determineSeverity($product)),
            'badge_color' => $this->getBadgeColor($this->determineSeverity($product)),
            'is_read' => false,
            'created_at' => now()->toDateTimeString(),
            'user_id' => $userId ?? auth()->id(),
        ];

        // Simpan ke cache
        $this->addNotification($notification);

        return $notification;
    }

    /**
     * Tambah notifikasi ke cache
     */
    private function addNotification($notification)
    {
        $notifications = $this->getAllNotifications();
        
        // Cek duplikat (produk yang sama, belum dibaca, hari yang sama)
        $isDuplicate = collect($notifications)->first(function ($n) use ($notification) {
            return $n['product_id'] == $notification['product_id'] 
                && $n['type'] == $notification['type']
                && !$n['is_read']
                && date('Y-m-d', strtotime($n['created_at'])) == date('Y-m-d');
        });

        if (!$isDuplicate) {
            $notifications[] = $notification;
            Cache::put($this->cacheKey, $notifications, $this->cacheExpiration);
        }

        return $notification;
    }

    /**
     * Ambil semua notifikasi dari cache
     */
    public function getAllNotifications()
    {
        return Cache::get($this->cacheKey, []);
    }

    /**
     * Ambil notifikasi belum dibaca
     */
    public function getUnreadNotifications()
    {
        $all = $this->getAllNotifications();
        return array_filter($all, fn($n) => !$n['is_read']);
    }

    /**
     * Hitung jumlah notifikasi belum dibaca
     */
    public function getUnreadCount()
    {
        return count($this->getUnreadNotifications());
    }

    /**
     * Hitung jumlah notifikasi critical belum dibaca
     */
    public function getCriticalCount()
    {
        $unread = $this->getUnreadNotifications();
        return count(array_filter($unread, fn($n) => in_array($n['severity'], ['critical', 'danger'])));
    }

    /**
     * Ambil notifikasi terbaru (limit)
     */
    public function getRecentNotifications($limit = 5)
    {
        $unread = $this->getUnreadNotifications();
        
        // Sort by created_at desc
        usort($unread, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        
        return array_slice($unread, 0, $limit);
    }

    /**
     * Tandai notifikasi sebagai dibaca
     */
    public function markAsRead($notificationId)
    {
        $notifications = $this->getAllNotifications();
        
        foreach ($notifications as &$notification) {
            if ($notification['id'] == $notificationId) {
                $notification['is_read'] = true;
                $notification['read_at'] = now()->toDateTimeString();
                break;
            }
        }
        
        Cache::put($this->cacheKey, $notifications, $this->cacheExpiration);
    }

    /**
     * Tandai semua notifikasi sebagai dibaca
     */
    public function markAllAsRead()
    {
        $notifications = $this->getAllNotifications();
        
        foreach ($notifications as &$notification) {
            $notification['is_read'] = true;
            $notification['read_at'] = now()->toDateTimeString();
        }
        
        Cache::put($this->cacheKey, $notifications, $this->cacheExpiration);
        
        return count($notifications);
    }

    /**
     * Hapus notifikasi
     */
    public function deleteNotification($notificationId)
    {
        $notifications = $this->getAllNotifications();
        $notifications = array_filter($notifications, fn($n) => $n['id'] != $notificationId);
        
        Cache::put($this->cacheKey, array_values($notifications), $this->cacheExpiration);
    }

    /**
     * Hapus semua notifikasi yang sudah dibaca
     */
    public function clearRead()
    {
        $notifications = $this->getAllNotifications();
        $unread = array_filter($notifications, fn($n) => !$n['is_read']);
        
        Cache::put($this->cacheKey, array_values($unread), $this->cacheExpiration);
        
        return count($notifications) - count($unread);
    }

    /**
     * Hapus semua notifikasi
     */
    public function clearAll()
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Cek apakah perlu buat notifikasi
     */
    private function shouldCreateNotification(Product $product): bool
    {
        return $product->stok <= 0 || 
               $product->stok <= $product->safety_stock || 
               $product->stok <= $product->rop;
    }

    /**
     * Tentukan tipe notifikasi
     */
    private function determineStockType(Product $product): string
    {
        if ($product->stok <= 0) {
            return 'out_of_stock';
        } elseif ($product->stok <= $product->safety_stock) {
            return 'critical_stock';
        } elseif ($product->stok <= $product->rop) {
            return 'reorder_point';
        }
        return 'stock_alert';
    }

    /**
     * Tentukan severity
     */
    private function determineSeverity(Product $product): string
    {
        if ($product->stok <= 0) {
            return 'danger';
        } elseif ($product->stok <= $product->safety_stock) {
            return 'critical';
        } elseif ($product->stok <= $product->rop) {
            return 'warning';
        }
        return 'info';
    }

    /**
     * Generate pesan notifikasi
     */
    private function generateMessage(Product $product): string
    {
        if ($product->stok <= 0) {
            return "Produk '{$product->nama}' sudah HABIS! Stok: 0. Segera lakukan pembelian.";
        } elseif ($product->stok <= $product->safety_stock) {
            return "Produk '{$product->nama}' dalam kondisi KRITIS! Stok: {$product->stok} unit, di bawah safety stock ({$product->safety_stock} unit).";
        } elseif ($product->stok <= $product->rop) {
            return "Produk '{$product->nama}' telah mencapai Reorder Point! Stok: {$product->stok} unit, ROP: {$product->rop} unit.";
        }
        return "Stok produk '{$product->nama}' perlu perhatian. Stok: {$product->stok} unit.";
    }

    /**
     * Generate judul notifikasi
     */
    private function generateTitle(Product $product): string
    {
        if ($product->stok <= 0) {
            return "🚨 STOK HABIS - {$product->nama}";
        } elseif ($product->stok <= $product->safety_stock) {
            return "⚠️ STOK KRITIS - {$product->nama}";
        } elseif ($product->stok <= $product->rop) {
            return "⚡ Reorder Point - {$product->nama}";
        }
        return "ℹ️ Alert Stok - {$product->nama}";
    }

    /**
     * Get icon berdasarkan severity
     */
    private function getIcon($severity): string
    {
        return match($severity) {
            'danger' => '🚨',
            'critical' => '⚠️',
            'warning' => '⚡',
            default => 'ℹ️',
        };
    }

    /**
     * Get badge color berdasarkan severity
     */
    private function getBadgeColor($severity): string
    {
        return match($severity) {
            'danger' => 'bg-red-500',
            'critical' => 'bg-orange-500',
            'warning' => 'bg-yellow-500',
            default => 'bg-blue-500',
        };
    }

    /**
     * Cek semua produk dan buat notifikasi
     */
    public function checkAllProducts()
    {
        $products = Product::all();
        $notificationsCreated = 0;

        foreach ($products as $product) {
            if ($this->checkAndCreateStockNotification($product)) {
                $notificationsCreated++;
            }
        }

        return $notificationsCreated;
    }

    /**
     * Get time ago helper
     */
    public function getTimeAgo($datetime)
    {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return $diff . ' detik yang lalu';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' menit yang lalu';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' jam yang lalu';
        } else {
            return floor($diff / 86400) . ' hari yang lalu';
        }
    }
}