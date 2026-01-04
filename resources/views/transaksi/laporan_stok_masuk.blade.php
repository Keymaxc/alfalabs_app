@extends('layouts.dashboard')
@section('title', $pageTitle ?? 'Laporan Stok Masuk')

@section('content')
    <style>
        #tabel-stok-masuk tbody td {
            font-size: 0.85rem;
        }
    </style>

    <div class="card">
        <div class="card-body py-5">
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

            {{-- Bar atas --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <form id="form-search-stok" action="{{ route('transaksi.stok-masuk.laporan') }}" method="GET" class="d-flex flex-grow-1">
                    <input
                        type="text"
                        name="q"
                        id="search-stok"
                        class="form-control flex-grow-1 rounded-pill"
                        placeholder="Cari nomor STK, kategori, atau keterangan..."
                        value="{{ request('q') }}"
                        autocomplete="off"
                    >
                </form>

                <div class="d-flex gap-2">
                    <a href="{{ route('transaksi.stok-masuk') }}" class="btn btn-outline-primary rounded-pill px-3">
                        <i class="fas fa-box-open me-1"></i> Input Stok Masuk
                    </a>
                    <a href="{{ route('transaksi.stok-masuk.export.pdf', ['q' => request('q')]) }}"
                       class="btn btn-outline-danger rounded-pill px-3"
                       target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </a>
                </div>
            </div>

            @if (request('q'))
                <p class="text-muted mb-2">
                    Hasil pencarian untuk: <strong>"{{ request('q') }}"</strong>
                </p>
            @endif

            {{-- Tabel --}}
            <div class="table-responsive">
                <table id="tabel-stok-masuk" class="table table-hover table-sm align-middle mt-3 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px">No</th>
                            <th>Nomor Transaksi</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Jumlah Masuk</th>
                            <th class="text-end">Estimasi Total</th>
                            <th>Keterangan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stokMasuks as $trx)
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration + ($stokMasuks->currentPage() - 1) * $stokMasuks->perPage() }}
                                </td>
                                <td>{{ $trx->nomor_transaksi }}</td>
                                <td>{{ $trx->kategoriProduk->nama_kategori ?? '-' }}</td>
                                <td class="text-end">
                                    Rp {{ number_format($trx->harga_satuan ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">{{ number_format($trx->jumlah, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                                <td>{{ $trx->keterangan ?? '-' }}</td>
                                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada stok masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $stokMasuks->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000
        });
        @endif

        @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            html: `{!! implode('<br>', $errors->all()) !!}`,
        });
        @endif

        // Auto search
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-stok');
            const formSearch  = document.getElementById('form-search-stok');
            if (!searchInput || !formSearch) return;

            let typingTimer;
            const doneTypingInterval = 500;

            searchInput.addEventListener('input', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function () {
                    formSearch.submit();
                }, doneTypingInterval);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    formSearch.submit();
                }
            });
        });
    </script>
@endpush
