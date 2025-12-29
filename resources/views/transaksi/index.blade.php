@extends('layouts.dashboard')
@section('title', $pageTitle)

@section('content')
    <style>
        #tabel-transaksi tbody td {
            font-size: 0.85rem;
        }
    </style>

    <div class="card">
        <div class="card-body py-5">

            {{-- 🔹 Alert Pesan --}}
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

            {{-- 🔹 Bar Atas: Search + Tombol Aksi --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">

                {{-- Form Search (auto search saat ngetik) --}}
                <form id="form-search-transaksi" action="{{ route('transaksi.index') }}" method="GET"
                      class="d-flex flex-grow-1">
                    <input
                        type="text"
                        name="q"
                        id="search-transaksi"
                        class="form-control flex-grow-1 rounded-pill"
                        placeholder="Ketik untuk mencari nomor transaksi, pelanggan, kategori, atau keterangan..."
                        value="{{ request('q') }}"
                        autocomplete="off"
                    >
                </form>

                {{-- Tombol Input & Export PDF --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('transaksi.masuk') }}"
                       class="btn btn-primary rounded-pill px-3">
                        <i class="fas fa-plus-circle me-1"></i> Input Transaksi
                    </a>
                    <a href="{{ route('transaksi.stok-masuk') }}"
                       class="btn btn-outline-primary rounded-pill px-3">
                        <i class="fas fa-boxes-stacked me-1"></i> Stok Masuk
                    </a>
                    <a href="{{ route('transaksi.export.pdf', ['q' => request('q')]) }}"
                       class="btn btn-outline-danger rounded-pill px-3"
                       target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </a>
                </div>
            </div>

            {{-- Info filter kalau ada search --}}
            @if (request('q'))
                <p class="text-muted mb-2">
                    Hasil pencarian untuk: <strong>"{{ request('q') }}"</strong>
                </p>
            @endif

            {{-- 🔹 Tabel Data Transaksi (putih, minimalis) --}}
            <div class="table-responsive">
                <table id="tabel-transaksi" class="table table-hover table-sm align-middle mt-3 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px">No</th>
                            <th>Nomor Transaksi</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Nama Pelanggan</th>
                            <th class="text-end">Jumlah</th>
                            <th>Keterangan</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Deposit</th>
                            <th class="text-end">Pelunasan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksis as $trx)
                            <tr>
                                {{-- Nomor global (ikut pagination) --}}
                                <td class="text-center">
                                    {{ $loop->iteration + ($transaksis->currentPage() - 1) * $transaksis->perPage() }}
                                </td>
                                <td>{{ $trx->nomor_transaksi }}</td>
                                <td class="text-capitalize">{{ $trx->jenis_transaksi }}</td>
                                <td>{{ $trx->kategoriProduk->nama_kategori ?? '-' }}</td>
                                <td>{{ $trx->nama_pelanggan ?? '-' }}</td>
                                <td class="text-end">{{ number_format($trx->jumlah, 0, ',', '.') }}</td>
                                <td>{{ $trx->keterangan ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($trx->deposit, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($trx->pelunasan, 0, ',', '.') }}</td>
                                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">Belum ada transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 🔹 Pagination --}}
            <div class="mt-3">
                {{ $transaksis->links() }}
            </div>
        </div>
    </div>
@endsection

{{-- 🔹 SweetAlert + Auto Search --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // SweetAlert Notifikasi
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

        // 🔍 Auto search saat ngetik
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-transaksi');
            const formSearch  = document.getElementById('form-search-transaksi');
            if (!searchInput || !formSearch) return;

            let typingTimer;
            const doneTypingInterval = 500; 

            // Saat mengetik
            searchInput.addEventListener('input', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function () {
                    formSearch.submit(); // auto submit
                }, doneTypingInterval);
            });

            // Saat tekan Enter -> langsung submit (jangan nunggu 0.5 detik)
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    formSearch.submit();
                }
            });
        });
    </script>
@endpush
