@extends('layouts.dashboard')

@section('title', $pageTitle ?? 'Transaksi Masuk')

@section('content')
    {{-- Styling khusus form transaksi, nggak ngaruh ke halaman lain --}}
    <style>
        .form-transaksi-wrapper .form-label {
            font-size: 0.85rem;
        }

        .form-transaksi-wrapper .form-control,
        .form-transaksi-wrapper .form-select,
        .form-transaksi-wrapper .input-group-text {
            font-size: 0.85rem;
        }

        .form-transaksi-wrapper h6 {
            font-size: 0.78rem;
            letter-spacing: 0.05em;
        }
    </style>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Transaksi Masuk</h4>
            <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-list me-1"></i> Laporan Transaksi
            </a>
        </div>

        <div class="card-body py-4 form-transaksi-wrapper">

            {{-- 🔹 Alert Pesan (Bootstrap biasa) --}}
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

            <form action="{{ route('transaksi.store') }}" method="POST">
                @csrf

                {{-- ====================== INFO TRANSAKSI ====================== --}}
                <h6 class="fw-bold text-uppercase text-muted mb-3">Info Transaksi</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nomor_transaksi" class="form-label fw-semibold">Nomor Transaksi</label>
                        <input
                            type="text"
                            name="nomor_transaksi"
                            id="nomor_transaksi"
                            class="form-control @error('nomor_transaksi') is-invalid @enderror"
                            value="{{ old('nomor_transaksi', $nomorTransaksi ?? '') }}"
                            readonly
                            required
                        >
                        @error('nomor_transaksi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jenis Transaksi</label>
                        <input
                            type="text"
                            class="form-control"
                            value="Pemasukan"
                            readonly
                        >
                        <input type="hidden" name="jenis_transaksi" value="pemasukan">
                    </div>
                </div>

                <hr>

                {{-- ====================== DETAIL BARANG ====================== --}}
                <h6 class="fw-bold text-uppercase text-muted mb-3">Detail Barang</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-7">
                        <label for="kategori_produk_id" class="form-label fw-semibold">Kategori Barang</label>
                        <select
                            name="kategori_produk_id"
                            id="kategori_produk_id"
                            class="form-select @error('kategori_produk_id') is-invalid @enderror"
                            required
                        >
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($kategoriProduks as $kategori)
                                <option
                                    value="{{ $kategori->id }}"
                                    data-harga="{{ $kategori->harga }}"
                                    {{ old('kategori_produk_id') == $kategori->id ? 'selected' : '' }}
                                >
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_produk_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-5">
                        <label for="harga_satuan" class="form-label fw-semibold">Harga Satuan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                type="text"
                                id="harga_satuan"
                                class="form-control"
                                value="0"
                                readonly
                            >
                        </div>
                        <input type="hidden" name="harga" id="harga_raw">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="jumlah" class="form-label fw-semibold">Jumlah Barang</label>
                        <input
                            type="number"
                            name="jumlah"
                            id="jumlah"
                            min="1"
                            class="form-control @error('jumlah') is-invalid @enderror"
                            value="{{ old('jumlah', 1) }}"
                            required
                        >
                        @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <label for="total_harga" class="form-label fw-semibold">Total Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                type="text"
                                id="total_harga_display"
                                class="form-control"
                                value="0"
                                readonly
                            >
                        </div>
                        <input
                            type="hidden"
                            name="total_harga"
                            id="total_harga"
                            value="{{ old('total_harga', 0) }}"
                        >
                        @error('total_harga')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                {{-- ====================== DATA PELANGGAN ====================== --}}
                <h6 class="fw-bold text-uppercase text-muted mb-3">Data Pelanggan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nama_pelanggan" class="form-label fw-semibold">Nama Pelanggan</label>
                        <input
                            type="text"
                            name="nama_pelanggan"
                            id="nama_pelanggan"
                            class="form-control @error('nama_pelanggan') is-invalid @enderror"
                            value="{{ old('nama_pelanggan') }}"
                            required
                        >
                        @error('nama_pelanggan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="kontak_pelanggan" class="form-label fw-semibold">Kontak Pelanggan</label>
                        <input
                            type="text"
                            name="kontak_pelanggan"
                            id="kontak_pelanggan"
                            class="form-control @error('kontak_pelanggan') is-invalid @enderror"
                            value="{{ old('kontak_pelanggan') }}"
                            required
                        >
                        @error('kontak_pelanggan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                {{-- ====================== PEMBAYARAN ====================== --}}
                <h6 class="fw-bold text-uppercase text-muted mb-3">Pembayaran</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="deposit" class="form-label fw-semibold">Deposit</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                type="number"
                                name="deposit"
                                id="deposit"
                                class="form-control @error('deposit') is-invalid @enderror"
                                value="{{ old('deposit', 0) }}"
                                min="0"
                                required
                            >
                        </div>
                        @error('deposit')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="pelunasan_display" class="form-label fw-semibold">Pelunasan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                type="text"
                                id="pelunasan_display"
                                class="form-control"
                                value="0"
                                readonly
                            >
                        </div>
                        <input
                            type="hidden"
                            name="pelunasan"
                            id="pelunasan"
                            value="{{ old('pelunasan', 0) }}"
                        >
                        @error('pelunasan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
                        <textarea
                            name="keterangan"
                            id="keterangan"
                            rows="3"
                            class="form-control @error('keterangan') is-invalid @enderror"
                        >{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('transaksi.index') }}" class="btn btn-light border rounded-pill px-3">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                    <button type="reset" class="btn btn-outline-secondary rounded-pill px-3">
                        Reset
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-3">
                        <i class="fas fa-save me-1"></i> Simpan Transaksi
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

{{--  SweetAlert + Script Perhitungan --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Notifikasi SweetAlert
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

    function formatRupiah(angka) {
        angka = Number(angka) || 0;
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(angka);
    }

    function updatePelunasan() {
        const totalHidden      = document.getElementById('total_harga');
        const depositInput     = document.getElementById('deposit');
        const pelunasanHidden  = document.getElementById('pelunasan');
        const pelunasanDisplay = document.getElementById('pelunasan_display');

        if (!totalHidden || !depositInput || !pelunasanHidden || !pelunasanDisplay) return;

        const total   = Number(totalHidden.value || 0);
        const deposit = Number(depositInput.value || 0);

        let pelunasan = total - deposit;
        if (pelunasan < 0) pelunasan = 0;

        pelunasanHidden.value  = pelunasan;
        pelunasanDisplay.value = formatRupiah(pelunasan);
    }

    function updateHargaDanTotal() {
        const kategoriSelect   = document.getElementById('kategori_produk_id');
        const jumlahInput      = document.getElementById('jumlah');
        const hargaRawInput    = document.getElementById('harga_raw');
        const hargaDisplay     = document.getElementById('harga_satuan');
        const totalHiddenInput = document.getElementById('total_harga');
        const totalDisplay     = document.getElementById('total_harga_display');

        if (!kategoriSelect || !jumlahInput || !hargaRawInput || !hargaDisplay || !totalHiddenInput || !totalDisplay) {
            return;
        }

        const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex] || null;
        const harga  = selectedOption ? Number(selectedOption.getAttribute('data-harga') || 0) : 0;
        const jumlah = Number(jumlahInput.value || 0);

        const total = harga * (jumlah > 0 ? jumlah : 0);

        hargaRawInput.value    = harga;
        hargaDisplay.value     = formatRupiah(harga);
        totalHiddenInput.value = total;
        totalDisplay.value     = formatRupiah(total);

        updatePelunasan();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const kategoriSelect = document.getElementById('kategori_produk_id');
        const jumlahInput    = document.getElementById('jumlah');
        const depositInput   = document.getElementById('deposit');

        if (kategoriSelect) {
            kategoriSelect.addEventListener('change', updateHargaDanTotal);
        }
        if (jumlahInput) {
            jumlahInput.addEventListener('input', updateHargaDanTotal);
        }
        if (depositInput) {
            depositInput.addEventListener('input', updatePelunasan);
        }

        // Hitung awal (kalau ada old value)
        updateHargaDanTotal();
        updatePelunasan();
    });
</script>
@endpush
