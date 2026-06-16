@extends('base.app')

@section('title', 'Kelola Produk')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.kurasi.index') }}" style="color: inherit; text-decoration: none;">Periode Kurasi</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Kelola Produk</li>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="row no-gutters">
        @include('layouts.sidebar')

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-0 dashboard-main">
            @include('layouts.navbar')

            <div class="px-4 py-3 dashboard-content" data-aos="fade-up">
                @php $isReadonly = $periode->status_kurasi !== 'belum'; @endphp
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="font-weight-bold text-primary mb-1">
                            {{ $isReadonly ? 'Daftar' : 'Kelola' }} Produk: {{ $periode->nama_periode }}
                        </h4>
                        <p class="text-muted small mb-0 mt-2">
                            @if($isReadonly)
                                Produk yang terdaftar pada periode kurasi: <strong class="text-dark">{{ $periode->nama_periode }}</strong>.
                                <span class="badge badge-{{ $periode->status_kurasi == 'selesai' ? 'success' : 'warning' }} ml-1">{{ ucfirst($periode->status_kurasi) }}</span>
                            @else
                                Pilih produk-produk yang akan dinilai pada periode kurasi: <strong class="text-dark">{{ $periode->nama_periode }}</strong>.
                            @endif
                        </p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                        <i data-lucide="check-circle" class="mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                        <i data-lucide="alert-circle" class="mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                    <form action="{{ route('admin.kurasi.produk.store', $periode->id_periode_kurasi) }}" method="POST">
                        @csrf
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-dark"><i data-lucide="list-checks" class="mr-2 text-primary" style="width: 18px;"></i>Daftar Produk (Alternatif)</h6>
                            @if(!$isReadonly)
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill font-weight-bold px-4 shadow-sm">
                                <i data-lucide="save" class="mr-2" style="width: 14px;"></i>Simpan Pilihan Produk
                            </button>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0" id="tableProdukKurasi">
                                    <thead class="bg-light text-muted small uppercase tracking-wider sticky-top">
                                        <tr>
                                            <th class="border-0 px-4 py-3" style="width: 50px;">
                                                @if(!$isReadonly)
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="checkAll">
                                                    <label class="custom-control-label" for="checkAll"></label>
                                                </div>
                                                @endif
                                            </th>
                                            <th class="border-0 py-3">Produk & Brand</th>
                                            <th class="border-0 py-3">Pemilik</th>
                                            <th class="border-0 py-3">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($alternatifs as $alt)
                                            <tr>
                                                <td class="px-4 py-3">
                                                    @if(!$isReadonly)
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input checkbox-item" 
                                                            id="check_{{ $alt->id_alternatif }}" 
                                                            name="alternatif_ids[]" 
                                                            value="{{ $alt->id_alternatif }}"
                                                            {{ in_array($alt->id_alternatif, $selectedAlternatifIds) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="check_{{ $alt->id_alternatif }}"></label>
                                                    </div>
                                                    @else
                                                        @if(in_array($alt->id_alternatif, $selectedAlternatifIds))
                                                            <i data-lucide="check-circle-2" class="text-success" style="width: 18px; height: 18px;"></i>
                                                        @else
                                                            <i data-lucide="circle" class="text-muted" style="width: 18px; height: 18px; opacity: 0.3;"></i>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="product-img-wrapper mr-3 shadow-sm">
                                                            @if($alt->foto_produk)
                                                                <img src="{{ Storage::disk('supabase')->url($alt->foto_produk) }}" alt="{{ $alt->nama_produk }}" class="w-100 h-100 object-fit-cover">
                                                            @else
                                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                                                    <i data-lucide="package" style="width: 20px;"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $alt->nama_produk }}</h6>
                                                            <small class="text-primary font-weight-500">{{ $alt->nama_brand_umkm }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3">{{ $alt->nama_pemilik ?? '-' }}</td>
                                                <td class="py-3">
                                                    @if(in_array($alt->id_alternatif, $selectedAlternatifIds))
                                                        <span class="badge badge-success px-2 py-1"><i data-lucide="check" style="width: 12px; height: 12px; margin-right: 2px;"></i> Terpilih</span>
                                                    @else
                                                        <span class="badge badge-light px-2 py-1 text-muted border">Belum Terpilih</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i data-lucide="package" class="mb-2" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                                                        <p class="mb-0">Belum ada data produk di sistem.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables with pagination disabled
        var table = $('#tableProdukKurasi').DataTable({
            "paging": false,
            "info": false,
            "ordering": false,
            "language": {
                "search": "Cari produk:",
                "emptyTable": "Belum ada data produk di sistem."
            },
            "drawCallback": function() {
                if (window.lucide) {
                    lucide.createIcons();
                }
            }
        });

        // Handle form submission to include hidden-by-search checkboxes
        $('#tableProdukKurasi').closest('form').on('submit', function(e) {
            var form = this;
            var checkedCheckboxes = table.$('.checkbox-item:checked');
            $(form).find('input[name="alternatif_ids[]"][type="hidden"]').remove();
            checkedCheckboxes.each(function() {
                if (!$.contains(document, this)) {
                    $(form).append(
                        $('<input>')
                            .attr('type', 'hidden')
                            .attr('name', 'alternatif_ids[]')
                            .val(this.value)
                    );
                }
            });
        });
    });

    // ===== Check All - Pure Vanilla JS (no jQuery, no DataTables) =====
    document.addEventListener('DOMContentLoaded', function() {
        var checkAll = document.getElementById('checkAll');
        if (!checkAll) {
            console.log('[CheckAll] Element #checkAll not found — readonly mode?');
            return;
        }

        console.log('[CheckAll] Handler registered');

        checkAll.addEventListener('change', function(e) {
            e.stopPropagation();
            var isChecked = checkAll.checked;
            var items = document.querySelectorAll('#tableProdukKurasi .checkbox-item');
            console.log('[CheckAll] Clicked, checked=' + isChecked + ', found ' + items.length + ' items');
            
            items.forEach(function(item) {
                item.checked = isChecked;
            });
        });

        // Update checkAll when individual items change
        var tableBody = document.querySelector('#tableProdukKurasi tbody');
        if (tableBody) {
            tableBody.addEventListener('change', function(e) {
                if (e.target.classList.contains('checkbox-item')) {
                    var items = document.querySelectorAll('#tableProdukKurasi .checkbox-item');
                    var allChecked = true;
                    items.forEach(function(item) {
                        if (!item.checked) allChecked = false;
                    });
                    checkAll.checked = allChecked;
                }
            });
        }
    });
</script>
@endpush

