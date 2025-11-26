<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengerjaanTransaksi extends Model
{
    use HasFactory;

    protected $table = 'pengerjaan_transaksis';

    protected $fillable = [
        'transaksi_id',
        'status',
        'catatan',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
