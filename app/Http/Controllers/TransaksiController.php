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
        $pageTitle       = 'Input Penjualan';
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
        $pageTitle       = 'Input Stok Masuk';
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
                'deadline_at'        => 'required|date|after_or_equal:today',
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
                'deadline_at.required'        => 'Deadline wajib diisi.',
                'deadline_at.after_or_equal'  => 'Deadline tidak boleh di tanggal yang sudah lewat.',
            ]
        );

        $kategori = KategoriProduk::findOrFail($validated['kategori_produk_id']);

        if ($kategori->stok < $validated['jumlah']) {
            return back()
                ->withErrors(['jumlah' => 'Stok tidak mencukupi untuk transaksi ini.'])
                ->withInput();
        }

        $transaksi = DB::transaction(function () use ($validated, $kategori) {
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

            return $transaksi;
        });

        $strukUrl = route('transaksi.struk.pdf', $transaksi->id);

        return redirect()
            ->route('transaksi.masuk')
            ->with([
                'success'   => 'Transaksi berhasil ditambahkan.',
                'struk_url' => $strukUrl,
            ]);
    }

    // 🔹 SIMPAN STOK MASUK
    public function storeStokMasuk(Request $request)
    {
        $validated = $request->validate(
            [
                'nomor_transaksi'    => 'required',
                'kategori_produk_id' => 'required|exists:kategori_produks,id',
                'jumlah'             => 'required|integer|min:1',
                'harga_satuan'       => 'required|integer|min:0',
                'keterangan'         => 'nullable|string',
            ],
            [
                'nomor_transaksi.required'    => 'Nomor transaksi wajib diisi.',
                'kategori_produk_id.required' => 'Kategori barang wajib dipilih.',
                'kategori_produk_id.exists'   => 'Kategori barang tidak ditemukan.',
                'jumlah.required'             => 'Jumlah barang wajib diisi.',
                'jumlah.integer'              => 'Jumlah barang harus berupa angka.',
                'jumlah.min'                  => 'Jumlah minimal 1.',
                'harga_satuan.required'       => 'Harga satuan wajib diisi.',
                'harga_satuan.integer'        => 'Harga satuan harus berupa angka.',
                'harga_satuan.min'            => 'Harga satuan minimal 0.',
            ]
        );

        $kategori = KategoriProduk::findOrFail($validated['kategori_produk_id']);

        DB::transaction(function () use ($validated, $kategori) {
            $validated['jenis_transaksi'] = 'pengeluaran';
            $validated['total_harga']     = ($validated['harga_satuan'] ?? 0) * $validated['jumlah'];
            $validated['deposit']         = 0;
            $validated['pelunasan']       = 0;
            unset($validated['harga_satuan']);

            Transaksi::create($validated); // total_harga sudah manual input

            $kategori->increment('stok', $validated['jumlah']);
        });

        return redirect()
            ->route('transaksi.stok-masuk')
            ->with('success', 'Stok masuk berhasil disimpan.');
    }

    // 🔹 LAPORAN TRANSAKSI (TABEL + SEARCH)
    public function index(Request $request)
    {
        $pageTitle = 'Daftar Penjualan';
        $search    = $request->q; // ?q=...
        $deadline  = $request->query('deadline'); // ?deadline=soon

        $query = Transaksi::with('kategoriProduk')
            ->where('jenis_transaksi', 'pemasukan')
            ->latest();

        if ($deadline === 'soon') {
            $query->whereNotNull('deadline_at')
                  ->where('deadline_at', '<=', now()->addDays(2));
        }

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

        return view('transaksi.index', compact('pageTitle', 'transaksis', 'search', 'deadline'));
    }

    // 🔹 STRUK TRANSAKSI PENJUALAN
    public function strukPdf(Transaksi $transaksi)
    {
        if ($transaksi->jenis_transaksi !== 'pemasukan') {
            abort(404);
        }

        $pageTitle = 'Struk Transaksi';
        $pdf = Pdf::loadView('transaksi.struk_pdf', compact('transaksi', 'pageTitle'))
            ->setPaper('a5', 'portrait');

        $filename = 'struk_' . $transaksi->nomor_transaksi . '.pdf';

        return $pdf->download($filename);
    }

    // 🔹 LAPORAN STOK MASUK
    public function stokMasukReport(Request $request)
    {
        $pageTitle = 'Laporan Stok Masuk';
        $search    = $request->q;

        $query = Transaksi::with('kategoriProduk')
            ->where('nomor_transaksi', 'like', 'STK-%')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('kategoriProduk', function ($qq) use ($search) {
                      $qq->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        $transaksis = $query->paginate(10)->appends(['q' => $search]);

        return view('transaksi.laporan_stok_masuk', compact('pageTitle', 'transaksis', 'search'));
    }

    // 🔹 EXPORT PDF (IKUT SEARCH)
    public function exportPdf(Request $request)
    {
        $pageTitle = 'Laporan Transaksi Penjualan';
        $search    = $request->q;

        $query = Transaksi::with('kategoriProduk')
            ->where('jenis_transaksi', 'pemasukan')
            ->latest();

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

    // 🔹 EXPORT PDF STOK MASUK
    public function exportStokMasukPdf(Request $request)
    {
        $pageTitle = 'Laporan Stok Masuk';
        $search    = $request->q;

        $query = Transaksi::with('kategoriProduk')
            ->where('nomor_transaksi', 'like', 'STK-%')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('kategoriProduk', function ($qq) use ($search) {
                      $qq->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        $transaksis = $query->get();

        $pdf = Pdf::loadView('transaksi.laporan_stok_masuk_pdf', compact('transaksis', 'pageTitle', 'search'))
            ->setPaper('a4', 'portrait');

        $filename = 'laporan_stok_masuk_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}
