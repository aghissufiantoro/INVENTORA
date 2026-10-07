<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanStokController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\HistoryTransaksiController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\DetailPosController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\BarangRusakController;
use App\Http\Controllers\Auth\CustomPasswordResetController;
use App\Http\Controllers\InventoryApprovalController;

//
// AUTH & LOGIN
//
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('password/forgot', [CustomPasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('password/send-otp', [CustomPasswordResetController::class, 'sendOTP'])->name('password.send.otp');
Route::get('password/verify', [CustomPasswordResetController::class, 'showVerifyForm'])->name('password.verify.form');
Route::post('password/verify-otp', [CustomPasswordResetController::class, 'verifyOTP'])->name('password.verify.otp');
Route::get('password/reset', [CustomPasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('password/reset', [CustomPasswordResetController::class, 'resetPassword'])->name('password.update');
Route::post('password/resend-otp', [CustomPasswordResetController::class, 'resendOTP'])->name('password.resend.otp');

//
// DASHBOARD
//
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn() => redirect()->route('dashboard.index'))->name('dashboard');
    Route::get('/dashboard/index', [DashboardController::class, 'index'])->name('dashboard.index');
});

//
// PRODUK & KATEGORI
//
Route::middleware(['auth'])->group(function () {
    Route::resource('product', ProductController::class);
    Route::post('/product/ajax', [ProductController::class, 'storeAjax'])->name('product.store.ajax');

    Route::get('product-reorder-alert', [ProductController::class, 'reorderAlert'])->name('product.reorder-alert');
    Route::post('product/{id}/adjust-stock', [ProductController::class, 'adjustStock'])->name('product.adjust-stock');
    Route::post('product/recalculate-rop', [ProductController::class, 'recalculateAllROP'])->name('product.recalculate-rop');
    Route::get('/product/{id}/auto-recommendation', [ProductController::class, 'getAutoRecommendation'])->name('product.auto-recommendation');
    Route::post('/product/{id}/apply-auto-recommendation', [ProductController::class, 'applyAutoRecommendation'])->name('product.apply-auto-recommendation');

    Route::post('/product/auto-calculate-all', [ProductController::class, 'autoCalculateAll'])->name('product.auto-calculate-all');

    Route::resource('kategori', KategoriController::class);
    Route::post('/kategori/ajax', [KategoriController::class, 'storeAjax'])->name('kategori.store.ajax');
    Route::post('/kategori/store-ajax', [KategoriController::class, 'storeAjax'])->name('kategori.storeAjax');
});

//
// INVENTORY APPROVAL (OWNER ONLY)
//
Route::middleware(['auth', 'role:owner'])->prefix('inventory-approval')->name('inventory-approval.')->group(function () {
    Route::get('/', [InventoryApprovalController::class, 'index'])->name('index');
    Route::post('/{id}/approve', [InventoryApprovalController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [InventoryApprovalController::class, 'reject'])->name('reject');
});

//
// KARYAWAN (OWNER ONLY)
//
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::resource('karyawan', KaryawanController::class);
});

//
// ACCOUNT
//
Route::middleware(['auth'])->group(function () {
    Route::resource('account', AccountController::class);
});

//
// LAPORAN
//
Route::middleware(['auth'])->group(function () {
    Route::prefix('laporan_stok')->name('laporan_stok.')->group(function () {
        Route::get('/', [LaporanStokController::class, 'index'])->name('index');
        Route::get('/kartu-stok', [LaporanStokController::class, 'stockCard'])->name('kartu');
        Route::get('/ringkasan', [LaporanStokController::class, 'summary'])->name('ringkasan');
        Route::get('/export-pdf', [LaporanStokController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export-excel', [LaporanStokController::class, 'exportExcel'])->name('export-excel');
    });

    Route::prefix('laporan_penjualan')->name('laporan_penjualan.')->group(function () {
        Route::get('/penjualan', [LaporanPenjualanController::class, 'index'])->name('penjualan');
        Route::get('/penjualan/pdf', [LaporanPenjualanController::class, 'exportPdf'])->name('penjualan.pdf');
        Route::get('/penjualan/excel', [LaporanPenjualanController::class, 'exportExcel'])->name('penjualan.excel');
    });
});

//
// KALENDER & EVENT
//
Route::middleware(['auth'])->group(function () {
    Route::resource('kalender', EventController::class);
    Route::get('/kalender/notifikasi', [EventController::class, 'getNotifications'])->name('kalender.notifikasi');
});

//
// RETUR BARANG
//
Route::middleware(['auth'])->group(function () {
    Route::get('/retur', [ReturController::class, 'index'])->name('retur.index');
    Route::post('/retur', [ReturController::class, 'store'])->name('retur.store');
    Route::post('/retur/{retur}/approve', [ReturController::class, 'approve'])->name('retur.approve');
    Route::post('/retur/{retur}/reject', [ReturController::class, 'reject'])->name('retur.reject');
});

//
// SALES VISIT TRACKING
//
Route::middleware(['auth'])->prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('index');
    Route::get('/create', [SalesController::class, 'create'])->name('create');
    Route::post('/', [SalesController::class, 'store'])->name('store');
    Route::get('/{sales}/edit', [SalesController::class, 'edit'])->name('edit');
    Route::put('/{sales}', [SalesController::class, 'update'])->name('update');
    Route::delete('/{sales}', [SalesController::class, 'destroy'])->name('destroy');
});

