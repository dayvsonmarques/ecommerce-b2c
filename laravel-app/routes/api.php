<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Admin\AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\AdminPageController;
use App\Http\Controllers\Api\V1\Admin\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api', 'tenant'])->group(function () {

    // =========================================================================
    // AUTH — público
    // =========================================================================
    Route::prefix('auth')->group(function () {
        Route::post('register',         [AuthController::class, 'register']);
        Route::post('login',            [AuthController::class, 'login']);
        Route::post('password/forgot',  [AuthController::class, 'forgotPassword']);
        Route::post('password/reset',   [AuthController::class, 'resetPassword']);
    });

    // =========================================================================
    // CATÁLOGO — público (SSG/ISR no Next.js)
    // =========================================================================
    Route::get('categories',                       [CategoryController::class, 'index']);
    Route::get('categories/{category}',            [CategoryController::class, 'show']);
    Route::get('products',                         [ProductController::class, 'index']);
    Route::get('products/{product}',               [ProductController::class, 'show']);
    Route::get('products/{product}/related',       [ProductController::class, 'related']);

    // =========================================================================
    // PÁGINAS CMS — público (carregadas no footer/storefront)
    // =========================================================================
    Route::get('pages',         [PageController::class, 'index']);
    Route::get('pages/{slug}',  [PageController::class, 'show']);

    // =========================================================================
    // WEBHOOK (público com validação de assinatura no controller)
    // =========================================================================
    Route::post('webhooks/payment', [PaymentController::class, 'webhook']);

    // =========================================================================
    // ESTIMATIVA DE FRETE — público (apenas CEP, sem autenticação necessária)
    // =========================================================================
    Route::post('shipping/estimate', [CartController::class, 'estimateShipping']);

    // =========================================================================
    // ROTAS PROTEGIDAS — Sanctum
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {

        // Auth — perfil e logout
        Route::prefix('auth')->group(function () {
            Route::get('profile',   [AuthController::class, 'profile']);
            Route::put('profile',   [AuthController::class, 'updateProfile']);
            Route::post('logout',   [AuthController::class, 'logout']);
        });

        // Endereços
        Route::prefix('addresses')->group(function () {
            Route::get('/',          [AddressController::class, 'index']);
            Route::post('/',         [AddressController::class, 'store']);
            Route::put('/{id}',      [AddressController::class, 'update']);
            Route::delete('/{id}',   [AddressController::class, 'destroy']);
        });

        // Carrinho
        Route::prefix('cart')->group(function () {
            Route::get('/',              [CartController::class, 'index']);
            Route::post('items',         [CartController::class, 'store']);
            Route::put('items/{id}',     [CartController::class, 'update']);
            Route::delete('items/{id}',  [CartController::class, 'destroy']);
            Route::post('clear',         [CartController::class, 'clear']);
            Route::post('coupon',        [CartController::class, 'applyCoupon']);
            Route::post('shipping',      [CartController::class, 'estimateShipping']);
        });

        // Pedidos (cliente)
        Route::prefix('orders')->group(function () {
            Route::get('/',              [OrderController::class, 'index']);
            Route::post('/',             [OrderController::class, 'store']);
            Route::get('/{id}',          [OrderController::class, 'show']);
            Route::post('/{id}/cancel',  [OrderController::class, 'cancel']);
            Route::post('/{id}/pay',     [PaymentController::class, 'pay']);
        });

        // =====================================================================
        // ADMIN — requer autenticação + is_admin
        // =====================================================================
        Route::prefix('admin')->middleware('admin')->group(function () {

            // Produtos
            Route::apiResource('products', AdminProductController::class);
            Route::patch('products/{product}/stock', [AdminProductController::class, 'adjustStock']);

            // Pedidos
            Route::get('orders',                        [AdminOrderController::class, 'index']);
            Route::get('orders/{order}',                [AdminOrderController::class, 'show']);
            Route::patch('orders/{order}/status',       [AdminOrderController::class, 'updateStatus']);

            // Usuários
            Route::get('users',                         [AdminUserController::class, 'index']);
            Route::get('users/{user}',                  [AdminUserController::class, 'show']);
            Route::patch('users/{user}/toggle-active',  [AdminUserController::class, 'toggleActive']);

            // Relatórios
            Route::get('reports/summary',               [AdminOrderController::class, 'reportSummary']);

            // CMS — Páginas
            Route::apiResource('pages', AdminPageController::class);
        });
    });
});
