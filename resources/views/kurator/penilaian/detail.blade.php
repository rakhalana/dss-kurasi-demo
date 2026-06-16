@extends('base.app')

@section('title', 'Daftar Produk - ' . $periode->nama_periode)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('kurator.penilaian.index') }}" style="color: inherit; text-decoration: none;">Tugas Kurasi</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $periode->nama_periode }}</li>
@endsection

@section('content')
    <div class="container-fluid p-0">
        <div class="row no-gutters">
            @include('layouts.sidebar')

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-0 dashboard-main">
                @include('layouts.navbar')

                <div class="px-4 py-3 dashboard-content" data-aos="fade-up">

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                        <div>
                            <h4 class="font-weight-bold text-primary mb-1">Daftar Produk: {{ $periode->nama_periode }}</h4>
                            <p class="text-muted small mb-0">Antrean produk yang perlu dinilai pada periode ini.</p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            @if($periode->status_kurasi == 'berlangsung' || $periode->status_kurasi == 'belum')
                                @php
                                    $hasEligible = $produkList->where('status_lolos_legalitas', true)->count() > 0;
                                @endphp
                                @if($hasEligible)
                                    <a href="{{ route('kurator.penilaian.workspace', $periode->id_periode_kurasi) }}" class="btn btn-primary d-flex align-items-center font-weight-bold shadow-sm rounded-pill px-4">
                                        @if($periode->status_kurasi == 'berlangsung')
                                            <i data-lucide="play" class="mr-2"></i> Lanjutkan Penilaian
                                        @else
                                            <i data-lucide="play-circle" class="mr-2"></i> Mulai Penilaian
                                        @endif
                                    </a>
                                @else
                                    <button class="btn btn-secondary d-flex align-items-center font-weight-bold shadow-sm rounded-pill px-4" disabled>
                                        <i data-lucide="slash" class="mr-2"></i> Tidak Ada Produk Eligible
                                    </button>
                                @endif
                            @elseif($periode->status_kurasi == 'selesai')
                                <span class="badge badge-pill badge-success px-3 py-2" style="font-size: 13px;">
                                    <i data-lucide="check-circle" class="mr-1" style="width: 14px; height: 14px; vertical-align: text-bottom;"></i> Periode Telah Selesai
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="dataTable">
                                    <thead class="bg-light text-muted small uppercase tracking-wider">
                                        <tr>
                                            <th class="pl-4 py-3" style="width: 50px;">No</th>
                                            <th class="py-3">Produk & Brand</th>
                                            <th class="py-3">Pemilik</th>
                                            <th class="py-3">Syarat Wajib (Legalitas)</th>
                                            <th class="py-3">Status Penilaian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($produkList as $index => $item)
                                            @php
                                                $isEligible = $item->status_lolos_legalitas;
                                                $rowStyle = !$isEligible ? 'opacity: 0.6; background-color: #f8f9fa;' : '';
                                            @endphp
                                            <tr style="{{ $rowStyle }}">
                                                <td class="pl-4 text-muted small">{{ $index + 1 }}</td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="product-img-wrapper mr-3 shadow-sm">
                                                            @if($item->alternatif->foto_produk)
                                                                <img src="{{ Storage::disk('supabase')->url($item->alternatif->foto_produk) }}" alt="{{ $item->alternatif->nama_produk }}" class="w-100 h-100" style="object-fit: cover;">
                                                            @else
                                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                                                    <i data-lucide="package" style="width: 20px;"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $item->alternatif->nama_produk }}</h6>
                                                            <small class="text-primary font-weight-500">{{ $item->alternatif->nama_brand_umkm }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 text-muted">{{ $item->alternatif->nama_pemilik ?? '-' }}</td>
                                                <td class="py-3">
                                                    @if($isEligible)
                                                        <span class="badge badge-success px-2 py-1"><i data-lucide="check" class="mr-1" style="width: 12px; height: 12px;"></i> Lolos</span>
                                                    @else
                                                        <span class="badge badge-danger px-2 py-1"><i data-lucide="x" class="mr-1" style="width: 12px; height: 12px;"></i> Tidak Lolos</span>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    @if(!$isEligible)
                                                        <span class="text-muted small">-</span>
                                                    @elseif($item->is_dinilai)
                                                        <span class="badge badge-pill badge-primary px-3 py-2"><i data-lucide="check-circle-2" class="mr-1" style="width: 14px; height: 14px; vertical-align: text-bottom;"></i> Sudah Dinilai</span>
                                                    @else
                                                        <span class="badge badge-pill badge-light text-dark px-3 py-2 border"><i data-lucide="clock" class="mr-1" style="width: 14px; height: 14px; vertical-align: text-bottom;"></i> Belum Dinilai</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i data-lucide="package" class="mb-2" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                                                        <p class="mb-0">Belum ada data produk di periode ini.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        AOS.init({ duration: 800, once: true });

        $('#dataTable').DataTable({
            "language": {
                "search": "Cari produk:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "emptyTable": "Belum ada data produk di periode ini.",
                "paginate": {
                    "previous": "<i data-lucide='chevron-left'></i>",
                    "next": "<i data-lucide='chevron-right'></i>"
                }
            },
            pageLength: 25,
            ordering: false,
            "drawCallback": function() {
                if (window.lucide) {
                    lucide.createIcons({ icons: lucide.icons });
                }
            }
        });
    });
</script>
@endpush
