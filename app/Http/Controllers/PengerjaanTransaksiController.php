<?php

namespace App\Http\Controllers;

use App\Models\PengerjaanTransaksi;
use Illuminate\Http\Request;

class PengerjaanTransaksiController extends Controller
{
    // 🔹 Halaman 1: Pengerjaan Berjalan (menunggu + proses)
    public function indexBerjalan(Request $request)
    {
        $pageTitle    = 'Pengerjaan Berjalan';
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
        $pageTitle    = 'Pengerjaan Selesai';
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

        // ⬅️ PERHATIKAN: ganti ke 'pengerjaan.selesai'
        return view('pengerjaan.selesai', compact(
            'pageTitle',
            'pengerjaans',
            'search',
            'statusFilter'
        ));
    }

    // 🔹 Update status pengerjaan
    public function update(Request $request, PengerjaanTransaksi $pengerjaan)
    {
        $validated = $request->validate([
            'status'  => 'required|in:menunggu,proses,selesai,diambil',
            'catatan' => 'nullable|string',
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in'       => 'Status tidak valid.',
        ]);

        $pengerjaan->update($validated);

        return back()->with('success', 'Status pengerjaan berhasil diperbarui!');
    }
}
