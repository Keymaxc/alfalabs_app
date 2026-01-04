<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\KategoriProduk;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $pageTitle = 'Dashboard';

        [$startOfMonth, $endOfMonth, $selectedMonth, $selectedMonthLabel] = $this->resolvePeriod(
            $request->query('month')
        );

        // Total semua transaksi pemasukan
        $totalTransaksi = Transaksi::where('jenis_transaksi', 'pemasukan')->count();

        // Total pemasukan hari ini
        $totalHariIni = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->whereDate('created_at', today())
            ->sum('total_harga');

        // Total pemasukan & transaksi bulan ini (pemasukan saja)
        $totalBulanIni = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_harga');

        $totalTransaksiBulanIni = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Total pelunasan bulan ini
        $totalPelunasanBulanIni = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('pelunasan');

        // Total pengeluaran (kerugian) bulan ini
        $totalPengeluaranBulanIni = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_harga');

        $labaRugiBulanIni = $totalBulanIni - $totalPengeluaranBulanIni;

        [$weeklyIncome, $weeklyExpense] = $this->weeklyBreakdown($startOfMonth, $endOfMonth);

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
            'totalPengeluaranBulanIni',
            'labaRugiBulanIni',
            'selectedMonth',
            'selectedMonthLabel',
            'weeklyIncome',
            'weeklyExpense',
            'latestTransaksi',
            'stokMenipis',
            'totalStokMenipis',
            'deadlineAlerts'
        ));
    }

    private function resolvePeriod(?string $monthParam): array
    {
        try {
            $period = Carbon::createFromFormat('Y-m', $monthParam ?? now()->format('Y-m'))->startOfMonth();
        } catch (\Throwable) {
            $period = now()->startOfMonth();
        }

        $start = $period->copy()->startOfMonth();
        $end   = $period->copy()->endOfMonth();

        return [$start, $end, $period->format('Y-m'), $period->isoFormat('MMMM Y')];
    }

    private function weeklyBreakdown(Carbon $start, Carbon $end): array
    {
        $weeksIncome  = array_fill(1, 5, 0);
        $weeksExpense = array_fill(1, 5, 0);

        $rows = Transaksi::selectRaw('DATE(created_at) as tanggal, jenis_transaksi, SUM(total_harga) as total')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('jenis_transaksi', ['pemasukan', 'pengeluaran'])
            ->groupBy('tanggal', 'jenis_transaksi')
            ->get();

        foreach ($rows as $row) {
            $date = Carbon::parse($row->tanggal);
            $weekIndex = min(intdiv($date->day - 1, 7) + 1, 5);

            if ($row->jenis_transaksi === 'pemasukan') {
                $weeksIncome[$weekIndex] += $row->total;
            } else {
                $weeksExpense[$weekIndex] += $row->total;
            }
        }

        return [$weeksIncome, $weeksExpense];
    }
}
