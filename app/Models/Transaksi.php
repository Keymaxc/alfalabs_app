<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $casts = [
        'deadline_at' => 'datetime',
    ];

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
        'deadline_at',
    ];

    public function kategoriProduk()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id');
    }
    public function kategoriProduk2()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id');
    }
    public function pengerjaan()
    {
        return $this->hasOne(PengerjaanTransaksi::class);
    }
}
