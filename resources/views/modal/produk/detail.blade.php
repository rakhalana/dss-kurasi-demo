<div class="modal fade" id="modalDetailProduk-{{ $item->id_alternatif }}" tabindex="-1" role="dialog"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header modal-header--gradient pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-primary">
                    <i data-lucide="package" class="mr-2"></i>Detail Produk
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="row no-gutters">
                    <!-- Left: Image & Quick Stats -->
                    <div
                        class="col-md-5 bg-light p-4 d-flex flex-column align-items-center justify-content-center border-right">
                        <div class="product-detail-img shadow-sm rounded-lg overflow-hidden mb-4 bg-white"
                            style="width: 220px; height: 220px; border: 5px solid #fff;">
                            @if($item->foto_produk)
                                <img src="{{ Storage::disk('supabase')->url($item->foto_produk) }}"
                                    class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                    <i data-lucide="package" style="width: 80px; height: 80px; opacity: 0.2;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="w-100">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Data Legalitas:</span>
                                @if($item->is_aktif)
                                    <span class="badge badge-pill badge-success px-3">Sudah diisi</span>
                                @else
                                    <span class="badge badge-pill badge-warning px-3 text-white">Belum diisi</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between mb-0">
                                <span class="text-muted small">Verifikasi Legalitas:</span>
                                @if($item->legalitas && $item->legalitas->lolos_filter)
                                    <span class="badge badge-pill badge-success px-3">Lolos</span>
                                @else
                                    <span class="badge badge-pill badge-danger px-3">Tidak Lolos</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right: Info Details -->
                    <div class="col-md-7 p-4">
                        <div class="mb-4">
                            <h4 class="font-weight-bold text-dark mb-1">{{ $item->nama_produk }}</h4>
                            <p class="text-primary font-weight-600 mb-2">{{ $item->nama_brand_umkm }}</p>
                            <hr class="my-3">
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-5 text-muted small uppercase font-weight-bold tracking-wider">Nama
                                Pemilik</div>
                            <div class="col-sm-7 text-dark font-weight-500">{{ $item->nama_pemilik }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 text-muted small uppercase font-weight-bold tracking-wider mb-2">
                                Deskripsi Produk</div>
                            <div class="col-12 text-dark bg-light p-3 rounded-lg border small mb-3"
                                style="min-height: 80px;">
                                {{ $item->deskripsi_produk ?: 'Tidak ada deskripsi.' }}
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12 text-muted small uppercase font-weight-bold tracking-wider mb-2">
                                Daftar Dokumen Legalitas</div>
                            <div class="col-12 d-flex flex-column gap-2" style="gap: 8px;">
                                @if($item->legalitas && ($item->legalitas->is_nib || $item->legalitas->is_sertifikat_halal || $item->legalitas->is_bpom || $item->legalitas->is_sp_pirt))
                                    @if($item->legalitas->is_nib)
                                        <div
                                            class="d-flex justify-content-between align-items-center bg-white p-2 px-3 border rounded-pill shadow-xs">
                                            <span class="small font-weight-600 text-dark">NIB</span>
                                            <span
                                                class="small text-primary font-weight-bold">{{ $item->legalitas->no_nib ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($item->legalitas->is_sertifikat_halal)
                                        <div
                                            class="d-flex justify-content-between align-items-center bg-white p-2 px-3 border rounded-pill shadow-xs">
                                            <span class="small font-weight-600 text-dark">Sertifikat Halal</span>
                                            <span
                                                class="small text-primary font-weight-bold">{{ $item->legalitas->no_sertifikat_halal ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($item->legalitas->is_bpom)
                                        <div
                                            class="d-flex justify-content-between align-items-center bg-white p-2 px-3 border rounded-pill shadow-xs">
                                            <span class="small font-weight-600 text-dark">BPOM</span>
                                            <span
                                                class="small text-primary font-weight-bold">{{ $item->legalitas->no_bpom ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($item->legalitas->is_sp_pirt)
                                        <div
                                            class="d-flex justify-content-between align-items-center bg-white p-2 px-3 border rounded-pill shadow-xs">
                                            <span class="small font-weight-600 text-dark">SP-PIRT</span>
                                            <span
                                                class="small text-primary font-weight-bold">{{ $item->legalitas->no_sp_pirt ?: '-' }}</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center p-3 bg-light rounded-lg border border-dashed">
                                        <span class="text-muted small">Belum ada dokumen yang diunggah.</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-wrap pt-2">
                            <button class="btn btn-outline-primary rounded-pill px-4 py-2 mr-2 mb-2 shadow-sm"
                                data-toggle="modal" data-target="#modalEditProduk-{{ $item->id_alternatif }}"
                                data-dismiss="modal">
                                <i data-lucide="edit-3" class="mr-2"></i>Ubah Data
                            </button>
                            <button class="btn btn-outline-danger rounded-pill px-4 py-2 mb-2 shadow-sm"
                                data-toggle="modal" data-target="#modalDeleteProduk-{{ $item->id_alternatif }}"
                                data-dismiss="modal">
                                <i data-lucide="trash-2" class="mr-2"></i>Hapus Produk
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white pt-2">
                <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm"
                    data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>