<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocalBankCardsController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/payment/success', function () {
    return view('payment.success');
})->name('payment.success');

Route::get('/payment/failure', function () {
    return view('payment.failure');
})->name('payment.failure');

Route::get('/payment/callback', [LocalBankCardsController::class, 'handleCallback'])->name('payment.callback')->middleware('auth:sanctum');
