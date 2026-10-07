<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiBarangMasukController;
use App\Http\Controllers\Api\ApiBarangRusakController;
use App\Http\Controllers\Api\ApiCategoryController;
use App\Http\Controllers\Api\ApiProductController;
use App\Http\Controllers\Api\ApiReturController;
use App\Http\Controllers\Api\ApiStockMovementController;
use App\Http\Controllers\Api\ApiTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::get('/tokens', [ApiAuthController::class, 'tokens']);
    Route::delete('/tokens/{tokenId}', [ApiAuthController::class, 'revokeToken']);

    Route::prefix('v1')->group(function () {
        Route::get('/products/by-barcode/{barcode}', [ApiProductController::class, 'byBarcode']);
        Route::post('/products/{product}/adjust-stock', [ApiProductController::class, 'adjustStock']);
        Route::apiResource('products', ApiProductController::class)->except(['store', 'update', 'destroy']);

        Route::apiResource('categories', ApiCategoryController::class);

        Route::get('/stock-movements/product/{productId}', [ApiStockMovementController::class, 'productHistory']);
        Route::apiResource('stock-movements', ApiStockMovementController::class)->only(['index', 'show']);

        Route::post('/transactions', [ApiTransactionController::class, 'store']);
        Route::get('/transactions/today-summary', [ApiTransactionController::class, 'todaySummary']);
        Route::apiResource('transactions', ApiTransactionController::class)->only(['index', 'show']);

        Route::post('/barang-masuk/{barangMasuk}/verify', [ApiBarangMasukController::class, 'verify']);
        Route::post('/barang-masuk/{barangMasuk}/update-harga', [ApiBarangMasukController::class, 'updateHarga']);
        Route::apiResource('barang-masuk', ApiBarangMasukController::class);

        Route::post('/barang-rusak/{barangRusak}/approve', [ApiBarangRusakController::class, 'approve']);
        Route::post('/barang-rusak/{barangRusak}/reject', [ApiBarangRusakController::class, 'reject']);
        Route::apiResource('barang-rusak', ApiBarangRusakController::class)->except(['update']);

        Route::post('/retur/{retur}/approve', [ApiReturController::class, 'approve']);
        Route::post('/retur/{retur}/reject', [ApiReturController::class, 'reject']);
        Route::apiResource('retur', ApiReturController::class)->only(['index', 'show', 'store']);
    });
});
