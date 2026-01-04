<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockRecommendation extends Model
{
    protected $fillable = [
        'kategori_produk_id',
        'status',
        'recommended_qty',
        'computed_for_date',
        'note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function kategoriProduk()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id');
    }
}
