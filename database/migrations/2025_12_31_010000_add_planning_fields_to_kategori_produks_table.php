<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_produks', function (Blueprint $table) {
            $table->integer('lead_time_days')->default(3)->after('stok_minimum');
            $table->integer('minimum_order_qty')->default(0)->after('lead_time_days');
        });
    }

    public function down(): void
    {
        Schema::table('kategori_produks', function (Blueprint $table) {
            $table->dropColumn(['lead_time_days', 'minimum_order_qty']);
        });
    }
};
