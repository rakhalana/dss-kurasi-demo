@extends('base.app')

@section('title', 'Workspace Penilaian - ' . $produkAktif->alternatif->nama_alternatif)

@section('class-body', 'workspace-page')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('kurator.penilaian.index') }}" style="color: inherit; text-decoration: none;">Tugas Kurasi</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('kurator.penilaian.detail', $periode->id_periode_kurasi) }}" style="color: inherit; text-decoration: none;">{{ $periode->nama_periode }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Penilaian Produk</li>
@endsection

@section('content')
    <div class="container-fluid p-0">
        <div class="row no-gutters">
            @include('layouts.sidebar')

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-0 workspace-main">
                @include('layouts.navbar')

                <div class="row no-gutters workspace-container">
                    
                    <!-- Panel Kiri: Form Penilaian -->
                    <div class="col-lg-9 panel-kiri" id="panelKiri">
                        @if($semuaDinilai)
                        <!-- State: Semua Produk Sudah Dinilai -->
                        <div class="panel-kiri-content d-flex align-items-center justify-content-center p-4">
                            <div class="text-center" style="max-width: 440px; width: 100%;">
                                {{-- Icon --}}
                                <div class="mb-3">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background: linear-gradient(135deg, #28a745, #20c997);">
                                        <i data-lucide="check" class="text-white" style="width: 36px; height: 36px;"></i>
                                    </div>
                                </div>

                                {{-- Heading --}}
                                <h4 class="font-weight-bold mb-2">Semua Produk Telah Dinilai</h4>
                                <p class="text-muted small mb-4 mx-auto-420">
                                    Semua produk dalam periode ini telah selesai Anda berikan penilaian. 
                                    Anda dapat meninjau ulang penilaian melalui panel antrean di samping kanan.
                                </p>

                                {{-- Buttons --}}
                                <div class="mx-auto-260">
                                    <a href="{{ route('kurator.penilaian.detail', $periode->id_periode_kurasi) }}" class="btn btn-primary btn-block btn-rounded font-weight-bold py-2 mb-2">
                                        <i data-lucide="clipboard-check" class="mr-2" style="width: 16px; height: 16px;"></i> Lihat Ringkasan Kurasi
                                    </a>
                                    <a href="{{ route('kurator.penilaian.index') }}" class="btn btn-outline-secondary btn-block btn-rounded font-weight-bold py-2">
                                        <i data-lucide="arrow-left" class="mr-2" style="width: 16px; height: 16px;"></i> Kembali ke Daftar
                                    </a>
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Scrollable Content -->
                        <div class="panel-kiri-content p-4">
                            <!-- Header Produk -->
                            <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                                <div class="product-img-wrapper product-img-wrapper--lg mr-3 border d-flex align-items-center justify-content-center">
                                    @if($produkAktif->alternatif->foto_produk)
                                        <img src="{{ asset('storage/' . $produkAktif->alternatif->foto_produk) }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <i data-lucide="image" class="text-muted" style="width: 32px; height: 32px;"></i>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="font-weight-bold mb-1">{{ $produkAktif->alternatif->nama_produk }}</h3>
                                    <div class="text-muted d-flex align-items-center">
                                        <span class="mr-3"><i data-lucide="tag" style="width: 14px; height: 14px; margin-right: 4px;"></i> {{ $produkAktif->alternatif->nama_brand_umkm }}</span>
                                        <span><i data-lucide="user" style="width: 14px; height: 14px; margin-right: 4px;"></i> {{ $produkAktif->alternatif->nama_pemilik ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Kriteria -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold mb-0 text-primary">Kriteria Penilaian</h6>
                                <span class="badge badge-primary px-3 py-2" style="border-radius: 20px;" id="kriteriaProgressText">1 / {{ count($kriteriaList) }}</span>
                            </div>
                            <div class="progress mb-4" style="height: 6px; border-radius: 3px;">
                                <div class="progress-bar bg-primary transition-all" id="kriteriaProgressBar" role="progressbar" style="width: {{ 100 / count($kriteriaList) }}%;"></div>
                            </div>

                            <!-- Form Wizard -->
                            <form id="formPenilaian" onsubmit="return false;">
                                @foreach($kriteriaList as $index => $kriteria)
                                    @php
                                        $existingValue = isset($penilaianExisting[$kriteria->id_kriteria]) ? $penilaianExisting[$kriteria->id_kriteria]->nilai_input : null;
                                    @endphp
                                    <div class="kriteria-card {{ $index == 0 ? 'active' : '' }}" data-step="{{ $index + 1 }}" data-kriteria-id="{{ $kriteria->id_kriteria }}">
                                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                                <h4 class="font-weight-bold mb-2">{{ $kriteria->nama_kriteria }}</h4>
                                                <p class="text-muted">{{ $kriteria->deskripsi ?? 'Pilih salah satu skala penilaian yang paling sesuai dengan kondisi aktual produk.' }}</p>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($kriteria->scales as $skala)
                                                        <div class="col-12 mb-3">
                                                            <label class="w-100 m-0">
                                                                <div class="skala-option p-3 d-flex align-items-center {{ $existingValue == $skala->nilai_skala ? 'selected' : '' }}">
                                                                    <input type="radio" class="skala-radio" name="kriteria_{{ $kriteria->id_kriteria }}" value="{{ $skala->nilai_skala }}" {{ $existingValue == $skala->nilai_skala ? 'checked' : '' }}>
                                                                    <div class="d-flex align-items-center justify-content-center bg-light rounded-circle mr-3 border" style="width: 40px; height: 40px; min-width: 40px;">
                                                                        <span class="font-weight-bold {{ $existingValue == $skala->nilai_skala ? 'text-primary' : 'text-dark' }}">{{ $skala->nilai_skala }}</span>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <span class="d-block text-dark">{{ $skala->deskripsi_skala }}</span>
                                                                    </div>
                                                                    <div class="check-icon text-primary ml-2" style="display: {{ $existingValue == $skala->nilai_skala ? 'block' : 'none' }};">
                                                                        <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <!-- Step Catatan -->
                                <div class="kriteria-card" data-step="{{ count($kriteriaList) + 1 }}" data-kriteria-id="komentar">
                                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                            <h4 class="font-weight-bold mb-2">Catatan / Komentar Kurator</h4>
                                            <p class="text-muted">Berikan catatan atau komentar keseluruhan terhadap produk ini (Opsional).</p>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <textarea id="catatanKurator" class="form-control" rows="5" placeholder="Tulis catatan Anda di sini...">{{ $produkAktif->catatan_kurator ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Sticky Footer Buttons -->
                        <div class="panel-kiri-footer d-flex justify-content-between px-4 py-3 border-top" id="wizardButtons">
                            <button type="button" class="btn btn-outline-secondary btn-rounded font-weight-bold px-4 py-2" id="btnPrev" style="display: none;">
                                <i data-lucide="chevron-left" class="mr-1"></i> Sebelumnya
                            </button>
                            <button type="button" class="btn btn-primary btn-rounded font-weight-bold px-4 py-2 ml-auto" id="btnNext" disabled>
                                Selanjutnya <i data-lucide="chevron-right" class="ml-1"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-rounded font-weight-bold px-4 py-2 ml-auto" id="btnSimpan" style="display: none;" disabled>
                                <i data-lucide="save" class="mr-1"></i> Simpan & Lanjut
                            </button>
                        </div>
                        @endif
                    </div>

                    <!-- Panel Kanan: Daftar Antrean -->
                    <div class="col-lg-3 panel-kanan d-none d-lg-flex border-left">
                        <div class="panel-kanan-header px-3 pt-4 pb-2">
                            <h6 class="font-weight-bold mb-0 text-muted text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Antrean Produk</h6>
                        </div>
                        
                        <div class="antrean-list px-3 py-2">
                            @foreach($antreanProduk as $index => $item)
                                <a href="{{ route('kurator.penilaian.workspace', ['id_periode' => $periode->id_periode_kurasi, 'id_alternatif' => $item->id_alternatif]) }}" 
                                   class="produk-list-item d-flex align-items-center p-2 text-dark {{ $item->id_alternatif == $produkAktif->id_alternatif ? 'active' : '' }}" id="nav-item-{{$item->id_alternatif}}">
                                    <div class="mr-2 font-weight-bold text-muted" style="min-width: 18px; font-size: 12px;">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-0 text-truncate font-weight-bold" style="font-size: 13px;">{{ $item->alternatif->nama_produk }}</h6>
                                        <span class="text-muted text-truncate d-block" style="font-size: 11px;">{{ $item->alternatif->nama_brand_umkm }}</span>
                                    </div>
                                    <div class="ml-1 status-badge">
                                        @if($item->is_dinilai)
                                            <i data-lucide="check-circle-2" class="text-success" style="width: 16px; height: 16px;"></i>
                                        @else
                                            <i data-lucide="circle" class="text-muted" style="opacity: 0.3; width: 16px; height: 16px;"></i>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="panel-kanan-footer px-3 py-3 border-top">
                            <button type="button" class="btn btn-outline-primary btn-block btn-rounded font-weight-bold py-2" style="font-size: 13px;" data-toggle="modal" data-target="#modalSelesaikanKurasi">
                                Selesaikan Kurasi <i data-lucide="check-square" class="ml-1"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
@endsection

@push('modal')
    @include('modal.penilaian.selesaikan')
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        @if(!$semuaDinilai)
        const totalSteps = {{ count($kriteriaList) + 1 }};
        let currentStep = 1;
        const idPeriode = Number("{{ $periode->id_periode_kurasi }}");
        const idAlternatif = Number("{{ $produkAktif->id_alternatif }}");
        const antrean = @json($antreanProduk->pluck('id_alternatif')->toArray()).map(Number);
        let isSaving = false;

        // Route URL Templates generated by Laravel's route helper to support subdirectories
        const workspaceUrlTemplate = "{{ route('kurator.penilaian.workspace', ['id_periode' => $periode->id_periode_kurasi, 'id_alternatif' => ':id']) }}";
        const workspaceBaseUrl = "{{ route('kurator.penilaian.workspace', ['id_periode' => $periode->id_periode_kurasi]) }}";
        const saveUrlTemplate = "{{ route('kurator.penilaian.store', ['id_periode' => $periode->id_periode_kurasi, 'id_alternatif' => $produkAktif->id_alternatif, 'id_kriteria' => ':kriteria_id']) }}";
        
        // Setup CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize UI
        updateWizardUI();

        // Handle Skala Option Click
        $('.skala-option').on('click', function() {
            $(this).closest('.row').find('.skala-option').removeClass('selected');
            $(this).closest('.row').find('.check-icon').hide();
            $(this).closest('.row').find('span.font-weight-bold').removeClass('text-primary').addClass('text-dark');
            
            $(this).addClass('selected');
            $(this).find('.check-icon').show();
            $(this).find('span.font-weight-bold').removeClass('text-dark').addClass('text-primary');
            $(this).find('input[type="radio"]').prop('checked', true);
            
            checkStepValidity();
        });

        // Next Button Click
        $('#btnNext').on('click', function() {
            if (currentStep < totalSteps && !isSaving) {
                $(this).prop('disabled', true);
                saveCurrentStep().then(() => {
                    currentStep++;
                    updateWizardUI();
                }).catch(() => {
                    $(this).prop('disabled', false);
                });
            }
        });

        // Prev Button Click
        $('#btnPrev').on('click', function() {
            if (currentStep > 1) {
                currentStep--;
                updateWizardUI();
            }
        });

        // Simpan & Lanjut Click (Last Step)
        $('#btnSimpan').on('click', function() {
            if (isSaving) return;
            
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-2"></span> Menyimpan...');
            
            saveCurrentStep().then(() => {
                // Update nav item visually
                $(`#nav-item-${idAlternatif} .status-badge`).html('<i data-lucide="check-circle-2" class="text-success" style="width: 16px; height: 16px;"></i>');
                lucide.createIcons();
                
                // Navigate to next product or show completion
                const currentIndex = antrean.indexOf(idAlternatif);
                if (currentIndex !== -1 && currentIndex < antrean.length - 1) {
                    const nextId = antrean[currentIndex + 1];
                    const nextUrl = decodeURIComponent(workspaceUrlTemplate).replace(':id', nextId);
                    window.location.href = nextUrl;
                } else {
                    // All products assessed - go to workspace without ID to trigger semuaDinilai
                    window.location.href = workspaceBaseUrl;
                }
            }).catch(() => {
                $btn.prop('disabled', false).html('Simpan & Lanjut <i data-lucide="save" class="ml-2"></i>');
                lucide.createIcons();
                alert('Gagal menyimpan. Silakan coba lagi.');
            });
        });

        function updateWizardUI() {
            $('.kriteria-card').removeClass('active');
            $(`.kriteria-card[data-step="${currentStep}"]`).addClass('active');

            const percent = (currentStep / totalSteps) * 100;
            $('#kriteriaProgressBar').css('width', `${percent}%`);
            $('#kriteriaProgressText').text(`${currentStep} / ${totalSteps}`);

            if (currentStep === 1) {
                $('#btnPrev').hide();
            } else {
                $('#btnPrev').show();
            }

            if (currentStep === totalSteps) {
                $('#btnNext').hide();
                $('#btnSimpan').show();
            } else {
                $('#btnNext').show();
                $('#btnSimpan').hide();
            }

            checkStepValidity();
            
            // Scroll to top of panel kiri content area
            $('.panel-kiri-content').animate({ scrollTop: 0 }, 200);
        }

        function checkStepValidity() {
            const currentCard = $(`.kriteria-card[data-step="${currentStep}"]`);
            
            if (currentCard.data('kriteria-id') === 'komentar') {
                $('#btnNext').prop('disabled', false);
                $('#btnSimpan').prop('disabled', false);
                return;
            }

            const isChecked = currentCard.find('input[type="radio"]:checked').length > 0;
            
            if (isChecked) {
                $('#btnNext').prop('disabled', false);
                $('#btnSimpan').prop('disabled', false);
            } else {
                $('#btnNext').prop('disabled', true);
                $('#btnSimpan').prop('disabled', true);
            }
        }

        function saveCurrentStep() {
            isSaving = true;
            return new Promise((resolve, reject) => {
                const currentCard = $(`.kriteria-card[data-step="${currentStep}"]`);
                const idKriteria = currentCard.data('kriteria-id');

                if (idKriteria === 'komentar') {
                    const catatan = $('#catatanKurator').val();
                    const saveKomentarUrl = "{{ route('kurator.penilaian.komentar', ['id_periode' => $periode->id_periode_kurasi, 'id_alternatif' => $produkAktif->id_alternatif]) }}";
                    
                    $.ajax({
                        url: saveKomentarUrl,
                        type: 'POST',
                        data: {
                            catatan_kurator: catatan
                        },
                        success: function(response) {
                            isSaving = false;
                            resolve(response);
                        },
                        error: function(xhr) {
                            console.error('Save error', xhr);
                            isSaving = false;
                            reject(xhr);
                        }
                    });
                    return;
                }

                const nilaiInput = currentCard.find('input[type="radio"]:checked').val();

                if (!nilaiInput) {
                    isSaving = false;
                    resolve();
                    return;
                }

                const saveUrl = decodeURIComponent(saveUrlTemplate).replace(':kriteria_id', idKriteria);

                $.ajax({
                    url: saveUrl,
                    type: 'POST',
                    data: {
                        nilai_input: nilaiInput
                    },
                    success: function(response) {
                        isSaving = false;
                        resolve(response);
                    },
                    error: function(xhr) {
                        console.error('Save error', xhr);
                        isSaving = false;
                        reject(xhr);
                    }
                });
            });
        }
        @endif
    });
</script>
@endpush
