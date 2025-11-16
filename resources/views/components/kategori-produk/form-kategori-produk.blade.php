<div>
    {{-- Styling kecil khusus modal kategori biar seirama dengan form transaksi --}}
    <style>
        .modal-kategori-wrapper .form-label {
            font-size: 0.85rem;
        }

        .modal-kategori-wrapper .form-control,
        .modal-kategori-wrapper .input-group-text {
            font-size: 0.85rem;
        }

        .modal-kategori-wrapper small {
            font-size: 0.75rem;
        }

        /* Tombol trigger: putih + outline biru, hover jadi biru, rounded */
        .btn-kategori-trigger {
            border-radius: 999px;
            background-color: #ffffff;
            border-width: 1px;
        }

        .btn-kategori-trigger:hover {
            color: #ffffff;
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
    </style>

    {{-- Tombol buka modal --}}
    <button
        type="button"
        class="btn btn-outline-primary btn-kategori-trigger px-3 py-1"
        data-bs-toggle="modal"
        data-bs-target="#modalKategori{{ $id ?? 'Baru' }}"
    >
        @if (isset($id))
            <i class="fas fa-edit"></i>
        @else
            <span>Kategori Baru</span>
        @endif
    </button>

    {{-- Modal Kategori (Create / Edit pakai $action dari component) --}}
    <div class="modal fade" id="modalKategori{{ $id ?? 'Baru' }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ $action }}" method="POST">
                @csrf
                @if(isset($id))
                    @method('PUT')
                @endif

                <div class="modal-content border-0 shadow-sm">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            {{ isset($id) ? 'Edit Kategori' : 'Tambah Kategori' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body modal-kategori-wrapper">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="nama_kategori_{{ $id ?? 'baru' }}">Nama Kategori</label>
                            <input
                                type="text"
                                name="nama_kategori"
                                id="nama_kategori_{{ $id ?? 'baru' }}"
                                class="form-control"
                                placeholder="Contoh: Cuci Kering"
                                value="{{ old('nama_kategori', $nama_kategori) }}"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="harga_{{ $id ?? 'baru' }}">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input
                                    type="number"
                                    name="harga"
                                    id="harga_{{ $id ?? 'baru' }}"
                                    class="form-control"
                                    min="0"
                                    step="1000"
                                    placeholder="0"
                                    value="{{ old('harga', $harga) }}"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light border rounded-pill px-3"
                            data-bs-dismiss="modal"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary rounded-pill px-3"
                        >
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
