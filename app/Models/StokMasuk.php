<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMasuk extends Model
{
    protected $fillable = [
        'nomor_transaksi',
        'kategori_produk_id',
        'jumlah',
        'harga_satuan',
        'total_harga',
        'keterangan',
    ];

    public function kategoriProduk()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id');
    }
}