//
// TRANSAKSI & POS
//
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', \App\Livewire\Pos\Cashier::class)->name('pos.index');

    Route::post('/pos/add', [PosController::class, 'addToCart'])->name('pos.add');
    Route::patch('/pos/update/{productId}', [PosController::class, 'updateCart'])->name('pos.update');
    Route::delete('/pos/remove/{productId}', [PosController::class, 'removeFromCart'])->name('pos.remove');
    Route::post('/pos/clear', [PosController::class, 'clearCart'])->name('pos.clear');

    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{id}', [PosController::class, 'receipt'])->name('pos.receipt');

    Route::post('/pos/cancel/{id}', [PosController::class, 'cancelTransaction'])->name('pos.cancel');
    Route::post('/transaksi/masuk', [PosController::class, 'storeMasuk'])->name('transaksi.masuk');

    Route::get('/pos/history', [DetailPosController::class, 'history'])->name('pos.history');
    Route::get('/pos/detail/{id}', [DetailPosController::class, 'show'])->name('pos.detail');
    Route::get('/receipt/{id}', [DetailPosController::class, 'show'])->name('pos.receipt');
    Route::get('/history_transaksi/index', [PosController::class, 'history'])->name('history_transaksi.history');
    Route::get('/history_transaksi', [HistoryTransaksiController::class, 'index'])->name('history_transaksi.index');
    Route::get('/history_transaksi/{id}', [HistoryTransaksiController::class, 'show'])->name('history_transaksi.show');
});

//
// BARANG MASUK
//
Route::middleware(['auth'])->prefix('barang-masuk')->name('barang_masuk.')->group(function () {
    Route::get('/', [BarangMasukController::class, 'index'])->name('index');
    Route::get('/create', [BarangMasukController::class, 'create'])->name('create');
    Route::post('/', [BarangMasukController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [BarangMasukController::class, 'edit'])->name('edit');
    Route::put('/{id}', [BarangMasukController::class, 'update'])->name('update');
    Route::delete('/{id}', [BarangMasukController::class, 'destroy'])->name('destroy');

    Route::post('/{id}/verify', [BarangMasukController::class, 'verify'])->name('verify');
    Route::put('/{id}/update-harga', [BarangMasukController::class, 'updateHarga'])->name('update_harga');
});

//
// NOTIFIKASI
//
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::patch('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/clear-read', [NotificationController::class, 'clearRead'])->name('clear-read');
    Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
    Route::post('/check-stock', [NotificationController::class, 'checkStock'])->name('check-stock');
});

//
// BARANG RUSAK
//
Route::middleware(['auth'])->prefix('barang-rusak')->name('barangrusak.')->group(function () {
    Route::get('/', [BarangRusakController::class, 'index'])->name('index');
    Route::post('/', [BarangRusakController::class, 'store'])->name('store');
    Route::post('/{id}/approve', [BarangRusakController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [BarangRusakController::class, 'reject'])->name('reject');
    Route::delete('/{id}', [BarangRusakController::class, 'destroy'])->name('destroy');
});

//
// PURCHASE ORDER
//
Route::middleware(['auth'])->prefix('purchase-order')->name('purchase-order.')->group(function () {
    Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
    Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
    Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
    Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('show');
    Route::get('/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'downloadPDF'])->name('pdf');
    Route::patch('/{id}/complete', [PurchaseOrderController::class, 'markAsCompleted'])->name('complete');

    Route::get('/{id}/receive', [PurchaseOrderController::class, 'receive'])->name('receive');
    Route::post('/{id}/process-receive', [PurchaseOrderController::class, 'processReceive'])->name('process-receive');

    Route::get('/{purchaseOrder}/download-pdf', [PurchaseOrderController::class, 'downloadPDF'])->name('download-pdf');
});
