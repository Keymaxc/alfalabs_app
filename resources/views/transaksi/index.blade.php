@extends('layouts.dashboard')
@section('title', $pageTitle)

@section('content')
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

        {{-- 🔹 Tombol Input Transaksi --}}
        <a href="{{ route('transaksi.masuk') }}" class="btn btn-dark mb-3">
            <i class="fas fa-plus-circle"></i> Input Transaksi
        </a>

        {{-- 🔹 Tabel Data Transaksi --}}
        <table class="table mt-3 align-middle">
            <thead class="table-dark">
                <tr>
                    <th class="text-center" style="width: 40px">No</th>
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
                @forelse ($transaksis as $trx)
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
                        <td colspan="8" class="text-center">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- 🔹 Pagination --}}
        <div class="mt-3">
            {{ $transaksis->links() }}
        </div>
    </div>
</div>
@endsection

{{-- 🔹 SweetAlert Notifikasi --}}
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
</script>
@endpush
