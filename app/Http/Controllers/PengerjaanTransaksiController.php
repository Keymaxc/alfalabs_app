<?php

namespace App\Http\Controllers;

use App\Models\PengerjaanTransaksi;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class PengerjaanTransaksiController extends Controller
{
    // 🔹 Halaman 1: Pengerjaan Berjalan (menunggu + proses)
    public function indexBerjalan(Request $request)
    {
        $pageTitle    = 'Order Berjalan';
        $search       = $request->q;
        $statusFilter = $request->status; // menunggu / proses

        $query = PengerjaanTransaksi::with(['transaksi.kategoriProduk'])
            ->whereIn('status', ['menunggu', 'proses'])
            ->latest();

        if ($search) {
            $query->whereHas('transaksi', function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                  ->orWhereHas('kategoriProduk', function ($qq) use ($search) {
                      $qq->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        if ($statusFilter && in_array($statusFilter, ['menunggu', 'proses'])) {
            $query->where('status', $statusFilter);
        }

        $pengerjaans = $query->paginate(10)->appends([
            'q'      => $search,
            'status' => $statusFilter,
        ]);

        // ⬅️ PERHATIKAN: ganti ke 'pengerjaan.berjalan'
        return view('pengerjaan.berjalan', compact(
            'pageTitle',
            'pengerjaans',
            'search',
            'statusFilter'
        ));
    }

    // 🔹 Halaman 2: Pengerjaan Selesai (selesai + diambil)
    public function indexSelesai(Request $request)
    {
        $pageTitle    = 'Order Selesai';
        $search       = $request->q;
        $statusFilter = $request->status; // selesai / diambil

        $query = PengerjaanTransaksi::with(['transaksi.kategoriProduk'])
            ->whereIn('status', ['selesai', 'diambil'])
            ->latest();

        if ($search) {
            $query->whereHas('transaksi', function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                  ->orWhereHas('kategoriProduk', function ($qq) use ($search) {
                      $qq->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        if ($statusFilter && in_array($statusFilter, ['selesai', 'diambil'])) {
            $query->where('status', $statusFilter);
        }

        $pengerjaans = $query->paginate(10)->appends([
            'q'      => $search,
            'status' => $statusFilter,
        ]);

        $diambilList = PengerjaanTransaksi::with(['transaksi.kategoriProduk'])
            ->where('status', 'diambil')
            ->latest();

        if ($search) {
            $diambilList->whereHas('transaksi', function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                  ->orWhereHas('kategoriProduk', function ($qq) use ($search) {
                      $qq->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        $diambilList = $diambilList->get();

        // ⬅️ PERHATIKAN: ganti ke 'pengerjaan.selesai'
        return view('pengerjaan.selesai', compact(
            'pageTitle',
            'pengerjaans',
            'search',
            'statusFilter',
            'diambilList'
        ));
    }

    // 🔹 Update status pengerjaan
    public function update(Request $request, PengerjaanTransaksi $pengerjaan)
    {
        $pengerjaan->loadMissing('transaksi');

        $validated = $request->validate([
            'status'  => 'required|in:menunggu,proses,selesai,diambil',
            'catatan' => 'nullable|string',
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in'       => 'Status tidak valid.',
        ]);

        // Cegah ambil barang jika belum lunas
        if ($validated['status'] === 'diambil' && $pengerjaan->transaksi && ($pengerjaan->transaksi->pelunasan ?? 0) > 0) {
            return back()->withErrors(['status' => 'Barang belum lunas, lakukan pelunasan terlebih dahulu.']);
        }

        $pengerjaan->update($validated);

        return back()->with('success', 'Status pengerjaan berhasil diperbarui!');
    }

    // 🔹 Pelunasan transaksi
    public function pelunasan(Request $request, Transaksi $transaksi)
    {
        $validated = $request->validate([
            'jumlah_pelunasan' => 'required|numeric|min:1',
        ], [
            'jumlah_pelunasan.required' => 'Nominal pelunasan wajib diisi.',
            'jumlah_pelunasan.numeric'  => 'Nominal pelunasan harus berupa angka.',
            'jumlah_pelunasan.min'      => 'Nominal pelunasan minimal 1.',
        ]);

        $sisa = max($transaksi->pelunasan ?? 0, 0);
        if ($sisa <= 0) {
            return back()->with('success', 'Transaksi sudah lunas.');
        }

        $bayar = min($validated['jumlah_pelunasan'], $sisa);
        $transaksi->deposit   = ($transaksi->deposit ?? 0) + $bayar;
        $transaksi->pelunasan = max(0, $sisa - $bayar);
        $transaksi->save();

        return back()->with('success', 'Pelunasan berhasil disimpan.');
    }
}
