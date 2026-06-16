@extends('base.app')

@section('title', $periode->nama_periode)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('hasil.index') }}" style="color: inherit; text-decoration: none;">Hasil Kurasi</a>
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
                {{-- Header Section --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="font-weight-bold text-primary mb-1">Hasil Penilaian: {{ $periode->nama_periode }}</h4>
                        <p class="text-muted small mb-0">Hasil akhir perhitungan Profile Matching dan integrasi Bobot AHP.</p>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('hasil.cetak', $periode->id_periode_kurasi) }}" target="_blank" class="btn btn-outline-primary btn-rounded mr-2 px-3">
                            <i data-lucide="printer" class="mr-1" style="width: 16px;"></i> Cetak Laporan Penilaian
                        </a>
                    </div>
                </div>

                <div class="row">
                    {{-- Leaderboard Table --}}
                    <div class="col-lg-9 mb-4 order-1">
                        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">Ranking Produk</h6>
                                <!-- <span class="badge badge-primary-light text-primary px-3 py-1 small rounded-pill">Profile Matching Engine</span> -->
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light text-muted small uppercase tracking-wider">
                                            <tr>
                                                <th class="pl-4 py-3 text-center" style="width: 70px;">Rank</th>
                                                <th class="py-3">Produk & Brand</th>
                                                <th class="py-3 text-center">Skor Akhir</th>
                                                <th class="py-3 text-center">Status</th>
                                                <th class="py-3 pr-4 text-right">Detail</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($results as $index => $res)
                                                <tr class="{{ $index < 3 ? 'bg-rank-' . ($index + 1) : '' }}">
                                                    <td class="pl-4 py-3 text-center">
                                                        @if($index == 0)
                                                            <div class="rank-badge gold"><i data-lucide="trophy"></i></div>
                                                        @elseif($index == 1)
                                                            <div class="rank-badge silver">2</div>
                                                        @elseif($index == 2)
                                                            <div class="rank-badge bronze">3</div>
                                                        @else
                                                            <span class="font-weight-bold text-muted">#{{ $index + 1 }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="product-img-wrapper mr-3 shadow-sm border">
                                                                @if($res->alternatif->foto_produk)
                                                                    <img src="{{ Storage::disk('supabase')->url($res->alternatif->foto_produk) }}" alt="Foto">
                                                                @else
                                                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                                                                        <i data-lucide="image" class="text-muted" style="width: 16px;"></i>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <div class="font-weight-bold text-dark">{{ $res->alternatif->nama_produk }}</div>
                                                                <div class="small text-muted">{{ $res->alternatif->brand }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 text-center">
                                                        <span class="h6 font-weight-bold text-primary mb-0">{{ number_format($res->total_score, 3) }}</span>
                                                    </td>
                                                    <td class="py-3 text-center">
                                                        @if($res->status_layak === 'layak_retail')
                                                            <span class="badge badge-pill badge-success px-3 py-2">
                                                                <i data-lucide="check-circle" class="mr-1" style="width: 12px; height: 12px;"></i> Layak Retail
                                                            </span>
                                                        @elseif($res->status_layak === 'layak_retail_bersyarat')
                                                            <span class="badge badge-pill badge-warning px-3 py-2">
                                                                <i data-lucide="alert-circle" class="mr-1" style="width: 12px; height: 12px;"></i> Layak Retail Bersyarat
                                                            </span>
                                                        @else
                                                            <span class="badge badge-pill badge-danger px-3 py-2">
                                                                <i data-lucide="x-circle" class="mr-1" style="width: 12px; height: 12px;"></i> Belum Layak
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 pr-4 text-right">
                                                        <button class="btn btn-sm btn-white border rounded-pill px-3 shadow-sm" data-toggle="collapse" data-target="#detail-{{ $res->alternatif->id_alternatif }}">
                                                            Detail <i data-lucide="chevron-down" class="ml-1" style="width: 14px;"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                {{-- Detail Breakdown Section --}}
                                                <tr class="collapse" id="detail-{{ $res->alternatif->id_alternatif }}">
                                                    <td colspan="5" class="p-0 border-0">
                                                        <div class="bg-light px-4 py-4 border-bottom">
                                                            <div class="row">
                                                                {{-- CASE 1: GAGAL LEGALITAS (Prioritas Utama) --}}
                                                                @if(!$res->is_lolos_legalitas)
                                                                    <div class="col-12">
                                                                        <div class="evaluation-report p-4 rounded-lg border border-danger bg-danger-light text-center">
                                                                            <i data-lucide="shield-alert" class="text-danger mb-3" style="width: 48px; height: 48px;"></i>
                                                                            <h5 class="font-weight-bold text-dark mb-2">Gagal Verifikasi Legalitas</h5>
                                                                            <p class="text-muted mb-4">Produk ini tidak dapat diproses lebih lanjut dalam penilaian kriteria karena dokumen legalitas belum lengkap.</p>
                                                                            
                                                                            <div class="d-inline-block text-left bg-white p-4 rounded shadow-sm border-top border-danger" style="max-width: 500px; width: 100%;">
                                                                                <h6 class="small font-weight-bold text-danger mb-3"><i data-lucide="file-warning" class="mr-1" style="width: 14px;"></i> Dokumen yang wajib dilengkapi:</h6>
                                                                                <div class="d-flex flex-wrap mb-3">
                                                                                    @foreach($res->missing_docs as $doc)
                                                                                        <span class="badge badge-danger px-3 py-2 mr-2 mb-2" style="font-size: 0.8rem;">{{ $doc }}</span>
                                                                                    @endforeach
                                                                                </div>
                                                                                <p class="small text-muted mb-0 font-italic"><strong>Rekomendasi:</strong> Demi memenuhi standar regulasi dan keamanan pangan, silakan lengkapi dokumen yang diperlukan. Produk dapat kembali mengajukan produk ini pada periode kurasi mendatang setelah persyaratan dokumen terpenuhi.</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    {{-- CASE 2: LOLOS LEGALITAS, TAMPILKAN BREAKDOWN PM --}}
                                                                    {{-- Evaluasi --}}
                                                                    <div class="col-md-5 mb-3 mb-md-0">
                                                                        <div class="evaluation-report p-3 rounded-lg border {{ count($res->evaluations) > 0 ? 'border-warning bg-warning-light' : 'border-success bg-success-light' }}">
                                                                            <h6 class="font-weight-bold text-dark mb-3 d-flex align-items-center">
                                                                                <i data-lucide="{{ count($res->evaluations) > 0 ? 'clipboard-warning' : 'check-circle' }}" class="mr-2 {{ count($res->evaluations) > 0 ? 'text-warning' : 'text-success' }}"></i> 
                                                                                Evaluasi & Saran Perbaikan
                                                                            </h6>
                                                                            
                                                                            @if(count($res->evaluations) > 0)
                                                                                <div class="mb-3">
                                                                                    <p class="small text-muted mb-2">Aspek penilaian yang perlu ditingkatkan:</p>
                                                                                    <div class="eval-list">
                                                                                        @foreach($res->evaluations as $eval)
                                                                                            <div class="d-flex align-items-start mb-2 bg-white p-2 rounded border-left border-danger shadow-sm">
                                                                                                <i data-lucide="alert-circle" class="text-danger mr-2 mt-0.5" style="width: 14px; height: 14px;"></i>
                                                                                                <div class="small text-muted">
                                                                                                    Lakukan perbaikan pada kriteria <strong class="text-dark">{{ $eval['kriteria'] }}</strong> dengan memastikan {{ lcfirst($eval['target_desc']) }}
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                                
                                                                                @if(!empty($res->pa->catatan_kurator))
                                                                                    <div class="mb-3 p-2 bg-white rounded border border-info shadow-sm">
                                                                                        <div class="small font-weight-bold text-info mb-1"><i data-lucide="message-square" class="mr-1" style="width: 12px;"></i> Catatan Kurator:</div>
                                                                                        <p class="small text-muted mb-0 font-italic">{{ $res->pa->catatan_kurator }}</p>
                                                                                    </div>
                                                                                @endif

                                                                                @if($res->status_layak === 'layak_retail_bersyarat')
                                                                                    <div class="saran-box p-2 bg-white rounded border border-warning">
                                                                                        <div class="small font-weight-bold text-warning mb-1"><i data-lucide="lightbulb" class="mr-1" style="width: 12px;"></i> Rekomendasi:</div>
                                                                                        <p class="small text-muted mb-0 font-italic">Produk ini memiliki potensi besar dan layak dipasarkan secara retail dengan beberapa penyesuaian. Kami menyarankan untuk melakukan peningkatan kualitas/perbaikan pada kriteria di atas agar memenuhi standar retail sepenuhnya.</p>
                                                                                    </div>
                                                                                @else
                                                                                    <div class="saran-box p-2 bg-white rounded border border-danger">
                                                                                        <div class="small font-weight-bold text-danger mb-1"><i data-lucide="alert-circle" class="mr-1" style="width: 12px;"></i> Rekomendasi:</div>
                                                                                        <p class="small text-muted mb-0 font-italic">Produk belum memenuhi kriteria kelayakan retail. Kami menyarankan untuk melakukan perbaikan menyeluruh pada kriteria yang belum mencapai target nilai agar siap untuk mengikuti kurasi periode berikutnya.</p>
                                                                                    </div>
                                                                                @endif
                                                                            @else
                                                                                <div class="text-center py-3">
                                                                                    <i data-lucide="award" class="text-success mb-2" style="width: 32px; height: 32px;"></i>
                                                                                    <p class="small text-dark font-weight-bold mb-1">Performa Sempurna!</p>
                                                                                    <p class="small text-muted mb-0">Produk ini telah memenuhi seluruh kriteria target nilai yang ditetapkan.</p>
                                                                                </div>
                                                                                
                                                                                @if(!empty($res->pa->catatan_kurator))
                                                                                    <div class="mt-3 p-2 bg-white rounded border border-info shadow-sm text-left">
                                                                                        <div class="small font-weight-bold text-info mb-1"><i data-lucide="message-square" class="mr-1" style="width: 12px;"></i> Catatan Kurator:</div>
                                                                                        <p class="small text-muted mb-0 font-italic">{{ $res->pa->catatan_kurator }}</p>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Breakdown Perhitungan --}}
                                                                    <div class="col-md-7">
                                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                                            <h6 class="font-weight-bold text-dark mb-0">Breakdown Skor (Profile Matching)</h6>
                                                                            <span class="text-muted" style="font-size: 0.7rem;"><i data-lucide="info" class="mr-1" style="width: 12px;"></i> Skor = Bobot Gap × Bobot AHP</span>
                                                                        </div>
                                                                        <table class="table table-sm table-borderless small mb-0">
                                                                            <thead>
                                                                                <tr class="text-muted border-bottom">
                                                                                    <th>Kriteria</th>
                                                                                    <th class="text-center">Aktual</th>
                                                                                    <th class="text-center">Target</th>
                                                                                    <th class="text-center" style="width: 80px;">Gap</th>
                                                                                    <th class="text-center">Bobot</th>
                                                                                    <th class="text-right">Kontribusi</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($kriterias as $k)
                                                                                    @php $b = $res->breakdown[$k->id_kriteria]; @endphp
                                                                                    <tr class="border-bottom-faint">
                                                                                        <td class="font-weight-500 py-2">{{ $k->nama_kriteria }}</td>
                                                                                        <td class="text-center py-2 font-weight-bold {{ $b['gap'] < 0 ? 'text-danger' : 'text-dark' }}">
                                                                                            {{ $b['aktual'] }}
                                                                                        </td>
                                                                                        <td class="text-center py-2 text-muted">{{ $b['target'] }}</td>
                                                                                        <td class="text-center py-2">
                                                                                            <span class="gap-indicator {{ $b['gap'] < 0 ? 'neg' : ($b['gap'] > 0 ? 'pos' : 'zero') }}">
                                                                                                {{ $b['gap'] > 0 ? '+' . $b['gap'] : $b['gap'] }}
                                                                                            </span>
                                                                                        </td>
                                                                                        <td class="text-center py-2 text-muted">{{ $b['bobot_gap'] }}</td>
                                                                                        <td class="text-right py-2 font-weight-bold text-dark">{{ number_format($b['skor'], 3) }}</td>
                                                                                    </tr>
                                                                                @endforeach
                                                                                <tr class="bg-primary-light">
                                                                                    <td colspan="5" class="text-right font-weight-bold py-2">Total Skor Akhir:</td>
                                                                                    <td class="text-right font-weight-bold text-primary py-2" style="font-size: 1rem;">{{ number_format($res->total_score, 3) }}</td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <i data-lucide="bar-chart-3" class="mb-2" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                                                            <p class="mb-0">Belum ada hasil ranking produk.</p>
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

                    {{-- Info Periode & Bobot AHP (Secondary Sidebar) --}}
                    <div class="col-lg-3 mb-4 order-2">
                        <div class="card border-0 shadow-sm rounded-lg mb-4">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h6 class="font-weight-bold text-dark mb-0">Info Periode</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Kurator:</span>
                                    <span class="font-weight-bold small text-dark">{{ $periode->kurator->name }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Waktu Pelaksanaan:</span>
                                    <span class="font-weight-bold small text-dark">{{ \Carbon\Carbon::parse($periode->tanggal_kurasi)->translatedFormat('d F Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Total Produk:</span>
                                    <span class="font-weight-bold small text-dark">{{ count($results) }} Produk</span>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-lg">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h6 class="font-weight-bold text-dark mb-0">Bobot Kriteria</h6>
                            </div>
                            <div class="card-body pt-0">
                                <p class="text-muted small mb-3">Bobot prioritas yang digunakan pada periode ini.</p>
                                @foreach($periode->ahpSesi->bobot as $bobot)
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small text-dark font-weight-500">{{ $bobot->kriteria->nama_kriteria }}</span>
                                            <span class="small font-weight-bold text-primary">{{ number_format($bobot->bobot_prioritas * 100, 2) }}%</span>
                                        </div>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $bobot->bobot_prioritas * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Legenda Status --}}
                        <div class="card border-0 shadow-sm rounded-lg mt-4">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h6 class="font-weight-bold text-dark mb-0">Keterangan Status</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="mb-3">
                                    <span class="badge badge-success mb-1">Layak Retail</span>
                                    <p class="small text-muted mb-0">Produk direkomendasikan sebagai siap masuk retail.</p>
                                </div>
                                <div class="mb-3">
                                    <span class="badge badge-warning mb-1">Layak Retail Bersyarat</span>
                                    <ul class="pl-3 mb-0 small text-muted">
                                        <li>UMKM melakukan perbaikan produk pada kriteria tersebut.</li>
                                        <li>Kurator melakukan verifikasi perbaikan secara terbatas.</li>
                                    </ul>
                                </div>
                                <div>
                                    <span class="badge badge-danger mb-1">Belum Layak</span>
                                    <ul class="pl-3 mb-0 small text-muted">
                                        <li>UMKM melakukan perbaikan menyeluruh.</li>
                                        <li>Produk mengikuti kurasi ulang penuh.</li>
                                    </ul>
                                </div>
                            </div>
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
        
        // Handle icon change on collapse
        $('[data-toggle="collapse"]').on('click', function() {
            $(this).find('i').toggleClass('rotate-180');
        });
    });
</script>
@endpush
