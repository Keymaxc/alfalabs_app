<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pakai raw SQL agar tidak tergantung doctrine/dbal
        DB::statement("UPDATE transaksis SET deadline_at = IFNULL(created_at, NOW()) WHERE deadline_at IS NULL");
        DB::statement('ALTER TABLE transaksis MODIFY deadline_at DATETIME NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE transaksis MODIFY deadline_at DATETIME NULL');
    }
};
