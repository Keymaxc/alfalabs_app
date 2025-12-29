<?php

use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PengerjaanTransaksiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {

    Route::prefix('master-data')->name('master-data.')->middleware('role:admin')->group(function () {
        Route::resource('kategori-produk', KategoriProdukController::class);
    });

    Route::middleware('role:admin,staff')->group(function () {
        // Halaman daftar transaksi (tabel)
        Route::get('/transaksi', [TransaksiController::class, 'index'])
            ->name('transaksi.index');

        //  Form transaksi masuk
        Route::get('/transaksi/masuk', [TransaksiController::class, 'create'])
            ->name('transaksi.masuk');

        //  Simpan transaksi
        Route::post('/transaksi', [TransaksiController::class, 'store'])
            ->name('transaksi.store');

        // Form stok masuk
        Route::get('/transaksi/stok-masuk', [TransaksiController::class, 'createStokMasuk'])
            ->name('transaksi.stok-masuk');

        // Simpan stok masuk
        Route::post('/transaksi/stok-masuk', [TransaksiController::class, 'storeStokMasuk'])
            ->name('transaksi.stok-masuk.store');

        // Export PDF (mengikuti filter/search)
        Route::get('/transaksi/export/pdf', [TransaksiController::class, 'exportPdf'])
            ->name('transaksi.export.pdf');
    });

    Route::middleware('role:admin,staff')->group(function () {
        // Halaman Pengerjaan Transaksi
        Route::get('/pengerjaan', [PengerjaanTransaksiController::class, 'indexBerjalan'])
            ->name('pengerjaan.berjalan');

        Route::get('/pengerjaan/selesai', [PengerjaanTransaksiController::class, 'indexSelesai'])
            ->name('pengerjaan.selesai');

        Route::put('/pengerjaan/{pengerjaan}', [PengerjaanTransaksiController::class, 'update'])
            ->name('pengerjaan.update');
    });
});
