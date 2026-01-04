@extends('layouts.dashboard')

@section('title', $pageTitle ?? 'Forecast & Rekomendasi Stok')

@section('content')
    <style>
        .badge-status { text-transform: capitalize; }
        .chip-info {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #f5f7fb;
            color: #4b5563;
            font-size: 0.82rem;
        }
        .chip-dot {
            width: 8px; height: 8px; border-radius: 50%; background: #0ea5e9;
        }
    </style>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Forecast & Rekomendasi Stok</h4>
            <span class="chip-info">
                <span class="chip-dot"></span>
                Dihitung {{ $generatedAt->format('d/m/Y') }}
            </span>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3 text-muted small">
                <span class="chip-info">
                    <i class="fas fa-magic text-primary"></i>
                    Berdasarkan penjualan 60 hari terakhir
                </span>
                <span class="chip-info">
                    <i class="fas fa-shield-alt text-success"></i>
                    Ada buffer stok aman
                </span>
                <span class="chip-info">
                    <i class="fas fa-clock text-warning"></i>
                    Lead time & MOQ ikut master
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th class="text-end">Stok Saat Ini</th>
                            <th class="text-end">Lead Time (hari)</th>
                            <th class="text-end">Perkiraan 7 Hari</th>
                            <th class="text-end">Batas Pesan Ulang *</th>
                            <th>Status</th>
                            <th class="text-end">Saran Beli (qty)</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recommendations as $rec)
                            @php
                                $meta = $rec->meta ?? [];
                                $status = $rec->status;
                                $badgeClass = match ($status) {
                                    'perlu_beli' => 'bg-danger',
                                    'overstock'  => 'bg-warning text-dark',
                                    default      => 'bg-success',
                                };
                            @endphp
                            <tr>
                                <td>{{ $rec->kategoriProduk->nama_kategori ?? '-' }}</td>
                                <td class="text-end">{{ number_format($meta['stok'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $meta['lead_time'] ?? ($rec->kategoriProduk->lead_time_days ?? 0) }}</td>
                                <td class="text-end">{{ number_format($meta['pred_7d'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($meta['rop'] ?? 0, 0, ',', '.') }}</td>
                                <td><span class="badge badge-status {{ $badgeClass }}">{{ str_replace('_', ' ', $status) }}</span></td>
                                <td class="text-end">
                                    @if($rec->recommended_qty > 0)
                                        {{ number_format($rec->recommended_qty, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $rec->note ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data rekomendasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-3 mb-0">
                * Batas Pesan Ulang = kebutuhan saat lead time + buffer stok aman. Jika stok di bawah angka ini, segera lakukan pembelian.
            </p>
        </div>
    </div>
@endsection
