<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\KategoriProduk;
use App\Models\PengerjaanTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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

    // 🔹 FORM INPUT STOK MASUK
    public function createStokMasuk()
    {
        $pageTitle       = 'Transaksi Stok Masuk';
        $kategoriProduks = KategoriProduk::all();
        $nomorTransaksi  = 'STK-' . date('YmdHis');

        return view('transaksi.form_transaksi_stok_masuk', compact(
            'pageTitle',
            'kategoriProduks',
            'nomorTransaksi'
        ));
    }

    // 🔹 SIMPAN TRANSAKSI + AUTO BUAT PENGERJAAN
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

        if ($kategori->stok < $validated['jumlah']) {
            return back()
                ->withErrors(['jumlah' => 'Stok tidak mencukupi untuk transaksi ini.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $kategori) {
            $validated['total_harga'] = $kategori->harga * $validated['jumlah'];

            $deposit   = $validated['deposit'] ?? 0;
            $pelunasan = $validated['total_harga'] - $deposit;
            if ($pelunasan < 0) {
                $pelunasan = 0;
            }
            $validated['pelunasan'] = $pelunasan;

            $transaksi = Transaksi::create($validated);

            PengerjaanTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'status'       => 'menunggu',
                'catatan'      => null,
            ]);

            $kategori->decrement('stok', $validated['jumlah']);
        });

        return redirect()
            ->route('transaksi.masuk')
            ->with('success', 'Transaksi berhasil disimpan dan masuk ke daftar pengerjaan!');
    }

    // 🔹 SIMPAN STOK MASUK
    public function storeStokMasuk(Request $request)
    {
        $validated = $request->validate(
            [
                'nomor_transaksi'    => 'required',
                'kategori_produk_id' => 'required|exists:kategori_produks,id',
                'jumlah'             => 'required|integer|min:1',
                'keterangan'         => 'nullable|string',
            ],
            [
                'nomor_transaksi.required'    => 'Nomor transaksi wajib diisi.',
                'kategori_produk_id.required' => 'Kategori barang wajib dipilih.',
                'kategori_produk_id.exists'   => 'Kategori barang tidak ditemukan.',
                'jumlah.required'             => 'Jumlah barang wajib diisi.',
                'jumlah.integer'              => 'Jumlah barang harus berupa angka.',
                'jumlah.min'                  => 'Jumlah minimal 1.',
            ]
        );

        $kategori = KategoriProduk::findOrFail($validated['kategori_produk_id']);

        DB::transaction(function () use ($validated, $kategori) {
            $validated['jenis_transaksi'] = 'pengeluaran';
            $validated['total_harga']     = $kategori->harga * $validated['jumlah'];
            $validated['deposit']         = 0;
            $validated['pelunasan']       = 0;

            Transaksi::create($validated);

            $kategori->increment('stok', $validated['jumlah']);
        });

        return redirect()
            ->route('transaksi.stok-masuk')
            ->with('success', 'Stok masuk berhasil disimpan.');
    }

    // 🔹 LAPORAN TRANSAKSI (TABEL + SEARCH)
    public function index(Request $request)
    {
        $pageTitle = 'Daftar Transaksi';
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

        return view('transaksi.index', compact('pageTitle', 'transaksis', 'search'));
    }

    // 🔹 EXPORT PDF (IKUT SEARCH)
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
        // atau:
        // return $pdf->stream($filename);
    }
}
