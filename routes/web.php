<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
// Reservasi Publik (tanpa login)
Route::view('reservasi-online', 'reservasi-publik')->name('reservasi.publik');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Kasir & Admin
    Route::middleware(['role:kasir,admin'])->group(function () {
        Route::view('meja', 'pages.meja')->name('meja.index');
        Route::view('order', 'pages.order')->name('order.index');
        Route::view('pembayaran', 'pages.pembayaran')->name('pembayaran.index');
    });

    // Dapur & Admin
    Route::middleware(['role:dapur,admin'])->group(function () {
        Route::view('kds', 'pages.kds')->name('kds.index');
    });

    // Admin only
    Route::middleware(['role:admin'])->group(function () {
        Route::view('menu', 'pages.menu')->name('menu.index');
        Route::view('promo', 'pages.promo')->name('promo.index');
        Route::view('reservasi', 'pages.reservasi')->name('reservasi.index');
        Route::view('stok', 'pages.stok')->name('stok.index');
        Route::view('laporan', 'pages.laporan')->name('laporan.index');
    });
});

require __DIR__ . '/settings.php';
