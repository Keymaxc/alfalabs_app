<?php

use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {

    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::resource('kategori-produk', KategoriProdukController::class);
    });

    // Halaman daftar transaksi (tabel)
    Route::get('/transaksi', [TransaksiController::class, 'index'])
        ->name('transaksi.index');

    //  Form transaksi masuk
    Route::get('/transaksi/masuk', [TransaksiController::class, 'create'])
        ->name('transaksi.masuk');

    // Simpan transaksi
    Route::post('/transaksi', [TransaksiController::class, 'store'])
        ->name('transaksi.store');
});
Route::prefix('master-data')->name('master-data.')->middleware('auth')->group(function () {
    Route::prefix('kategori-produk')->name('kategori-produk.')->group(function () {
        Route::get('/', [KategoriProdukController::class, 'index'])
            ->name('index');

        Route::post('/', [KategoriProdukController::class, 'store'])
            ->name('store');

        Route::put('/{id}', [KategoriProdukController::class, 'update'])
            ->name('update');

        Route::delete('/{id}', [KategoriProdukController::class, 'destroy'])
            ->name('destroy');
    });
});
