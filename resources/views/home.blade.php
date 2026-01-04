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
        .bar-track { background: #eef2f7; height: 10px; border-radius: 999px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 999px; }
        .bar-income { background: linear-gradient(90deg, #22c55e, #52e499); }
        .bar-expense { background: linear-gradient(90deg, #ef4444, #fb7185); }
        .card-soft .list-icon { width: 32px; height: 32px; display: grid; place-items: center; border-radius: 10px; }
    </style>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('analytics-filter');
        if (!form) return;

        const dateInput = document.getElementById('dateFilter');
        const monthInput = document.getElementById('monthFilter');
        const todayBtn  = document.getElementById('todayBtn');

        const submitWithMonth = () => {
            if (dateInput && monthInput && dateInput.value) {
                monthInput.value = dateInput.value.slice(0, 7);
            }
            form.submit();
        };

        if (dateInput) {
            dateInput.addEventListener('change', submitWithMonth);
        }

        if (todayBtn && dateInput) {
            todayBtn.addEventListener('click', () => {
                const today = new Date();
                const y = today.getFullYear();
                const m = String(today.getMonth() + 1).padStart(2, '0');
                const d = String(today.getDate()).padStart(2, '0');
                dateInput.value = `${y}-${m}-${d}`;
                submitWithMonth();
            });
        }
    });
</script>
@endpush

    {{-- Filter global (gabung tanggal & bulan) --}}
    <form id="analytics-filter" action="{{ route('home') }}" method="GET" class="card border-0 shadow-sm mb-3 card-soft">
        <div class="card-body d-flex flex-wrap gap-3 align-items-center">
            <div class="d-flex flex-column">
                <label class="form-label text-muted small mb-1">Pilih tanggal</label>
                <input type="date" name="date" id="dateFilter" value="{{ $selectedDate->format('Y-m-d') }}" class="form-control form-control-sm" style="min-width: 180px;">
                <input type="hidden" name="month" id="monthFilter" value="{{ $selectedMonth }}">
                <small class="text-muted mt-1">Bulan otomatis mengikuti tanggal.</small>
            </div>
            <div class="ms-auto d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="todayBtn">
                    Hari ini
                </button>
                <a href="{{ route('home') }}" class="btn btn-light btn-sm rounded-pill px-3">Reset</a>
            </div>
        </div>
    </form>

    {{-- Status Akses & Data dihilangkan sesuai permintaan --}}

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
                        <div class="stat-muted text-uppercase">Pemasukan Tanggal Ini</div>
                        <div class="fw-bold fs-4">Rp {{ number_format($totalTanggal, 0, ',', '.') }}</div>
                        <div class="text-muted small">Tanggal {{ $selectedDateLabel }} ({{ number_format($totalTransaksiTanggal, 0, ',', '.') }} transaksi)</div>
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
                        <div class="text-muted small">{{ $selectedMonthLabel }} ({{ number_format($totalTransaksiBulanIni, 0, ',', '.') }} transaksi)</div>
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

    {{-- Laporan Keuangan Bulanan (hanya superadmin) --}}
    @if(auth()->user()?->isSuperAdmin())
    @php
        $maxWeekly = max(
            max($weeklyIncome ?? [0]),
            max($weeklyExpense ?? [0]),
            1
        );
    @endphp
    <div class="card border-0 shadow-sm mb-4 card-soft">
        <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <p class="text-uppercase text-muted small mb-1">Laporan Keuangan</p>
                <h5 class="mb-0 fw-bold">Periode {{ $selectedMonthLabel }}</h5>
            </div>
            <form action="{{ route('home') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                <input type="month" name="month" value="{{ $selectedMonth }}" class="form-control form-control-sm" style="min-width: 160px;">
                <button class="btn btn-outline-secondary btn-sm rounded-pill" type="submit">Terapkan</button>
                <a href="{{ route('laporan.keuangan.pdf', ['month' => $selectedMonth]) }}"
                   class="btn btn-primary btn-sm rounded-pill">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
            </form>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="list-group list-group-flush list-soft">
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <div>
                                <div class="text-muted small">Total Pemasukan</div>
                                <div class="fw-bold">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</div>
                            </div>
                            <span class="badge bg-success-subtle text-success border">Pemasukan</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <div>
                                <div class="text-muted small">Total Pengeluaran / Kerugian</div>
                                <div class="fw-bold">Rp {{ number_format($totalPengeluaranBulanIni, 0, ',', '.') }}</div>
                            </div>
                            <span class="badge bg-danger-subtle text-danger border">Pengeluaran</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <div>
                                <div class="text-muted small">Laba / Rugi</div>
                                @php $isProfit = $labaRugiBulanIni >= 0; @endphp
                                <div class="fw-bold {{ $isProfit ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($labaRugiBulanIni, 0, ',', '.') }}
                                </div>
                            </div>
                            <span class="badge {{ $isProfit ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border">
                                {{ $isProfit ? 'Laba' : 'Rugi' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="text-uppercase text-muted small">Grafik Mingguan</div>
                            <div class="fw-semibold">Pemasukan vs Pengeluaran</div>
                        </div>
                        <div class="d-flex align-items-center gap-3 small text-muted">
                            <span class="d-inline-flex align-items-center gap-1">
                                <span style="width:14px;height:8px;background:#22c55e;display:inline-block;border-radius:4px;"></span> Pemasukan
                            </span>
                            <span class="d-inline-flex align-items-center gap-1">
                                <span style="width:14px;height:8px;background:#ef4444;display:inline-block;border-radius:4px;"></span> Pengeluaran
                            </span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">Minggu</th>
                                    <th>Pemasukan</th>
                                    <th>Pengeluaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($weeklyIncome as $week => $incomeValue)
                                    @php
                                        $expenseValue = $weeklyExpense[$week] ?? 0;
                                        $incomePct  = $maxWeekly > 0 ? ($incomeValue / $maxWeekly) * 100 : 0;
                                        $expensePct = $maxWeekly > 0 ? ($expenseValue / $maxWeekly) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">Minggu {{ $week }}</td>
                                        <td>
                                            <div class="bar-track mb-1">
                                                <div class="bar-fill bar-income" style="width: {{ $incomePct }}%;"></div>
                                            </div>
                                            <div class="small text-muted">Rp {{ number_format($incomeValue, 0, ',', '.') }}</div>
                                        </td>
                                        <td>
                                            <div class="bar-track mb-1">
                                                <div class="bar-fill bar-expense" style="width: {{ $expensePct }}%;"></div>
                                            </div>
                                            <div class="small text-muted">Rp {{ number_format($expenseValue, 0, ',', '.') }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Rekap transaksi per tanggal & per bulan --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-0">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Rekap</p>
                        <h5 class="mb-0 fw-bold">Transaksi per Tanggal (14 hari)</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle table-modern">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end">Pemasukan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transaksiPerTanggal as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                        <td class="text-end">{{ number_format($row->total_transaksi, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($row->total_pemasukan, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 card-soft">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-0">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Rekap</p>
                        <h5 class="mb-0 fw-bold">Transaksi per Bulan (12 bulan)</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle table-modern">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end">Pemasukan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transaksiPerBulan as $row)
                                    @php
                                        $bulanLabel = \Carbon\Carbon::createFromFormat('Y-m', $row->bulan)->isoFormat('MMMM Y');
                                    @endphp
                                    <tr>
                                        <td>{{ $bulanLabel }}</td>
                                        <td class="text-end">{{ number_format($row->total_transaksi, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($row->total_pemasukan, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                        <h5 class="mb-0 fw-bold">Pengerjaan 7 Hari Ke Depan</h5>
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
