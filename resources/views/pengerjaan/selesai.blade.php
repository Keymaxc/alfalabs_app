@extends('layouts.dashboard')
@section('title', $pageTitle)

@section('content')
    <style>
        #tabel-pengerjaan tbody td {
            font-size: 0.85rem;
        }

        #tabel-pengerjaan thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .search-track-input {
            border-radius: 999px;
            font-size: 0.85rem;
        }

        .btn-rounded {
            border-radius: 999px;
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 0.3rem 0.55rem;
            border-radius: 999px;
        }
    </style>

    <div class="card">
        <div class="card-body py-4">

            {{-- Alert --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Bar atas: search + filter status + link ke halaman berjalan --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <form
                    action="{{ route('pengerjaan.selesai') }}"
                    method="GET"
                    class="d-flex flex-grow-1 gap-2"
                >
                    <input
                        type="text"
                        name="q"
                        class="form-control search-track-input flex-grow-1"
                        placeholder="Cari nomor transaksi, pelanggan, atau kategori..."
                        value="{{ $search }}"
                        autocomplete="off"
                    >

                    <select
                        name="status"
                        class="form-select search-track-input"
                        style="max-width: 180px"
                        onchange="this.form.submit()"
                    >
                        <option value="">Semua Status</option>
                        <option value="selesai" {{ $statusFilter == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="diambil" {{ $statusFilter == 'diambil' ? 'selected' : '' }}>Diambil</option>
                    </select>
                </form>

            {{-- Tombol ke halaman berjalan --}}
                <a href="{{ route('pengerjaan.berjalan') }}"
                   class="btn btn-outline-secondary btn-rounded px-3">
                    <i class="fas fa-spinner me-1"></i> Lihat yang Berjalan
                </a>
            </div>

            @if ($search || $statusFilter)
                <p class="text-muted mb-2" style="font-size: 0.8rem;">
                    Filter:
                    @if ($search)
                        <span class="me-2">Pencarian: <strong>"{{ $search }}"</strong></span>
                    @endif
                    @if ($statusFilter)
                        <span>Status: <strong>{{ ucfirst($statusFilter) }}</strong></span>
                    @endif
                </p>
            @endif

            {{-- Tabel --}}
            <div class="table-responsive">
                <table id="tabel-pengerjaan" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px">No</th>
                            <th>Nomor Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Kategori</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Deposit</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Status</th>
                            <th>Catatan</th>
                            <th style="width: 150px;">Deadline</th>
                            <th class="text-center" style="width: 90px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengerjaans as $item)
                            @php
                                $trx   = $item->transaksi;
                                $kat   = $trx?->kategoriProduk;
                                $sisa  = max($trx->pelunasan ?? 0, 0);
                                $badge = match ($item->status) {
                                    'selesai' => 'bg-success',
                                    'diambil' => 'bg-dark',
                                    default   => 'bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration + ($pengerjaans->currentPage() - 1) * $pengerjaans->perPage() }}
                                </td>
                                <td>{{ $trx?->nomor_transaksi ?? '-' }}</td>
                                <td>{{ $trx?->nama_pelanggan ?? '-' }}</td>
                                <td>{{ $kat?->nama_kategori ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format($trx->total_harga ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($trx->deposit ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end {{ $sisa > 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($sisa, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-status {{ $badge }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td style="max-width: 220px;">
                                    <span style="font-size: 0.8rem;">
                                        {{ $item->catatan ? $item->catatan : '-' }}
                                    </span>
                                </td>
                                <td style="font-size: 0.8rem;">
                                    @php $deadline = $trx?->deadline_at; @endphp
                                    @if($deadline)
                                        <div>{{ $deadline->format('d/m/Y') }}</div>
                                        <div class="small text-muted">
                                            {{ $deadline->diffForHumans(now(), ['parts' => 2, 'short' => true]) }}
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button
                                        class="btn btn-outline-primary btn-sm btn-rounded"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalUpdatePengerjaan{{ $item->id }}"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($sisa > 0)
                                        <button
                                            class="btn btn-outline-success btn-sm btn-rounded"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalPelunasan{{ $item->id }}"
                                        >
                                            <i class="fas fa-wallet"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            {{-- Modal update tahapan --}}
                            <div class="modal fade" id="modalUpdatePengerjaan{{ $item->id }}" tabindex="-1"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="{{ route('pengerjaan.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-content border-0 shadow-sm">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">
                                                    Update Pengerjaan - {{ $trx?->nomor_transaksi }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body" style="font-size: 0.85rem;">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Tahapan / Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="diambil" selected>Diambil</option>
                                                    </select>
                                                    </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Catatan</label>
                                                    <textarea
                                                        name="catatan"
                                                        rows="3"
                                                        class="form-control"
                                                        placeholder="Contoh: Sudah diambil pelanggan."
                                                    >{{ old('catatan', $item->catatan) }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button"
                                                        class="btn btn-light border btn-rounded px-3"
                                                        data-bs-dismiss="modal">
                                                    Batal
                                                </button>
                                                <button type="submit"
                                                        class="btn btn-primary btn-rounded px-3">
                                                    <i class="fas fa-save me-1"></i> Simpan
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Modal Pelunasan --}}
                            <div class="modal fade" id="modalPelunasan{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="{{ route('pengerjaan.pelunasan', $trx?->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-content border-0 shadow-sm">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">
                                                    Pelunasan - {{ $trx?->nomor_transaksi }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body" style="font-size: 0.85rem;">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Sisa Bayar</label>
                                                    <input type="text" class="form-control" value="Rp {{ number_format($sisa, 0, ',', '.') }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nominal Pelunasan</label>
                                                    <input type="number" name="jumlah_pelunasan" class="form-control" min="1" max="{{ $sisa }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light border btn-rounded px-3" data-bs-dismiss="modal">
                                                    Batal
                                                </button>
                                                <button type="submit" class="btn btn-success btn-rounded px-3">
                                                    <i class="fas fa-save me-1"></i> Simpan
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada pengerjaan selesai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $pengerjaans->links() }}
            </div>

            {{-- Laporan barang sudah diambil --}}
            <hr class="my-4">
            <h6 class="fw-bold text-uppercase text-muted mb-3">Laporan Barang Sudah Diambil</h6>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px">No</th>
                            <th>Nomor Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th style="width: 150px;">Tanggal Diambil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($diambilList as $item)
                            @php
                                $trx = $item->transaksi;
                                $kat = $trx?->kategoriProduk;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $trx?->nomor_transaksi ?? '-' }}</td>
                                <td>{{ $trx?->nama_pelanggan ?? '-' }}</td>
                                <td>{{ $kat?->nama_kategori ?? '-' }}</td>
                                <td style="max-width: 220px;">
                                    <span style="font-size: 0.8rem;">
                                        {{ $item->catatan ? $item->catatan : '-' }}
                                    </span>
                                </td>
                                <td style="font-size: 0.8rem;">
                                    {{ $item->updated_at?->format('d/m/Y H:i') ?? $item->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada barang yang diambil.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000
        });
        @endif

        @if ($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            html: `{!! implode('<br>', $errors->all()) !!}`,
        });
        @endif
    </script>
@endpush
