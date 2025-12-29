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

        {{-- 🔹 Tombol Tambah --}}
        <button class="btn btn-dark mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>

        {{-- 🔹 Tabel Data --}}
        <table class="table mt-3 align-middle">
            <thead class="table-dark">
                <tr>
                    <th class="text-center" style="width: 15px">No</th>
                    <th>Nama Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Stok Minimum</th>
                    <th class="text-center" style="width: 150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kategori as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->nama_kategori }}</td>
                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td>{{ number_format($item->stok, 0, ',', '.') }}</td>
                        <td>{{ number_format($item->stok_minimum, 0, ',', '.') }}</td>
                        <td class="text-center">
                            {{-- Tombol Edit --}}
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modalEdit{{ $item->id }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            {{-- Tombol Hapus --}}
                            <form action="{{ route('master-data.kategori-produk.destroy', $item->id) }}" 
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- 🔹 Modal Edit --}}
                    <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('master-data.kategori-produk.update', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Edit Kategori</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Kategori</label>
                                            <input type="text" name="nama_kategori" class="form-control" 
                                                value="{{ $item->nama_kategori }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Harga</label>
                                            <input type="number" name="harga" class="form-control" 
                                                value="{{ $item->harga }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Stok</label>
                                            <input type="number" name="stok" class="form-control"
                                                value="{{ $item->stok }}" min="0" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Stok Minimum</label>
                                            <input type="number" name="stok_minimum" class="form-control"
                                                value="{{ $item->stok_minimum }}" min="0" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Data Kategori Kosong</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- 🔹 Pagination --}}
        <div class="mt-3">
            {{ $kategori->links() }}
        </div>
    </div>
</div>

{{-- 🔹 Modal Tambah --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('master-data.kategori-produk.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" required>
                    </div>
                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="harga" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-control" min="0" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stok Minimum</label>
                    <input type="number" name="stok_minimum" class="form-control" min="0" required>
                </div>
            </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
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
