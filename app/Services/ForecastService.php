<?php

namespace App\Services;

use App\Models\Forecast;
use App\Models\KategoriProduk;
use App\Models\StockRecommendation;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class ForecastService
{
    /**
    * Jalankan perhitungan forecast & rekomendasi stok.
    *
    * @return array{0: \Illuminate\Support\Collection, 1: \Carbon\Carbon}
    */
    public function run(): array
    {
        $today          = now()->startOfDay();
        $horizonDays    = 7;
        $historyDays    = 60;
        $start          = $today->copy()->subDays($historyDays - 1);
        $zScore         = 1.65; // service level ~95%

        $history = Transaksi::selectRaw('kategori_produk_id, DATE(created_at) as tanggal, SUM(jumlah) as qty')
            ->where('jenis_transaksi', 'pemasukan')
            ->whereDate('created_at', '>=', $start)
            ->groupBy('kategori_produk_id', 'tanggal')
            ->get()
            ->groupBy('kategori_produk_id');

        DB::transaction(function () use ($history, $start, $historyDays, $horizonDays, $zScore, $today) {
            foreach (KategoriProduk::all() as $kategori) {
                $dailyHistory = ($history[$kategori->id] ?? collect())->keyBy('tanggal');
                $series = [];
                for ($i = 0; $i < $historyDays; $i++) {
                    $dateKey = $start->copy()->addDays($i)->toDateString();
                    $series[] = (int) ($dailyHistory[$dateKey]->qty ?? 0);
                }

                $avgDaily   = $this->average($series);
                $stdDaily   = $this->stdDev($series, $avgDaily);
                $leadTime   = max(1, (int) ($kategori->lead_time_days ?? 3));
                $predDaily  = (int) round($avgDaily);
                $pred7      = max(0, $predDaily * $horizonDays);
                $safety     = (int) ceil($zScore * $stdDaily * sqrt($leadTime));
                $rop        = (int) ceil(($predDaily * $leadTime) + $safety);
                $stok       = (int) ($kategori->stok ?? 0);
                $minOrder   = max(0, (int) ($kategori->minimum_order_qty ?? 0));
                $stokMin    = max(0, (int) ($kategori->stok_minimum ?? 0));

                $status         = 'aman';
                $recommendedQty = 0;
                $note           = 'Stok aman';

                if ($stok <= $rop) {
                    $status = 'perlu_beli';
                    $target = max($rop + $stokMin, $rop);
                    $recommendedQty = max($target - $stok, $minOrder);
                    $note = 'Stok di bawah ROP';
                } elseif ($predDaily > 0 && $stok > $predDaily * 14) {
                    $status = 'overstock';
                    $note   = 'Stok jauh di atas kebutuhan 2 minggu';
                }

                // Simpan forecast horizon pendek (7 hari)
                Forecast::updateOrCreate(
                    [
                        'kategori_produk_id' => $kategori->id,
                        'predicted_date'     => $today->copy()->addDays($horizonDays)->toDateString(),
                    ],
                    [
                        'predicted_qty' => $pred7,
                        'model_version' => 'v3-ma-60d',
                    ]
                );

                StockRecommendation::updateOrCreate(
                    [
                        'kategori_produk_id' => $kategori->id,
                        'computed_for_date'  => $today->toDateString(),
                    ],
                    [
                        'status'           => $status,
                        'recommended_qty'  => max(0, (int) $recommendedQty),
                        'note'             => $note,
                        'meta'             => [
                            'avg_daily'   => $avgDaily,
                            'std_daily'   => $stdDaily,
                            'safety_stock'=> $safety,
                            'rop'         => $rop,
                            'stok'        => $stok,
                            'lead_time'   => $leadTime,
                            'pred_7d'     => $pred7,
                            'model'          => 'moving_average',
                        ],
                    ]
                );
            }
        });

        $recommendations = StockRecommendation::with('kategoriProduk')
            ->whereDate('computed_for_date', $today)
            ->orderByRaw("FIELD(status, 'perlu_beli', 'aman', 'overstock')")
            ->orderBy('kategori_produk_id')
            ->get();

        return [$recommendations, $today];
    }

    private function average(array $series): float
    {
        $count = max(count($series), 1);
        return array_sum($series) / $count;
    }

    private function stdDev(array $series, float $mean): float
    {
        if (empty($series)) {
            return 0.0;
        }

        $variance = 0.0;
        foreach ($series as $value) {
            $variance += pow($value - $mean, 2);
        }

        return sqrt($variance / max(count($series), 1));
    }
}
