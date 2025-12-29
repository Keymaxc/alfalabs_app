@extends('layouts.dashboard')

@section('title', $pageTitle ?? 'Dashboard')

@section('content')
    <style>
        body { background: #f6f8fc; }
        .analytics-grid .card { border-radius: 16px; }
        .stat-chip {
            width: 46px; height: 46px; border-radius: 14px;
            display: grid; place-items: center; color: #fff; font-size: 18px;
        }
        .stat-muted { color: #6c757d; font-size: 0.8rem; letter-spacing: 0.05em; }
        .table-modern thead th { border: none !important; background: #f6f8fb; letter-spacing: 0.04em; font-size: 0.8rem; color: #6b7280; }
        .table-modern tbody tr:hover { background: #f8fbff; }
        .card-soft { border-radius: 18px; }
        .list-soft .list-group-item { border: none; }
        .list-soft .list-group-item + .list-group-item { border-top: 1px solid #eef1f6; }
    </style>

    {{-- Kartu Analitik (4 box) --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3 mb-4 analytics-grid">
        <div class="col">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-chip" style="background: linear-gradient(135deg,#1572e8,#4e9dff);">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <div class="stat-muted text-uppercase">Total Transaksi</div>
                        <div class="fw-bold fs-4">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
                        <div class="text-muted small">Semua transaksi tercatat</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-chip" style="background: linear-gradient(135deg,#22c55e,#52e499);">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div>
                        <div class="stat-muted text-uppercase">Pemasukan Hari Ini</div>
                        <div class="fw-bold fs-4">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</div>
                        <div class="text-muted small">Tanggal {{ now()->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-chip" style="background: linear-gradient(135deg,#a855f7,#6366f1);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div class="stat-muted text-uppercase">Pemasukan Bulan Ini</div>
                        <div class="fw-bold fs-4">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</div>
                        <div class="text-muted small">{{ now()->format('F Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-chip" style="background: linear-gradient(135deg,#f59e0b,#f97316);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-muted text-uppercase">Pelunasan Bulan Ini</div>
                        <div class="fw-bold fs-4">Rp {{ number_format($totalPelunasanBulanIni, 0, ',', '.') }}</div>
                        <div class="text-muted small">Sisa bayar yang sudah lunas</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-chip" style="background: linear-gradient(135deg,#ef4444,#fb7185);">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div>
                        <div class="stat-muted text-uppercase">Barang Perlu Restok</div>
                        <div class="fw-bold fs-4">{{ number_format($totalStokMenipis, 0, ',', '.') }}</div>
                        <div class="text-muted small">Stok di bawah batas minimum</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-chip" style="background: linear-gradient(135deg,#0ea5e9,#38bdf8);">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <div>
                        <div class="stat-muted text-uppercase">Transaksi Bulan Ini</div>
                        <div class="fw-bold fs-4">{{ number_format($totalTransaksiBulanIni, 0, ',', '.') }}</div>
                        <div class="text-muted small">Jumlah pemasukan</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Deadline + Restok (sebaris) --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-0">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Deadline Terdekat</p>
                        <h5 class="mb-0 fw-bold">Pengerjaan Mendekati Tenggat</h5>
                    </div>
                    <a href="{{ route('transaksi.index', ['deadline' => 'soon']) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        Lihat Transaksi
                    </a>
                </div>
                <div class="card-body">
                    @if(($deadlineAlerts ?? collect())->isNotEmpty())
                        <div class="list-group list-group-flush list-soft">
                            @foreach ($deadlineAlerts as $alert)
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ $alert->nomor_transaksi }}</div>
                                        <div class="text-muted small">
                                            {{ $alert->nama_pelanggan ?? '-' }} ·
                                            {{ $alert->kategoriProduk->nama_kategori ?? 'Tanpa kategori' }}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge bg-light text-dark border">
                                            {{ $alert->deadline_at?->format('d/m/Y') ?? '-' }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ $alert->deadline_at?->diffForHumans(now(), ['parts' => 2, 'short' => true]) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            Tidak ada deadline dalam 2 hari ke depan.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-0">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Inventori</p>
                        <h5 class="mb-0 fw-bold">Prioritas Restok</h5>
                    </div>
                    <a href="{{ route('master-data.kategori-produk.index') }}"
                       class="btn btn-sm btn-outline-secondary rounded-pill">
                        Lihat Stok
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle table-modern">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th class="text-end">Stok</th>
                                    <th class="text-end">Min</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stokMenipis as $stok)
                                    <tr>
                                        <td>{{ $stok->nama_kategori }}</td>
                                        <td class="text-end">{{ number_format($stok->stok, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($stok->stok_minimum, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            Semua stok aman.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Aktivitas Terbaru (full row) --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-0">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Aktivitas Terbaru</p>
                        <h5 class="mb-0 fw-bold">Transaksi Terbaru</h5>
                    </div>
                    <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle table-modern">
                            <thead>
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
        </div>
    </div>
@endsection
