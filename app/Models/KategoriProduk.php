<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriProduk extends Model
{
    use HasFactory;

    protected $table = 'kategori_produks';

    protected $fillable = [
        'nama_kategori',
        'harga',
        'stok',
        'stok_minimum',
        'lead_time_days',
        'minimum_order_qty',
    ];

    protected $attributes = [
        'stok_minimum' => 50,
    ];
}
