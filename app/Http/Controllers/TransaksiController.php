<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\KategoriProduk;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function create()
    {
        $pageTitle       = 'Transaksi Masuk';
        $kategoriProduks = KategoriProduk::all();
        $nomorTransaksi  = 'TRX-' . date('YmdHis');

        return view('transaksi.form_transaksi_masuk', compact(
            'pageTitle',
            'kategoriProduks',
            'nomorTransaksi'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'nomor_transaksi'    => 'required',
                'jenis_transaksi'    => 'required|in:pemasukan', // ⬅️ sekarang cuma boleh 'pemasukan'
                'kategori_produk_id' => 'required|exists:kategori_produks,id',
                'jumlah'             => 'required|integer|min:1',
                'nama_pelanggan'     => 'required|string',
                'kontak_pelanggan'   => 'required|string',
                'deposit'            => 'required|integer|min:0',
                'keterangan'         => 'nullable|string',
            ],
            [
                'nomor_transaksi.required'    => 'Nomor transaksi wajib diisi.',
                'jenis_transaksi.required'    => 'Jenis transaksi wajib diisi.',
                'jenis_transaksi.in'          => 'Jenis transaksi tidak valid.',
                'kategori_produk_id.required' => 'Kategori barang wajib dipilih.',
                'kategori_produk_id.exists'   => 'Kategori barang tidak ditemukan.',
                'jumlah.required'             => 'Jumlah barang wajib diisi.',
                'jumlah.integer'              => 'Jumlah barang harus berupa angka.',
                'jumlah.min'                  => 'Jumlah minimal 1.',
                'nama_pelanggan.required'     => 'Nama pelanggan wajib diisi.',
                'kontak_pelanggan.required'   => 'Kontak pelanggan wajib diisi.',
                'deposit.required'            => 'Deposit wajib diisi (isi 0 jika tidak ada).',
                'deposit.integer'             => 'Deposit harus berupa angka.',
                'deposit.min'                 => 'Deposit tidak boleh negatif.',
            ]
        );

        $kategori = KategoriProduk::findOrFail($validated['kategori_produk_id']);

        $validated['total_harga'] = $kategori->harga * $validated['jumlah'];

        $deposit   = $validated['deposit'] ?? 0;
        $pelunasan = $validated['total_harga'] - $deposit;
        if ($pelunasan < 0) {
            $pelunasan = 0;
        }
        $validated['pelunasan'] = $pelunasan;

        Transaksi::create($validated);

        return redirect()
            ->route('transaksi.masuk')
            ->with('success', 'Transaksi berhasil disimpan!');
    }
    public function index()
    {
        $pageTitle  = 'Daftar Transaksi Masuk';
        $transaksis = Transaksi::with('kategoriProduk')->latest()->paginate(10);

        return view('transaksi.index', compact('pageTitle', 'transaksis'));
    }
}
