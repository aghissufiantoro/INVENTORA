<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use App\Models\Product;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Tampilkan semua notifikasi
     */
    public function index(Request $request)
    {
        $allNotifications = $this->notificationService->getAllNotifications();
        
        // Sort by created_at desc
        usort($allNotifications, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        // Filter berdasarkan status
        if ($request->has('status')) {
            if ($request->status === 'unread') {
                $allNotifications = array_filter($allNotifications, fn($n) => !$n['is_read']);
            } elseif ($request->status === 'read') {
                $allNotifications = array_filter($allNotifications, fn($n) => $n['is_read']);
            }
        }

        // Filter berdasarkan severity
        if ($request->has('severity')) {
            $allNotifications = array_filter($allNotifications, fn($n) => $n['severity'] == $request->severity);
        }

        // Filter berdasarkan type
        if ($request->has('type')) {
            $allNotifications = array_filter($allNotifications, fn($n) => $n['type'] == $request->type);
        }

        // Manual pagination
        $perPage = 20;
        $page = $request->get('page', 1);
        $total = count($allNotifications);
        $notifications = array_slice($allNotifications, ($page - 1) * $perPage, $perPage);

        // Add time_ago to each notification
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->notificationService->getTimeAgo($notification['created_at']);
        }

        $unreadCount = $this->notificationService->getUnreadCount();
        $criticalCount = $this->notificationService->getCriticalCount();

        return view('notifications.index', compact('notifications', 'unreadCount', 'criticalCount', 'total', 'page', 'perPage'));
    }

    /**
     * Tandai notifikasi sebagai dibaca
     */
    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Notifikasi ditandai sebagai dibaca');
    }

    /**
     * Tandai semua notifikasi sebagai dibaca
     */
    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsRead();
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'count' => $count]);
        }
        
        return redirect()->back()->with('success', "{$count} notifikasi ditandai sebagai dibaca");
    }

    /**
     * Hapus notifikasi
     */
    public function destroy($id)
    {
        $this->notificationService->deleteNotification($id);
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Notifikasi berhasil dihapus');
    }

    /**
     * Hapus semua notifikasi yang sudah dibaca
     */
    public function clearRead()
    {
        $count = $this->notificationService->clearRead();
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'count' => $count]);
        }
        
        return redirect()->back()->with('success', "{$count} notifikasi berhasil dihapus");
    }

    /**
     * Hapus semua notifikasi
     */
    public function clearAll()
    {
        $this->notificationService->clearAll();
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Semua notifikasi berhasil dihapus');
    }

    /**
     * API: Get unread count
     */
    public function getUnreadCount()
    {
        return response()->json([
            'unread_count' => $this->notificationService->getUnreadCount(),
            'critical_count' => $this->notificationService->getCriticalCount(),
        ]);
    }

    /**
     * API: Get recent notifications
     */
    public function getRecent(Request $request)
    {
        $limit = $request->get('limit', 5);
        $notifications = $this->notificationService->getRecentNotifications($limit);

        // Add time_ago
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->notificationService->getTimeAgo($notification['created_at']);
        }

        return response()->json([
            'notifications' => $notifications,
            'total_unread' => $this->notificationService->getUnreadCount(),
        ]);
    }

    /**
     * Manual check stock (trigger notifikasi)
     */
    public function checkStock()
    {
        $count = $this->notificationService->checkAllProducts();
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'count' => $count]);
        }
        
        return redirect()->back()->with('success', "{$count} notifikasi stok berhasil dibuat");
    }
}