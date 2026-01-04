<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanKeuanganController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    public function pdf(Request $request)
    {
        [$start, $end, $monthParam, $monthLabel] = $this->resolvePeriod($request->query('month'));

        $totalPemasukan = Transaksi::where('jenis_transaksi', 'pemasukan')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_harga');

        $totalPengeluaran = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_harga');

        $labaRugi = $totalPemasukan - $totalPengeluaran;

        [$weeklyIncome, $weeklyExpense] = $this->weeklyBreakdown($start, $end);

        $pdf = Pdf::loadView('keuangan.laporan_pdf', [
            'monthLabel'        => $monthLabel,
            'monthParam'        => $monthParam,
            'totalPemasukan'    => $totalPemasukan,
            'totalPengeluaran'  => $totalPengeluaran,
            'labaRugi'          => $labaRugi,
            'weeklyIncome'      => $weeklyIncome,
            'weeklyExpense'     => $weeklyExpense,
        ])->setPaper('a4', 'portrait');

        $filename = 'laporan_keuangan_' . $start->format('Y_m') . '.pdf';

        return $pdf->download($filename);
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
