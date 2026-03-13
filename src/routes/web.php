<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LikeController;    // 追加
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 1. 未ログイン
Route::middleware(['check.profile'])->group(function ()
{
    Route::get('/', [ItemController::class, 'index'])->name('index');
    Route::get('/search', [ItemController::class, 'search'])->name('item.search');
    Route::get('/item/{item_id}', [ItemController::class, 'detail']);
});
Route::get('/mail', [ItemController::class, 'mail']);


// 2. ログイン必須
Route::middleware(['auth', 'verified'])->group(function ()
{
    Route::middleware(['check.profile'])->group(function () {
        Route::get('/mypage', [ItemController::class, 'profile']);

        Route::get('/sell', [ItemController::class, 'exhibition']);
        Route::post('/sell', [ItemController::class, 'store']);

        Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');
        Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase'])->name('purchase.store');
        Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'success'])->name('purchase.success');
        Route::get('/purchase/cancel/{item_id}', [PurchaseController::class, 'cancel'])->name('purchase.cancel');

        Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('address.edit');
        Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('address.update');

        // いいね機能
        Route::post('/item/{item_id}/like', [LikeController::class, 'store'])->name('like');

        // コメント投稿
        Route::post('/item/comment/{item_id}', [CommentController::class, 'store'])->name('comment');
    });

    Route::get('/mypage/profile', [UserController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile', [UserController::class, 'update'])->name('profile.update');
});

// stripe
Route::post('/api/webhooks/stripe', [PurchaseController::class, 'handle']);
