<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\KategoriProduk;
use App\Models\StokMasuk;
use App\Models\Forecast;
use App\Models\StockRecommendation;
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

        [$selectedDate, $selectedDateLabel, $monthFromDate] = $this->resolveDate($request->query('date'));
        $today = now()->startOfDay();

        $monthParam = $request->query('month') ?? $monthFromDate;
        [$startOfMonth, $endOfMonth, $selectedMonth, $selectedMonthLabel] = $this->resolvePeriod($monthParam);

        // Total semua transaksi pemasukan
        $totalTransaksi = Transaksi::where('jenis_transaksi', 'pemasukan')->count();

        // Total pemasukan per tanggal (default: hari ini)
        $totalTanggal = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->whereDate('created_at', $selectedDate)
            ->sum('total_harga');
        $totalTransaksiTanggal = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->whereDate('created_at', $selectedDate)
            ->count();

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
        $totalPengeluaranBulanIni = StokMasuk::whereBetween('created_at', [$startOfMonth, $endOfMonth])
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
            ->whereBetween('deadline_at', [now()->startOfDay(), now()->addDays(7)])
            ->orderBy('deadline_at')
            ->take(5)
            ->get();

        $transaksiPerTanggal = Transaksi::selectRaw('DATE(created_at) as tanggal, COUNT(*) as total_transaksi, SUM(total_harga) as total_pemasukan')
            ->where('jenis_transaksi', 'pemasukan')
            ->groupBy('tanggal')
            ->orderByDesc('tanggal')
            ->limit(14)
            ->get();

        $transaksiPerBulan = Transaksi::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total_transaksi, SUM(total_harga) as total_pemasukan")
            ->where('jenis_transaksi', 'pemasukan')
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->limit(12)
            ->get();

        return view('home', compact(
            'pageTitle',
            'totalTransaksi',
            'totalTanggal',
            'totalBulanIni',
            'totalTransaksiBulanIni',
            'totalPelunasanBulanIni',
            'totalPengeluaranBulanIni',
            'labaRugiBulanIni',
            'selectedMonth',
            'selectedMonthLabel',
            'selectedDate',
            'selectedDateLabel',
            'totalTransaksiTanggal',
            'weeklyIncome',
            'weeklyExpense',
            'latestTransaksi',
            'stokMenipis',
            'totalStokMenipis',
            'deadlineAlerts',
            'transaksiPerTanggal',
            'transaksiPerBulan'
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

    private function resolveDate(?string $dateParam): array
    {
        try {
            $date = Carbon::parse($dateParam)->startOfDay();
        } catch (\Throwable) {
            $date = now()->startOfDay();
        }

        return [$date, $date->format('d/m/Y'), $date->format('Y-m')];
    }

    private function weeklyBreakdown(Carbon $start, Carbon $end): array
    {
        $weeksIncome  = array_fill(1, 5, 0);
        $weeksExpense = array_fill(1, 5, 0);

        $incomeRows = Transaksi::selectRaw('DATE(created_at) as tanggal, SUM(total_harga) as total')
            ->whereBetween('created_at', [$start, $end])
            ->where('jenis_transaksi', 'pemasukan')
            ->groupBy('tanggal')
            ->get();

        $expenseRows = StokMasuk::selectRaw('DATE(created_at) as tanggal, SUM(total_harga) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('tanggal')
            ->get();

        foreach ($incomeRows as $row) {
            $date = Carbon::parse($row->tanggal);
            $weekIndex = min(intdiv($date->day - 1, 7) + 1, 5);
            $weeksIncome[$weekIndex] += $row->total;
        }

        foreach ($expenseRows as $row) {
            $date = Carbon::parse($row->tanggal);
            $weekIndex = min(intdiv($date->day - 1, 7) + 1, 5);
            $weeksExpense[$weekIndex] += $row->total;
        }

        return [$weeksIncome, $weeksExpense];
    }
}
