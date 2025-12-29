@extends('layouts.dashboard')

@section('title', $pageTitle ?? 'Input Stok Masuk')

@section('content')
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
            <h4 class="card-title mb-0">Transaksi Stok Masuk</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('transaksi.stok-masuk.laporan') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-boxes-stacked me-1"></i> Laporan Stok Masuk
                </a>
                <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-list me-1"></i> Laporan Transaksi
                </a>
            </div>
        </div>

        <div class="card-body py-4 form-transaksi-wrapper">
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

            <form action="{{ route('transaksi.stok-masuk.store') }}" method="POST">
                @csrf

                <h6 class="fw-bold text-uppercase text-muted mb-3">Info Stok Masuk</h6>
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
                        <input type="text" class="form-control" value="Stok Masuk" readonly>
                    </div>
                </div>

                <hr>

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
                                    data-stok="{{ $kategori->stok }}"
                                    {{ old('kategori_produk_id') == $kategori->id ? 'selected' : '' }}
                                >
                                    {{ $kategori->nama_kategori }} (Stok: {{ number_format($kategori->stok, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_produk_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="stok_saat_ini" class="form-label fw-semibold">Stok Saat Ini</label>
                        <input type="text" id="stok_saat_ini" class="form-control" value="0" readonly>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="jumlah" class="form-label fw-semibold">Jumlah Masuk</label>
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
                    <div class="col-md-4">
                        <label for="harga_satuan" class="form-label fw-semibold">Harga Satuan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                type="number"
                                name="harga_satuan"
                                id="harga_satuan"
                                class="form-control @error('harga_satuan') is-invalid @enderror"
                                value="{{ old('harga_satuan', 0) }}"
                                min="0"
                                required
                            >
                        </div>
                        @error('harga_satuan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="total_harga_display" class="form-label fw-semibold">Estimasi Total</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="total_harga_display" class="form-control" value="0" readonly>
                        </div>
                        <input type="hidden" name="total_harga" id="total_harga" value="{{ old('total_harga', 0) }}">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-12">
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
                        <i class="fas fa-save me-1"></i> Simpan Stok Masuk
                    </button>
                </div>
            </form>
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

    function formatRupiah(angka) {
        angka = Number(angka) || 0;
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(angka);
    }

    function updateStokMasuk() {
        const kategoriSelect = document.getElementById('kategori_produk_id');
        const jumlahInput = document.getElementById('jumlah');
        const hargaInput = document.getElementById('harga_satuan');
        const stokDisplay = document.getElementById('stok_saat_ini');
        const totalDisplay = document.getElementById('total_harga_display');
        const totalHidden = document.getElementById('total_harga');

        if (!kategoriSelect || !jumlahInput || !hargaInput || !stokDisplay || !totalDisplay || !totalHidden) {
            return;
        }

        const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex] || null;
        const stok = selectedOption ? Number(selectedOption.getAttribute('data-stok') || 0) : 0;
        const harga = Number(hargaInput.value || 0);
        const jumlah = Number(jumlahInput.value || 0);

        const total = harga * (jumlah > 0 ? jumlah : 0);

        stokDisplay.value = formatRupiah(stok);
        totalDisplay.value = formatRupiah(total);
        totalHidden.value = total;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const kategoriSelect = document.getElementById('kategori_produk_id');
        const jumlahInput = document.getElementById('jumlah');
        const hargaInput = document.getElementById('harga_satuan');

        if (kategoriSelect) {
            kategoriSelect.addEventListener('change', updateStokMasuk);
        }
        if (jumlahInput) {
            jumlahInput.addEventListener('input', updateStokMasuk);
        }
        if (hargaInput) {
            hargaInput.addEventListener('input', updateStokMasuk);
        }

        updateStokMasuk();
    });
</script>
@endpush
