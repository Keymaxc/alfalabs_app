<div>
    <!-- Button trigger modal -->
    <button type="button" class="btn {{ isset($id) ? 'btn-primary btn-icon' : 'btn-dark' }}" data-bs-toggle="modal"
        data-bs-target="#formKategori{{ $id ?? '' }}">
        @if (isset($id))
            <i class="fas fa-edit"></i>
        @else
            <span>Kategori Baru</span>
        @endif
    </button>

    <div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formKategori">
                @csrf
                <input type="hidden" id="kategori_id">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">Tambah Kategori</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" id="nama_kategori" class="form-control">
                            <small class="text-danger" id="error_nama_kategori"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" id="harga" class="form-control">
                            <small class="text-danger" id="error_harga"></small>
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
</div>
