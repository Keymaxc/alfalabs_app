<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kategori_produks', function (Blueprint $table) {
            $table->integer('stok')->default(0)->after('harga');
            $table->integer('stok_minimum')->default(0)->after('stok');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_produks', function (Blueprint $table) {
            $table->dropColumn(['stok', 'stok_minimum']);
        });
    }
};
