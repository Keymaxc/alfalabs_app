<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_masuks', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi');
            $table->foreignId('kategori_produk_id')->constrained('kategori_produks')->cascadeOnDelete();
            $table->integer('jumlah');
            $table->integer('harga_satuan')->default(0);
            $table->integer('total_harga')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Migrasi data stok yang sebelumnya disimpan sebagai jenis_transaksi = pengeluaran
        DB::table('transaksis')
            ->where('jenis_transaksi', 'pengeluaran')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $hargaSatuan = ($row->jumlah ?? 0) > 0
                        ? (int) floor($row->total_harga / max($row->jumlah, 1))
                        : 0;

                    DB::table('stok_masuks')->insert([
                        'nomor_transaksi'    => $row->nomor_transaksi,
                        'kategori_produk_id' => $row->kategori_produk_id,
                        'jumlah'             => $row->jumlah,
                        'harga_satuan'       => $hargaSatuan,
                        'total_harga'        => $row->total_harga,
                        'keterangan'         => $row->keterangan,
                        'created_at'         => $row->created_at,
                        'updated_at'         => $row->updated_at,
                    ]);

                    DB::table('transaksis')->where('id', $row->id)->delete();
                }
            });
    }

    public function down(): void
    {
        // Kembalikan data stok ke tabel transaksis sebagai pengeluaran sebelum drop tabel
        DB::table('stok_masuks')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('transaksis')->insert([
                        'nomor_transaksi'    => $row->nomor_transaksi,
                        'jenis_transaksi'    => 'pengeluaran',
                        'kategori_produk_id' => $row->kategori_produk_id,
                        'jumlah'             => $row->jumlah,
                        'total_harga'        => $row->total_harga,
                        'keterangan'         => $row->keterangan,
                        'deposit'            => 0,
                        'pelunasan'          => 0,
                        'deadline_at'        => $row->created_at,
                        'created_at'         => $row->created_at,
                        'updated_at'         => $row->updated_at,
                    ]);
                }
            });

        Schema::dropIfExists('stok_masuks');
    }
};
