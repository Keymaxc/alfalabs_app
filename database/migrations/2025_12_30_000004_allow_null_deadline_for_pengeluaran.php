<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Izinkan deadline_at bernilai null untuk transaksi pengeluaran/stok.
        DB::statement('ALTER TABLE transaksis MODIFY deadline_at DATETIME NULL');
        DB::statement("UPDATE transaksis SET deadline_at = NULL WHERE jenis_transaksi = 'pengeluaran'");
    }

    public function down(): void
    {
        // Kembalikan menjadi NOT NULL, isi nilai kosong dengan created_at agar tidak gagal.
        DB::statement("UPDATE transaksis SET deadline_at = IFNULL(deadline_at, created_at)");
        DB::statement('ALTER TABLE transaksis MODIFY deadline_at DATETIME NOT NULL');
    }
};
