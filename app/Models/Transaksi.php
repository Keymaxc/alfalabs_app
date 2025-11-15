<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $fillable = [
        'nomor_transaksi',
        'jenis_transaksi',
        'kategori_produk_id',
        'jumlah',
        'total_harga',
        'keterangan',
        'nama_pelanggan',
        'kontak_pelanggan',
        'deposit',
        'pelunasan',
    ];

    public function kategoriProduk()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id');
    }
}
