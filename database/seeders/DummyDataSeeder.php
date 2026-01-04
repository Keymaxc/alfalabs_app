<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\PengerjaanTransaksi;
use App\Models\Transaksi;
use App\Models\StokMasuk;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nama_kategori'     => 'Kain Katun Premium',
                'harga'             => 45000,
                'stok'              => 220,
                'stok_minimum'      => 80,
                'lead_time_days'    => 5,
                'minimum_order_qty' => 40,
            ],
            [
                'nama_kategori'     => 'Kain Linen Jepang',
                'harga'             => 68000,
                'stok'              => 140,
                'stok_minimum'      => 60,
                'lead_time_days'    => 7,
                'minimum_order_qty' => 30,
            ],
            [
                'nama_kategori'     => 'Kain Drill Premium',
                'harga'             => 52000,
                'stok'              => 180,
                'stok_minimum'      => 70,
                'lead_time_days'    => 4,
                'minimum_order_qty' => 25,
            ],
        ];

        $kategoriMap = [];
        foreach ($categories as $cat) {
            $kategori = KategoriProduk::updateOrCreate(
                ['nama_kategori' => $cat['nama_kategori']],
                $cat
            );

            $kategoriMap[$kategori->id] = $kategori;
        }

        // Bersihkan data dummy sebelumnya agar tidak dobel
        $kategoriIds = array_keys($kategoriMap);
        $dummyTransaksiIds = Transaksi::whereIn('kategori_produk_id', $kategoriIds)
            ->where('keterangan', 'like', 'Penjualan dummy%')
            ->pluck('id');

        PengerjaanTransaksi::whereIn('transaksi_id', $dummyTransaksiIds)->delete();
        Transaksi::whereIn('kategori_produk_id', $kategoriIds)
            ->where('keterangan', 'like', 'Penjualan dummy%')
            ->delete();
        StokMasuk::whereIn('kategori_produk_id', $kategoriIds)
            ->where('keterangan', 'like', 'Restock dummy%')
            ->delete();

        $start          = now()->startOfDay()->subDays(179); // 6 bulan ke belakang
        $days           = 180;
        $counter        = (int) (Transaksi::max('id') ?? 0) + 1;
        $restockCounter = (int) (StokMasuk::max('id') ?? 0) + 1;

        $transaksiRows = [];
        $stokMasukRows = [];

        foreach ($kategoriMap as $kategori) {
            $stokCurrent = max(0, (int) $kategori->stok);

            $base = match ($kategori->nama_kategori) {
                'Kain Katun Premium' => 16,
                'Kain Linen Jepang'  => 11,
                default              => 9,
            };

            $stdGuess = max(2, (int) round($base * 0.25));
            $lead     = max(1, (int) ($kategori->lead_time_days ?? 3));
            $rop      = (int) ceil(($base * $lead) + 1.65 * $stdGuess * sqrt($lead));

            for ($i = 0; $i < $days; $i++) {
                $date = $start->copy()->addDays($i)->setTime(10, 0);

                // Restock jika stok rendah
                if ($stokCurrent <= ($rop + ($kategori->stok_minimum ?? 0))) {
                    $restockQty = max(
                        (int) ($kategori->minimum_order_qty ?? 0),
                        $rop + ($kategori->stok_minimum ?? 0) + (int) round($base * 7) - $stokCurrent
                    );

                    if ($restockQty < $base * 2) {
                        $restockQty = $base * 2;
                    }

                    $hargaSatuan = max((int) round($kategori->harga * 0.6), 10000);
                    $totalHarga  = $hargaSatuan * $restockQty;

                    $stokMasukRows[] = [
                        'nomor_transaksi'    => 'STK-' . $date->format('Ymd') . '-' . str_pad((string) $restockCounter++, 4, '0', STR_PAD_LEFT),
                        'kategori_produk_id' => $kategori->id,
                        'jumlah'             => $restockQty,
                        'harga_satuan'       => $hargaSatuan,
                        'total_harga'        => $totalHarga,
                        'keterangan'         => 'Restock dummy ' . $kategori->nama_kategori,
                        'created_at'         => $date,
                        'updated_at'         => $date,
                    ];

                    $stokCurrent += $restockQty;
                }

                $seasonalFactor = 1 + 0.3 * sin(2 * M_PI * ($i % 7) / 7); // pola mingguan
                $trendFactor    = 1 + ($i * 0.0015); // sedikit naik tiap hari
                $noise          = random_int(-3, 4);

                $demand = (int) round(max(1, ($base + $noise) * $seasonalFactor * $trendFactor));
                $sales  = min($demand, max($stokCurrent, 0));

                $stokCurrent -= $sales;

                $totalHarga = $sales * $kategori->harga;

                $transaksiRows[] = [
                    'nomor_transaksi'    => 'TRX-' . $date->format('Ymd') . '-' . str_pad((string) $counter++, 4, '0', STR_PAD_LEFT),
                    'jenis_transaksi'    => 'pemasukan',
                    'kategori_produk_id' => $kategori->id,
                    'jumlah'             => $sales,
                    'total_harga'        => $totalHarga,
                    'keterangan'         => 'Penjualan dummy ' . $kategori->nama_kategori,
                    'nama_pelanggan'     => 'Pelanggan Dummy',
                    'kontak_pelanggan'   => '0812-0000-0000',
                    'deposit'            => 0,
                    'pelunasan'          => $totalHarga,
                    'deadline_at'        => $date->copy()->addDays(2),
                    'created_at'         => $date,
                    'updated_at'         => $date,
                ];
            }

            $kategori->update(['stok' => max(0, $stokCurrent)]);
        }

        if (! empty($stokMasukRows)) {
            StokMasuk::insert($stokMasukRows);
        }

        if (! empty($transaksiRows)) {
            Transaksi::insert($transaksiRows);
        }

        // Buat data pengerjaan agar tampil di Order Berjalan/Selesai
        $insertedTransaksis = Transaksi::whereIn('kategori_produk_id', $kategoriIds)
            ->where('keterangan', 'like', 'Penjualan dummy%')
            ->whereDate('created_at', '>=', $start->toDateString())
            ->orderBy('created_at')
            ->get();

        $pengerjaanRows = [];
        $today = now();

        foreach ($insertedTransaksis as $trx) {
            $ageDays = $trx->created_at ? $trx->created_at->diffInDays($today) : 0;

            if ($ageDays >= 30) {
                $status = 'diambil';
                $note   = 'Barang sudah diambil pelanggan.';
            } elseif ($ageDays >= 10) {
                $status = 'selesai';
                $note   = 'Pengerjaan selesai, siap diambil.';
            } elseif ($ageDays >= 3) {
                $status = 'proses';
                $note   = 'Sedang diproses.';
            } else {
                $status = 'menunggu';
                $note   = 'Menunggu diproses.';
            }

            $pengerjaanRows[] = [
                'transaksi_id' => $trx->id,
                'status'       => $status,
                'catatan'      => $note,
                'created_at'   => $trx->created_at,
                'updated_at'   => $trx->updated_at,
            ];
        }

        if (! empty($pengerjaanRows)) {
            PengerjaanTransaksi::insert($pengerjaanRows);
        }
    }
}
