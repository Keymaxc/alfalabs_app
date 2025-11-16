@extends('layouts.dashboard')

@section('title', $pageTitle ?? 'Dashboard Analitik')

@section('content')
    {{-- 🔹 Row Kartu Analitik --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Total Transaksi</div>
                    <div class="fw-bold fs-4">
                        {{ number_format($totalTransaksi, 0, ',', '.') }}
                    </div>
                    <div class="text-muted small mt-1">
                        Semua transaksi yang tercatat
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Pemasukan Hari Ini</div>
                    <div class="fw-bold fs-4">
                        Rp {{ number_format($totalHariIni, 0, ',', '.') }}
                    </div>
                    <div class="text-muted small mt-1">
                        Tanggal {{ now()->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Pemasukan Bulan Ini</div>
                    <div class="fw-bold fs-4">
                        Rp {{ number_format($totalBulanIni, 0, ',', '.') }}
                    </div>
                    <div class="text-muted small mt-1">
                        {{ now()->format('F Y') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Pelunasan Bulan Ini</div>
                    <div class="fw-bold fs-4">
                        Rp {{ number_format($totalPelunasanBulanIni, 0, ',', '.') }}
                    </div>
                    <div class="text-muted small mt-1">
                        Sisa bayar yang sudah lunas
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 🔹 Tabel Transaksi Terbaru --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transaksi Terbaru</h5>
            <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                Lihat Semua
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px" class="text-center">#</th>
                            <th>Nomor Transaksi</th>
                            <th>Kategori</th>
                            <th>Nama Pelanggan</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Deposit</th>
                            <th class="text-end">Pelunasan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestTransaksi as $trx)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $trx->nomor_transaksi }}</td>
                                <td>{{ $trx->kategoriProduk->nama_kategori ?? '-' }}</td>
                                <td>{{ $trx->nama_pelanggan ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($trx->deposit, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($trx->pelunasan, 0, ',', '.') }}</td>
                                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    Belum ada transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
