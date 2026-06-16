<div class="modal fade" id="modalEditProduk-{{ $item->id_alternatif }}" tabindex="-1" role="dialog" aria-labelledby="modalEditProdukLabel-{{ $item->id_alternatif }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header modal-header--gradient pt-4 px-4">
                <h5 class="modal-title font-weight-bold">
                    <i data-lucide="edit-3" class="mr-2"></i>Ubah Info Produk
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.produk.update', $item->id_alternatif) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="form-group mb-3 text-center">
                                <label class="text-muted small font-weight-bold uppercase tracking-wider d-block text-left">Foto Produk</label>
                                <div class="product-preview mx-auto rounded shadow-sm overflow-hidden mb-3 bg-white" style="width: 150px; height: 150px; border: 3px solid #f8f9fa;">
                                    @if($item->foto_produk)
                                        <img src="{{ Storage::disk('supabase')->url($item->foto_produk) }}" id="preview-{{ $item->id_alternatif }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted" id="preview-placeholder-{{ $item->id_alternatif }}">
                                            <i data-lucide="image" style="width: 40px; height: 40px;"></i>
                                        </div>
                                        <img src="" id="preview-{{ $item->id_alternatif }}" class="w-100 h-100 object-fit-cover d-none">
                                    @endif
                                </div>
                                <div class="custom-file mb-1">
                                    <input type="file" class="custom-file-input" id="foto_produk_edit-{{ $item->id_alternatif }}" name="foto_produk" accept="image/*" onchange="previewImage(this, '{{ $item->id_alternatif }}')">
                                    <label class="custom-file-label rounded-pill border-light bg-light text-truncate" for="foto_produk_edit-{{ $item->id_alternatif }}">Ubah Foto...</label>
                                </div>
                                <small class="text-muted d-block text-left mt-1">Format: JPG, PNG. Maks: 2MB</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="text-muted small font-weight-bold uppercase tracking-wider">Nama Produk</label>
                                <input type="text" class="form-control rounded-pill border-light bg-light px-3" name="nama_produk" value="{{ $item->nama_produk }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-muted small font-weight-bold uppercase tracking-wider">Nama Brand / UMKM</label>
                                <input type="text" class="form-control rounded-pill border-light bg-light px-3" name="nama_brand_umkm" value="{{ $item->nama_brand_umkm }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-muted small font-weight-bold uppercase tracking-wider">Nama Pemilik</label>
                                <input type="text" class="form-control rounded-pill border-light bg-light px-3" name="nama_pemilik" value="{{ $item->nama_pemilik }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-muted small font-weight-bold uppercase tracking-wider">Deskripsi</label>
                                <textarea class="form-control border-light bg-light px-3" name="deskripsi_produk" rows="3" style="border-radius: 12px;">{{ $item->deskripsi_produk }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(input, id) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-' + id).attr('src', e.target.result).removeClass('d-none');
                $('#preview-placeholder-' + id).addClass('d-none');
            }
            reader.readAsDataURL(input.files[0]);
            
            var fileName = input.files[0].name;
            $(input).next('.custom-file-label').addClass("selected").html(fileName);
        }
    }
</script>
@endpush
