<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SadadController;
use App\Http\Controllers\Api\TLyncController;
use App\Http\Controllers\Api\AdfaliController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LocalBankCardsController;
use App\Http\Controllers\Api\RetalsController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ShipmentMethodController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VariationController;
use App\Http\Controllers\Api\WalletController;

Route::get('/user', [UserController::class, 'whoAmI'])->middleware('auth:sanctum');


//auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/otp', [AuthController::class, 'otp']);

Route::post('/forget-password', [PasswordController::class, 'sendResetLinkEmail']);


Route::apiResources([
    'retals' => RetalsController::class,
    'products' => ProductController::class,
    'product-categories' => ProductCategoryController::class,
    'shipment-methods' => ShipmentMethodController::class
]);

Route::apiResource('products', ProductController::class)->middleware('auth:sanctum');
Route::apiResource('orders', OrderController::class)->middleware(['auth:sanctum', 'check-verified-phone','check-user-has-location']);
Route::apiResource('stores', StoreController::class)->middleware('auth:sanctum');
Route::apiResource('variations', VariationController::class)->middleware('auth:sanctum');
Route::apiResource('transactions', TransactionController::class)->middleware('auth:sanctum');

Route::post('/users/update', [UserController::class, 'update'])->middleware('auth:sanctum');


//cart
Route::post('/add-to-cart', [CartController::class, 'addToCart'])->middleware('auth:sanctum');
Route::post('/remove-from-cart', [CartController::class, 'removeFromCart'])->middleware('auth:sanctum');
Route::post('/update-product-in-cart', [CartController::class, 'updateProductQuantity'])->middleware('auth:sanctum');
Route::get('/view-cart', [CartController::class, 'viewCart'])->middleware('auth:sanctum');


//sadad
Route::post('sadad', [SadadController::class, 'sadad'])->middleware('auth:sanctum');
Route::post('sadad/confirm', [SadadController::class, 'confirmPayment'])->middleware('auth:sanctum');

//adfali
Route::post('adfali', [AdfaliController::class, 'adfali'])->middleware('auth:sanctum');
Route::post('adfali/confirm', [AdfaliController::class, 'confirmPayment'])->middleware('auth:sanctum');

Route::post('/payment/initiate', [LocalBankCardsController::class, 'initiatePayment'])->name('payment.initiate')->middleware('auth:sanctum');


Route::post('/tlync/initiate2', [TLyncController::class, 'initiatePayment'])->name('payment2.initiate2');
Route::get('/tlync/callback2', [TLyncController::class, 'handleCallback'])->name('payment2.callback2');

Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->name('verification.verify');

//wallet
Route::get('/wallet', [WalletController::class, 'index'])->middleware('auth:sanctum');
Route::post('/products/{productId}/variation-options', [ProductController::class, 'addVariationOptionsToProduct']);

Route::post('/products/{productId}/favourite', [ProductController::class, 'addProductToFavourite'])->middleware('auth:sanctum');
Route::get('/user/favourite-products', [UserController::class, 'getFavouriteProducts'])->middleware('auth:sanctum');
Route::get('/user/favourite-stores', [UserController::class, 'getFavouriteStores'])->middleware('auth:sanctum');
Route::post('/products/rate', [ProductController::class, 'rateProduct'])->middleware('auth:sanctum');
Route::post('/stores/rate', [StoreController::class, 'rateStore'])->middleware('auth:sanctum');
Route::post('/stores/location/add', [StoreController::class, 'addStoreLocation'])->middleware('auth:sanctum');

Route::post('/products/store/add-favourite', [StoreController::class, 'addStoreToFavourite'])->middleware('auth:sanctum');


Route::post('/comments', [CommentController::class, 'addComment'])->middleware('auth:sanctum');
Route::get('/comments/store/{storeId}', [CommentController::class, 'getStoreComments'])->middleware('auth:sanctum');

Route::post('/users/location/add', [UserController::class, 'addLocation'])->middleware('auth:sanctum');

Route::post('/user/phones', [UserController::class, 'storePhone'])->middleware('auth:sanctum');

Route::patch('/user/update-default-location', [UserController::class, 'updateDefaultLocation'])
    ->middleware('auth:sanctum');

Route::patch('/user/update-default-phone', [UserController::class, 'updateDefaultPhone'])
    ->middleware('auth:sanctum');

Route::get('/stores/{store}/products', [StoreController::class, 'getStoreProducts'])->middleware('auth:sanctum');
Route::get('/stores/{store}/categories', [StoreController::class, 'getStoreCategories'])->middleware('auth:sanctum');

Route::post('/wallet/transfer', [WalletController::class, 'transferBalance'])->middleware('auth:sanctum');
Route::get('/most-ordered-products', [ProductController::class, 'mostOrderedProducts'])->name('mostOrderedProducts');


