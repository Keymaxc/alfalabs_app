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
        $pageTitle = 'Dashboard';

        // Total semua transaksi pemasukan
        $totalTransaksi = Transaksi::where('jenis_transaksi', 'pemasukan')->count();

        // Total pemasukan hari ini
        $totalHariIni = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->whereDate('created_at', today())
            ->sum('total_harga');

        // Total pemasukan & transaksi bulan ini (pemasukan saja)
        $totalBulanIni = Transaksi::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('jenis_transaksi', 'pemasukan')
            ->sum('total_harga');

        $totalTransaksiBulanIni = Transaksi::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('jenis_transaksi', 'pemasukan')
            ->count();

        // Total pelunasan bulan ini
        $totalPelunasanBulanIni = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->whereYear('created_at', now()->year)
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

        $deadlineAlerts = Transaksi::with('kategoriProduk')
            ->where('jenis_transaksi', 'pemasukan')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<=', now()->addDays(2))
            ->orderBy('deadline_at')
            ->take(5)
            ->get();

        return view('home', compact(
            'pageTitle',
            'totalTransaksi',
            'totalHariIni',
            'totalBulanIni',
            'totalTransaksiBulanIni',
            'totalPelunasanBulanIni',
            'latestTransaksi',
            'stokMenipis',
            'totalStokMenipis',
            'deadlineAlerts'
        ));
    }
}
