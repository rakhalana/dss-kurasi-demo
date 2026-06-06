<div class="modal fade" id="modalAddProduk" tabindex="-1" role="dialog" aria-labelledby="modalAddProdukLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header modal-header--gradient pt-4 px-4">
                <h5 class="modal-title font-weight-bold">
                    <i data-lucide="plus-circle" class="mr-2"></i>Tambah Produk Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="form-group mb-3 text-center">
                                <label
                                    class="text-muted small font-weight-bold uppercase tracking-wider d-block text-left">Foto
                                    Produk</label>
                                <div class="product-add-preview mx-auto rounded shadow-sm overflow-hidden mb-3"
                                    style="width: 150px; height: 150px; background: #f8f9fa; border: 2px dashed #dee2e6;">
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"
                                        id="add-preview-placeholder">
                                        <i data-lucide="image-plus"
                                            style="width: 48px; height: 48px; opacity: 0.5;"></i>
                                    </div>
                                    <img src="" id="add-preview-img" class="w-100 h-100 object-fit-cover d-none">
                                </div>
                                <div class="custom-file mb-1">
                                    <input type="file"
                                        class="custom-file-input @error('foto_produk') is-invalid @enderror"
                                        id="foto_produk_add" name="foto_produk" accept="image/*">
                                    <label class="custom-file-label rounded-pill border-light bg-light text-truncate"
                                        for="foto_produk_add">Pilih Foto...</label>
                                    @error('foto_produk')
                                        <div class="invalid-feedback text-left">
                                            @if(str_contains($message, 'must not be greater than 2048 kilobytes'))
                                                Ukuran foto produk tidak boleh lebih dari 2 MB.
                                            @else
                                                {{ $message }}
                                            @endif
                                        </div>
                                    @enderror
                                </div>
                                <small class="text-muted d-block text-left mt-1">Format: JPG, PNG. Maks: 2MB</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="text-muted small font-weight-bold uppercase tracking-wider">Nama
                                    Produk</label>
                                <input type="text"
                                    class="form-control rounded-pill border-light bg-light px-3 @error('nama_produk') is-invalid @enderror"
                                    name="nama_produk" value="{{ old('nama_produk') }}"
                                    placeholder="Contoh: Kripik Tempe Renyah" required>
                                @error('nama_produk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="text-muted small font-weight-bold uppercase tracking-wider">Nama Brand /
                                    UMKM</label>
                                <input type="text"
                                    class="form-control rounded-pill border-light bg-light px-3 @error('nama_brand_umkm') is-invalid @enderror"
                                    name="nama_brand_umkm" value="{{ old('nama_brand_umkm') }}"
                                    placeholder="Contoh: UMKM Berkah Jaya" required>
                                @error('nama_brand_umkm')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-muted small font-weight-bold uppercase tracking-wider">Nama
                                    Pemilik</label>
                                <input type="text"
                                    class="form-control rounded-pill border-light bg-light px-3 @error('nama_pemilik') is-invalid @enderror"
                                    name="nama_pemilik" value="{{ old('nama_pemilik') }}"
                                    placeholder="Nama lengkap pemilik" required>
                                @error('nama_pemilik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-muted small font-weight-bold uppercase tracking-wider">Deskripsi
                                    Produk</label>
                                <textarea
                                    class="form-control border-light bg-light px-3 @error('deskripsi_produk') is-invalid @enderror"
                                    name="deskripsi_produk" rows="4" style="border-radius: 12px;"
                                    placeholder="Jelaskan secara singkat mengenai produk ini...">{{ old('deskripsi_produk') }}</textarea>
                                @error('deskripsi_produk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $('#foto_produk_add').on('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#add-preview-img').attr('src', e.target.result).removeClass('d-none');
                    $('#add-preview-placeholder').addClass('d-none');
                }
                reader.readAsDataURL(this.files[0]);

                var fileName = this.files[0].name;
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            }
        });

        // Auto-open modal if there are errors (optional, but requested in many cases)
        @if($errors->any() && old('nama_produk'))
            $(document).ready(function () {
                $('#modalAddProduk').modal('show');
            });
        @endif
    </script>
@endpush