<?php
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Публичные маршруты
Route::prefix('v1')->group(function () {
    // Категории
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    
    // Продукты
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('products/featured/list', [ProductController::class, 'featured']);
});

// Защищенные маршруты (требуют аутентификации)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Корзина
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::put('cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('cart/{cartItem}', [CartController::class, 'destroy']);
    Route::delete('cart', [CartController::class, 'clear']);
    Route::get('cart/count', [CartController::class, 'count']);
    
    // Заказы
    Route::apiResource('orders', OrderController::class);
    
    // Административные маршруты (только для админов)
    Route::middleware('admin')->group(function () {
        Route::apiResource('admin/categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('admin/products', ProductController::class)->except(['index', 'show']);
    });
});