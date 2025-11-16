<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\KategoriProduk;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // ⬅️ penting untuk export PDF

class TransaksiController extends Controller
{
    // 🔹 FORM INPUT TRANSAKSI MASUK
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

    // 🔹 SIMPAN TRANSAKSI
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'nomor_transaksi'    => 'required',
                'jenis_transaksi'    => 'required|in:pemasukan',
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

        // Hitung total harga
        $validated['total_harga'] = $kategori->harga * $validated['jumlah'];

        // Hitung pelunasan
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

    // 🔹 LAPORAN TRANSAKSI (TABEL + SEARCH)
    public function index(Request $request)
    {
        $pageTitle = 'Daftar Transaksi Masuk';
        $search    = $request->q; // ?q=...

        $query = Transaksi::with('kategoriProduk')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                  ->orWhereHas('kategoriProduk', function ($qq) use ($search) {
                      $qq->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        $transaksis = $query->paginate(10)->appends(['q' => $search]);

        // ⬅️ PENTING: ini sekarang pakai view laporan, BUKAN form
        return view('transaksi.index', compact('pageTitle', 'transaksis', 'search'));
    }

    // 🔹 EXPORT PDF (IKUTI FILTER/SEARCH)
    public function exportPdf(Request $request)
    {
        $pageTitle = 'Laporan Transaksi Masuk';
        $search    = $request->q;

        $query = Transaksi::with('kategoriProduk')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                  ->orWhereHas('kategoriProduk', function ($qq) use ($search) {
                      $qq->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        $transaksis = $query->get();

        $pdf = Pdf::loadView('transaksi.laporan_pdf', compact('transaksis', 'pageTitle', 'search'))
                  ->setPaper('a4', 'portrait');

        $filename = 'laporan_transaksi_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
        // atau stream:
        // return $pdf->stream($filename);
    }
}
