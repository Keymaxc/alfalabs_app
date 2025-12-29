<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\KategoriProduk;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $pageTitle = 'Dashboard Analitik';

        // Total semua transaksi (pemasukan)
        $totalTransaksi = Transaksi::count();

        // Total pemasukan hari ini
        $totalHariIni = Transaksi::whereDate('created_at', today())
            ->sum('total_harga');

        // Total pemasukan bulan ini
        $totalBulanIni = Transaksi::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_harga');

        // Total pelunasan bulan ini
        $totalPelunasanBulanIni = Transaksi::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('pelunasan');

        // 5 transaksi terakhir
        $latestTransaksi = Transaksi::with('kategoriProduk')
            ->latest()
            ->take(5)
            ->get();

        $stokMenipis = KategoriProduk::whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('stok')
            ->take(5)
            ->get();

        $totalStokMenipis = KategoriProduk::whereColumn('stok', '<=', 'stok_minimum')->count();

        return view('home', compact(
            'pageTitle',
            'totalTransaksi',
            'totalHariIni',
            'totalBulanIni',
            'totalPelunasanBulanIni',
            'latestTransaksi',
            'stokMenipis',
            'totalStokMenipis'
        ));
    }
}
