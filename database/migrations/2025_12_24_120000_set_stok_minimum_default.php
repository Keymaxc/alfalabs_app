<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set semua stok_minimum menjadi 50
        DB::table('kategori_produks')->update(['stok_minimum' => 50]);
    }

    public function down(): void
    {
        // Kembalikan ke 0 jika di-rollback
        DB::table('kategori_produks')->update(['stok_minimum' => 0]);
    }
};
