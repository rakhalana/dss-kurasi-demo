@extends('base.app')

@section('title', 'Manajemen Kriteria & Parameter')

@section('content')
    <div class="container-fluid p-0">
        <div class="row no-gutters">
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Content -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-0 dashboard-main">
                @include('layouts.navbar')

                <div class="px-4 py-3 dashboard-content" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="font-weight-bold text-primary mb-1">Manajemen Kriteria & Parameter</h4>
                            <p class="text-muted small mb-0">Konfigurasi kriteria penilaian dan aktifkan/nonaktifkan skala
                                penilaian.</p>
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

                    <div class="row">
                        @foreach ($kriteria as $item)
                            <div class="col-12">
                                <div class="card card-kriteria border-0 shadow-sm overflow-hidden">
                                    <div
                                        class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center border-bottom-0">
                                        <div class="d-flex align-items-center kriteria-info mr-3">
                                            <div class="kriteria-code-badge mr-3">
                                                <span>{{ $item->kode_kriteria }}</span>
                                            </div>
                                            <div class="kriteria-text">
                                                <h6 class="mb-1 font-weight-bold text-dark text-truncate-custom">
                                                    {{ $item->nama_kriteria }}</h6>
                                                <span class="aspek-chip aspek-{{ str_replace('_', '-', $item->aspek) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $item->aspek)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mt-2 mt-sm-0">
                                            <span class="target-pill mr-3">
                                                Nilai Target: {{ $item->target_nilai }}
                                            </span>
                                            <button class="btn btn-sm btn-light btn-collapse-trigger shadow-sm collapsed mr-2"
                                                type="button" data-toggle="collapse"
                                                data-target="#collapse-{{ $item->id_kriteria }}" aria-expanded="false">
                                                <i data-lucide="chevron-down" style="width: 16px; height: 16px;"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary px-3 rounded-pill" data-toggle="modal"
                                                data-target="#modalEdit-{{ $item->id_kriteria }}">
                                                <i data-lucide="pencil" class="mr-sm-1"
                                                    style="width: 13px; height: 13px;"></i><span
                                                    class="d-none d-sm-inline">Edit</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="collapse" id="collapse-{{ $item->id_kriteria }}">
                                        <div class="card-body pt-0">
                                            <div class="p-3 mb-3 bg-light rounded-lg text-muted small"
                                                style="border-left: 4px solid #4a90e2; font-size: 0.82rem; line-height: 1.5;">
                                                {{ $item->deskripsi_kriteria }}
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-hover table-borderless align-middle mb-0">
                                                    <thead class="text-muted small uppercase tracking-wider">
                                                        <tr>
                                                            <th style="width: 150px;">Nilai Skala</th>
                                                            <th>Deskripsi Parameter / Skala</th>
                                                            <th style="width: 120px;" class="text-center">Status</th>
                                                            <th style="width: 120px;" class="text-center">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($item->scales as $scale)
                                                            <tr class="border-top">
                                                                <td>
                                                                    <div class="scale-badge-pill">
                                                                        Skala {{ $scale->nilai_skala }}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="text-sm {{ !$scale->is_aktif ? 'text-muted text-strikethrough' : '' }}">{{ $scale->deskripsi_skala }}</span>
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($scale->is_aktif)
                                                                        <span class="status-pill status-active">
                                                                            <span class="status-dot"></span>Aktif
                                                                        </span>
                                                                    @else
                                                                        <span class="status-pill status-inactive">
                                                                            <span class="status-dot"></span>Non-aktif
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                                                        data-toggle="modal"
                                                                        data-target="#modalEditSkala-{{ $item->id_kriteria }}-{{ $scale->nilai_skala }}"
                                                                        title="Edit Skala">
                                                                        <i data-lucide="pencil"
                                                                            style="width: 13px; height: 13px;"></i>
                                                                        <span class="d-none d-md-inline ml-1"
                                                                            style="font-size: 0.78rem;">Edit</span>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endforeach
                    </div>
                </div>

                @foreach ($kriteria as $item)
                    @include('modal.kriteria.edit', ['item' => $item])
                    @foreach ($item->scales as $scale)
                        @include('modal.kriteria.skala', ['item' => $item, 'scale' => $scale])
                    @endforeach
                @endforeach
            </main>
        </div>
    </div>
@endsection



@push('scripts')
    <script>
        $(document).ready(function () {
            AOS.init({
                duration: 800,
                once: true
            });

            // Re-initialize Lucide icons after modals are shown
            $(document).on('shown.bs.modal', function () {
                lucide.createIcons();
            });
        });
    </script>
@endpush