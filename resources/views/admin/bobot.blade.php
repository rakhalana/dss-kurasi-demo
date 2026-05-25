@extends('base.app')

@section('title', 'Bobot Kriteria')

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
                            <h4 class="font-weight-bold text-primary mb-1">Bobot Kriteria</h4>
                            <p class="text-muted small mb-0">Penentuan tingkat kepentingan antar kriteria menggunakan AHP
                                (Analytical Hierarchy Process).</p>
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

                    <!-- Tabs Nav -->
                    <ul class="nav nav-tabs mb-4" id="ahpTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="form-tab" data-toggle="tab" href="#form-ahp"
                                role="tab" aria-controls="form-ahp" aria-selected="true">
                                <i data-lucide="sliders" class="mr-2" style="width: 16px;"></i>Form Perbandingan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="hasil-tab" data-toggle="tab" href="#hasil-ahp"
                                role="tab" aria-controls="hasil-ahp" aria-selected="false">
                                <i data-lucide="bar-chart-2" class="mr-2" style="width: 16px;"></i>Matriks Perbandingan
                            </a>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="ahpTabContent">

                        <!-- Tab 1: Form Perbandingan -->
                        <div class="tab-pane fade show active" id="form-ahp" role="tabpanel" aria-labelledby="form-tab">

                            <form action="{{ route('admin.bobot.calculate') }}" method="POST">
                                @csrf

                                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 bg-white p-3 rounded-lg shadow-sm border"
                                    style="position: sticky; top: 70px; z-index: 1020;">
                                    <div class="d-flex align-items-center mb-3 mb-xl-0 mr-xl-4 text-muted">
                                        <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center text-primary"
                                            style="width: 48px; height: 48px; flex-shrink: 0; background-color: rgba(78, 115, 223, 0.1);">
                                            <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
                                        </div>
                                        <div class="small" style="line-height: 1.6;">
                                            <strong class="text-dark d-block mb-1">Panduan Pengisian Form
                                                Perbandingan</strong>
                                            Pilih angka pada slider ke arah kriteria yang
                                            <strong>lebih penting</strong>. Semakin jauh angkanya, semakin mutlak
                                            kepentingannya. Pilih angka <strong>1</strong> (tengah) jika keduanya sama
                                            penting.
                                        </div>
                                    </div>
                                    <button type="submit" id="btn-simpan-ahp"
                                        class="btn btn-primary shadow-sm rounded-pill font-weight-bold d-flex align-items-center justify-content-center px-4"
                                        style="white-space: nowrap; flex-shrink: 0; height: 48px; transition: all 0.3s;">
                                        <div id="btn-content-simpan" class="d-flex align-items-center">
                                            <i data-lucide="save" class="mr-2" style="width: 18px; height: 18px;"></i>Simpan
                                            Bobot AHP
                                        </div>
                                    </button>
                                </div>

                                @php
                                    $groupedPairs = [];
                                    foreach ($pairs as $pair) {
                                        $groupedPairs[$pair['k1']->nama_kriteria][] = $pair;
                                    }
                                @endphp

                                <div class="row">
                                    <!-- Accordion Kiri -->
                                    <div class="col-lg-8 col-xl-9">
                                        <div class="accordion mb-4" id="ahpAccordion">
                                            @foreach($groupedPairs as $k1Name => $group)
                                                <div class="card border-0 shadow-sm mb-2 rounded-lg overflow-hidden">
                                                    <div class="card-header bg-white border-bottom-0 p-0"
                                                        id="heading-{{ Str::slug($k1Name) }}">
                                                        <h2 class="mb-0">
                                                            <button
                                                                class="btn btn-link btn-block text-left font-weight-bold text-dark text-decoration-none d-flex justify-content-between align-items-center py-3 px-4"
                                                                type="button" data-toggle="collapse"
                                                                data-target="#collapse-{{ Str::slug($k1Name) }}"
                                                                aria-expanded="true"
                                                                aria-controls="collapse-{{ Str::slug($k1Name) }}">
                                                                <span class="d-flex align-items-center"><i data-lucide="target"
                                                                        class="mr-3 text-primary" style="width: 18px;"></i>
                                                                    Perbandingan untuk Kriteria: <span
                                                                        class="text-primary ml-1">{{ $k1Name }}</span></span>
                                                                <i data-lucide="chevron-down" style="width: 18px;"></i>
                                                            </button>
                                                        </h2>
                                                    </div>

                                                    <div id="collapse-{{ Str::slug($k1Name) }}"
                                                        class="collapse {{ $loop->first ? 'show' : '' }}"
                                                        aria-labelledby="heading-{{ Str::slug($k1Name) }}"
                                                        data-parent="#ahpAccordion">
                                                        <div class="card-body bg-light py-4">
                                                            <div class="row">
                                                                @foreach($group as $pair)
                                                                    <div class="col-12 col-xl-6 mb-3">
                                                                        <div
                                                                            class="d-flex flex-column align-items-center bg-white p-3 rounded shadow-sm border border-light h-100">
                                                                            <div
                                                                                class="d-flex justify-content-between w-100 mb-3 px-2">
                                                                                <span class="font-weight-bold text-primary"
                                                                                    style="flex:1; text-align:right;">{{ $pair['k1']->nama_kriteria }}</span>
                                                                                <span class="text-muted small px-3 text-center"
                                                                                    style="flex:0; white-space:nowrap;">vs</span>
                                                                                <span class="font-weight-bold text-success"
                                                                                    style="flex:1; text-align:left;">{{ $pair['k2']->nama_kriteria }}</span>
                                                                            </div>

                                                                            <div
                                                                                class="ahp-radio-group d-flex align-items-center bg-light rounded-pill px-3 py-2 border">
                                                                                <!-- K1 lebih dominan -->
                                                                                <label class="m-0 mx-1"
                                                                                    title="{{ $pair['k1']->nama_kriteria }} Mutlak lebih penting ">
                                                                                    <input type="radio" class="d-none"
                                                                                        name="pair[{{ $pair['k1']->id_kriteria }}][{{ $pair['k2']->id_kriteria }}]"
                                                                                        value="9" {{ $pair['value'] == '9' ? 'checked' : '' }}>
                                                                                    <div class="ahp-radio-btn ahp-btn-left">9</div>
                                                                                </label>
                                                                                <label class="m-0 mx-1"
                                                                                    title="{{ $pair['k1']->nama_kriteria }} Sangat penting ">
                                                                                    <input type="radio" class="d-none"
                                                                                        name="pair[{{ $pair['k1']->id_kriteria }}][{{ $pair['k2']->id_kriteria }}]"
                                                                                        value="7" {{ $pair['value'] == '7' ? 'checked' : '' }}>
                                                                                    <div class="ahp-radio-btn ahp-btn-left">7</div>
                                                                                </label>
                                                                                <label class="m-0 mx-1"
                                                                                    title="{{ $pair['k1']->nama_kriteria }} Lebih penting ">
                                                                                    <input type="radio" class="d-none"
                                                                                        name="pair[{{ $pair['k1']->id_kriteria }}][{{ $pair['k2']->id_kriteria }}]"
                                                                                        value="5" {{ $pair['value'] == '5' ? 'checked' : '' }}>
                                                                                    <div class="ahp-radio-btn ahp-btn-left">5</div>
                                                                                </label>
                                                                                <label class="m-0 mx-1"
                                                                                    title="{{ $pair['k1']->nama_kriteria }} Sedikit lebih penting ">
                                                                                    <input type="radio" class="d-none"
                                                                                        name="pair[{{ $pair['k1']->id_kriteria }}][{{ $pair['k2']->id_kriteria }}]"
                                                                                        value="3" {{ $pair['value'] == '3' ? 'checked' : '' }}>
                                                                                    <div class="ahp-radio-btn ahp-btn-left">3</div>
                                                                                </label>

                                                                                <!-- Sama Penting -->
                                                                                <label class="m-0 mx-2" title="Kedua kriteria Sama penting">
                                                                                    <input type="radio" class="d-none"
                                                                                        name="pair[{{ $pair['k1']->id_kriteria }}][{{ $pair['k2']->id_kriteria }}]"
                                                                                        value="1" {{ $pair['value'] == '1' ? 'checked' : '' }}>
                                                                                    <div class="ahp-radio-btn ahp-btn-center">1
                                                                                    </div>
                                                                                </label>

                                                                                <!-- K2 lebih dominan -->
                                                                                <label class="m-0 mx-1"
                                                                                    title="{{ $pair['k2']->nama_kriteria }} Sedikit lebih penting ">
                                                                                    <input type="radio" class="d-none"
                                                                                        name="pair[{{ $pair['k1']->id_kriteria }}][{{ $pair['k2']->id_kriteria }}]"
                                                                                        value="1/3" {{ $pair['value'] == '1/3' ? 'checked' : '' }}>
                                                                                    <div class="ahp-radio-btn ahp-btn-right">3</div>
                                                                                </label>
                                                                                <label class="m-0 mx-1"
                                                                                    title="{{ $pair['k2']->nama_kriteria }} Lebih penting ">
                                                                                    <input type="radio" class="d-none"
                                                                                        name="pair[{{ $pair['k1']->id_kriteria }}][{{ $pair['k2']->id_kriteria }}]"
                                                                                        value="1/5" {{ $pair['value'] == '1/5' ? 'checked' : '' }}>
                                                                                    <div class="ahp-radio-btn ahp-btn-right">5</div>
                                                                                </label>
                                                                                <label class="m-0 mx-1"
                                                                                    title="{{ $pair['k2']->nama_kriteria }} Sangat penting ">
                                                                                    <input type="radio" class="d-none"
                                                                                        name="pair[{{ $pair['k1']->id_kriteria }}][{{ $pair['k2']->id_kriteria }}]"
                                                                                        value="1/7" {{ $pair['value'] == '1/7' ? 'checked' : '' }}>
                                                                                    <div class="ahp-radio-btn ahp-btn-right">7</div>
                                                                                </label>
                                                                                <label class="m-0 mx-1"
                                                                                    title="{{ $pair['k2']->nama_kriteria }} Mutlak lebih penting ">
                                                                                    <input type="radio" class="d-none"
                                                                                        name="pair[{{ $pair['k1']->id_kriteria }}][{{ $pair['k2']->id_kriteria }}]"
                                                                                        value="1/9" {{ $pair['value'] == '1/9' ? 'checked' : '' }}>
                                                                                    <div class="ahp-radio-btn ahp-btn-right">9</div>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Legenda & Live Preview Kanan -->
                                    <div class="col-lg-4 col-xl-3">
                                        <!-- Live Result Card -->
                                        <div class="card border-0 shadow-sm rounded-lg sticky-top mb-3"
                                            style="top: 20px; z-index: 10;">
                                            <div
                                                class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                                <h6 class="m-0 font-weight-bold text-dark"><i data-lucide="activity"
                                                        class="mr-2 text-primary" style="width: 18px;"></i>Live Bobot AHP</h6>
                                                <span id="live-status-badge"
                                                    class="badge badge-success px-2 py-1">Konsisten</span>
                                            </div>
                                            <div class="card-body bg-light">
                                                <div class="mb-3 text-center">
                                                    <span class="text-muted small d-block mb-1">Consistency Ratio
                                                        (CR)</span>
                                                    <h3 id="live-cr" class="m-0 font-weight-bold text-success">0.0000</h3>
                                                    <small id="live-cr-msg" class="text-success font-weight-bold">✓ CR ≤ 0.1
                                                        (Valid)</small>
                                                </div>

                                                <hr class="border-light">

                                                <h6 class="small font-weight-bold text-muted text-uppercase mb-2">Bobot Kriteria:</h6>
                                                <div id="live-bobot-list">
                                                    @foreach($kriterias as $k)
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-1 small">
                                                            <span class="text-truncate mr-2"
                                                                style="max-width: 150px;">{{ $k->nama_kriteria }}</span>
                                                            <strong class="text-primary"
                                                                id="live-bobot-{{ $k->id_kriteria }}">0.00%</strong>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card border-0 shadow-sm rounded-lg sticky-top" style="top: 360px;">
                                            <div class="card-header bg-white border-bottom py-3">
                                                <h6 class="m-0 font-weight-bold text-dark d-flex align-items-center">
                                                    <i data-lucide="help-circle" class="mr-2 text-primary"
                                                        style="width: 18px;"></i>
                                                    Keterangan Skala
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <table class="table table-hover mb-0">
                                                    <thead class="bg-light text-muted uppercase">
                                                        <tr>
                                                            <th class="text-center py-3" style="width: 25%">Skala</th>
                                                            <th class="py-3">Keterangan AHP</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-center font-weight-bold text-primary align-middle"
                                                                style="font-size: 1.1rem;">1</td>
                                                            <td class="align-middle py-3"><strong>Sama
                                                                    penting</strong><br><span class="text-muted">Kedua
                                                                    kriteria sama tingkat kepentingannya.</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center font-weight-bold text-primary align-middle"
                                                                style="font-size: 1.1rem;">3</td>
                                                            <td class="align-middle py-3"><strong>Sedikit lebih
                                                                    penting</strong><br><span class="text-muted">Satu
                                                                    kriteria sedikit menyokong kriteria lainnya.</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center font-weight-bold text-primary align-middle"
                                                                style="font-size: 1.1rem;">5</td>
                                                            <td class="align-middle py-3"><strong>Lebih
                                                                    penting</strong><br><span class="text-muted">Kepentingan
                                                                    satu kriteria cukup kuat di atas yang lain.</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center font-weight-bold text-primary align-middle"
                                                                style="font-size: 1.1rem;">7</td>
                                                            <td class="align-middle py-3"><strong>Sangat
                                                                    penting</strong><br><span class="text-muted">Satu
                                                                    kriteria sangat mendominasi kriteria lainnya.</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center font-weight-bold text-primary align-middle"
                                                                style="font-size: 1.1rem;">9</td>
                                                            <td class="align-middle py-3"><strong>Mutlak
                                                                    penting</strong><br><span class="text-muted">Satu
                                                                    kriteria secara mutlak melebihi kriteria lainnya.</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 2: Hasil AHP -->
                        <div class="tab-pane fade" id="hasil-ahp" role="tabpanel" aria-labelledby="hasil-tab">
                            @if(!$hasilAhp)
                                <div class="alert alert-info text-center shadow-sm border-0">
                                    <i data-lucide="info" class="mr-2"></i>Belum ada data perhitungan. Silakan isi form
                                    perbandingan dan klik <strong>Simpan Bobot AHP</strong>.
                                </div>
                            @else
                                <!-- Matriks Perbandingan -->
                                <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-5">
                                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                                        <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center text-primary"
                                            style="width: 40px; height: 40px; background-color: rgba(78, 115, 223, 0.1);">
                                            <i data-lucide="grid" style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <h6 class="m-0 font-weight-bold text-dark">Matriks Perbandingan Berpasangan</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover text-center align-middle mb-0"
                                                style="min-width: 1000px;">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="align-middle text-left border-0 text-uppercase text-muted"
                                                            style="font-size: 0.8rem; letter-spacing: 0.5px;">Kriteria</th>
                                                        @foreach($kriterias as $k2)
                                                            <th class="align-middle border-0 text-dark"
                                                                style="white-space: nowrap; font-size: 0.85rem;">
                                                                {{ $k2->nama_kriteria }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($kriterias as $k1)
                                                        <tr>
                                                            <td class="text-left font-weight-bold border-right"
                                                                style="min-width: 180px; background-color: #f8f9fc; color: #4e73df;">
                                                                {{ $k1->nama_kriteria }}</td>
                                                            @foreach($kriterias as $k2)
                                                                @php
                                                                    $val = $hasilAhp['matrix'][$k1->id_kriteria][$k2->id_kriteria];
                                                                    $isDiagonal = $k1->id_kriteria == $k2->id_kriteria;
                                                                @endphp
                                                                <td class="{{ $isDiagonal ? 'font-weight-bold' : '' }}"
                                                                    style="{{ $isDiagonal ? 'background-color: rgba(78, 115, 223, 0.05);' : '' }}">
                                                                    {{ number_format($val, 4) }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="font-weight-bold">
                                                    <tr style="background-color: #f1f3f9;">
                                                        <td class="text-left border-right text-dark">Jumlah Kolom</td>
                                                        @foreach($kriterias as $k2)
                                                            <td class="text-primary">
                                                                {{ number_format($hasilAhp['colSum'][$k2->id_kriteria], 4) }}</td>
                                                        @endforeach
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Normalisasi & Bobot -->
                                <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-5">
                                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                                        <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center text-success"
                                            style="width: 40px; height: 40px; background-color: rgba(28, 200, 138, 0.1);">
                                            <i data-lucide="percent" style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <h6 class="m-0 font-weight-bold text-dark">Matriks Normalisasi & Bobot Prioritas</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover text-center align-middle mb-0"
                                                style="min-width: 1100px;">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="align-middle text-left border-0 text-uppercase text-muted"
                                                            style="font-size: 0.8rem; letter-spacing: 0.5px;">Kriteria</th>
                                                        @foreach($kriterias as $k2)
                                                            <th class="align-middle border-0 text-dark"
                                                                style="white-space: nowrap; font-size: 0.85rem;">
                                                                {{ $k2->nama_kriteria }}</th>
                                                        @endforeach
                                                        <th class="align-middle border-0 text-white font-weight-bold"
                                                            style="background-color: #1cc88a; font-size: 0.9rem; min-width: 120px;">
                                                            Bobot (%)
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($kriterias as $k1)
                                                        <tr>
                                                            <td class="text-left font-weight-bold border-right"
                                                                style="min-width: 180px; background-color: #f8f9fc; color: #1cc88a;">
                                                                {{ $k1->nama_kriteria }}</td>
                                                            @foreach($kriterias as $k2)
                                                                <td class="text-muted">
                                                                    {{ number_format($hasilAhp['normalized'][$k1->id_kriteria][$k2->id_kriteria], 4) }}
                                                                </td>
                                                            @endforeach
                                                            <td class="font-weight-bold"
                                                                style="background-color: rgba(28, 200, 138, 0.05); color: #1cc88a; font-size: 1.05rem;">
                                                                {{ number_format($hasilAhp['bobot'][$k1->id_kriteria] * 100, 2) }}%
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rasio Konsistensi -->
                                <div class="row mb-4">
                                    <div class="col-lg-6">
                                        <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100">
                                            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                                                <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center text-info"
                                                    style="width: 40px; height: 40px; background-color: rgba(54, 185, 204, 0.1);">
                                                    <i data-lucide="activity" style="width: 20px; height: 20px;"></i>
                                                </div>
                                                <h6 class="m-0 font-weight-bold text-dark">Parameter Konsistensi</h6>
                                            </div>
                                            <div class="card-body p-4">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                                    <div class="text-muted d-flex align-items-center">
                                                        <i data-lucide="hash" class="mr-2" style="width: 16px;"></i> Lambda Max
                                                        (λ max)
                                                    </div>
                                                    <div class="font-weight-bold text-dark h5 mb-0">
                                                        {{ number_format($hasilAhp['sesi']->lambda_max, 4) }}</div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                                    <div class="text-muted d-flex align-items-center">
                                                        <i data-lucide="bar-chart" class="mr-2" style="width: 16px;"></i>
                                                        Consistency Index (CI)
                                                    </div>
                                                    <div class="font-weight-bold text-dark h5 mb-0">
                                                        {{ number_format($hasilAhp['sesi']->ci, 4) }}</div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="text-muted d-flex align-items-center">
                                                        <i data-lucide="check-square" class="mr-2" style="width: 16px;"></i>
                                                        Consistency Ratio (CR)
                                                    </div>
                                                    <div
                                                        class="font-weight-bold {{ $hasilAhp['sesi']->cr > 0.1 ? 'text-danger' : 'text-success' }} h4 mb-0">
                                                        {{ number_format($hasilAhp['sesi']->cr, 4) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="card-footer py-3 {{ $hasilAhp['sesi']->cr > 0.1 ? 'bg-danger text-white' : 'bg-success text-white' }} border-0">
                                                <div class="d-flex align-items-center justify-content-center font-weight-bold"
                                                    style="font-size: 1.05rem;">
                                                    @if($hasilAhp['sesi']->cr > 0.1)
                                                        <i data-lucide="alert-triangle" class="mr-2"></i>
                                                        TIDAK KONSISTEN (CR > 0.1)
                                                    @else
                                                        <i data-lucide="check-circle" class="mr-2"></i>
                                                        KONSISTEN (CR ≤ 0.1) & Valid
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mt-4 mt-lg-0">
                                        <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100 bg-light">
                                            <div class="card-body p-4 d-flex flex-column justify-content-center">
                                                <h6 class="font-weight-bold text-dark mb-3">
                                                    <i data-lucide="info" class="mr-2 text-primary"></i>Penjelasan Hasil
                                                </h6>
                                                <p class="text-muted mb-3" style="line-height: 1.6; font-size: 0.9rem;">
                                                    Bobot prioritas yang dihasilkan akan digunakan sebagai
                                                    <strong>pengali</strong> pada nilai kriteria masing-masing alternatif produk
                                                    untuk menentukan skor akhir.
                                                </p>
                                                <div class="text-dark font-weight-bold mb-2" style="font-size: 0.9rem;">Asal
                                                    Usul Parameter:</div>
                                                <ul class="text-muted pl-3 mb-0" style="line-height: 1.6; font-size: 0.85rem;">
                                                    <li class="mb-2"><strong>Lambda Max (λ max)</strong>: Total jumlahan dari
                                                        perkalian antara <em>Jumlah Kolom</em> dengan <em>Bobot (%)</em>.
                                                        Semakin dekat nilainya dengan jumlah kriteria
                                                        (n={{ count($kriterias) }}), semakin sempurna konsistensinya.</li>
                                                    <li class="mb-2"><strong>CI (Consistency Index)</strong>: Indeks
                                                        penyimpangan pengisian. Rumusnya: <code>(λ max - n) / (n - 1)</code>.
                                                    </li>
                                                    <li class="mb-2"><strong>RI (Random Index)</strong>: Ketetapan indeks acak
                                                        standar Saaty. Untuk n={{ count($kriterias) }}, nilai RI baku adalah
                                                        <strong>1.45</strong>.</li>
                                                    <li><strong>CR (Consistency Ratio)</strong>: Rasio konsistensi akhir.
                                                        Rumusnya: <code>CI / RI</code>. Toleransi batas maksimal agar data
                                                        dianggap valid adalah <strong>0.1 (10%)</strong>.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endif
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            @if(session('error'))
                $('#form-tab').tab('show');
            @endif

            @if(session('success'))
                $('#hasil-tab').tab('show');
            @endif

            // Live AHP
            let rawKriteriaData = @json($kriterias->map(function ($k) {
            return ['id_kriteria' => $k->id_kriteria, 'nama_kriteria' => $k->nama_kriteria]; })->values());
            const kriteriaData = Array.isArray(rawKriteriaData) ? rawKriteriaData : Object.values(rawKriteriaData);

            const riArray = [0, 0, 0, 0.58, 0.9, 1.12, 1.24, 1.32, 1.41, 1.45, 1.49, 1.51, 1.48, 1.56, 1.57];

            function liveAHP() {
                try {
                    const n = kriteriaData.length;
                    if (!n || n === 0) return;

                    let matrix = {};

                    kriteriaData.forEach(k1 => {
                        matrix[k1.id_kriteria] = {};
                        kriteriaData.forEach(k2 => {
                            matrix[k1.id_kriteria][k2.id_kriteria] = k1.id_kriteria === k2.id_kriteria ? 1.0 : 0;
                        });
                    });

                    $('input[type="radio"]:checked').each(function () {
                        const name = $(this).attr('name');
                        if (!name) return;

                        const matches = name.match(/pair\[(\d+)\]\[(\d+)\]/);
                        if (matches) {
                            const k1 = parseInt(matches[1]);
                            const k2 = parseInt(matches[2]);
                            const valStr = $(this).val();
                            let val = 1.0;
                            if (valStr && valStr.includes('/')) {
                                const parts = valStr.split('/');
                                val = parseFloat(parts[0]) / parseFloat(parts[1]);
                            } else if (valStr) {
                                val = parseFloat(valStr);
                            }

                            if (!isNaN(val)) {
                                if (matrix[k1] && typeof matrix[k1][k2] !== 'undefined') {
                                    matrix[k1][k2] = val;
                                }
                                if (matrix[k2] && typeof matrix[k2][k1] !== 'undefined') {
                                    matrix[k2][k1] = 1.0 / val;
                                }
                            }
                        }
                    });

                    let colSums = {};
                    kriteriaData.forEach(k2 => {
                        let sum = 0;
                        kriteriaData.forEach(k1 => {
                            sum += matrix[k1.id_kriteria][k2.id_kriteria] || 0;
                        });
                        colSums[k2.id_kriteria] = sum;
                    });

                    let weights = {};
                    kriteriaData.forEach(k1 => {
                        let rowSum = 0;
                        kriteriaData.forEach(k2 => {
                            let cSum = colSums[k2.id_kriteria] > 0 ? colSums[k2.id_kriteria] : 1;
                            rowSum += (matrix[k1.id_kriteria][k2.id_kriteria] || 0) / cSum;
                        });
                        weights[k1.id_kriteria] = rowSum / n;
                    });

                    let lambdaMax = 0;
                    kriteriaData.forEach(k1 => {
                        let cSum = colSums[k1.id_kriteria] > 0 ? colSums[k1.id_kriteria] : 1;
                        let w = weights[k1.id_kriteria] || 0;
                        lambdaMax += cSum * w;
                    });

                    const ci = (lambdaMax - n) / (n - 1);
                    const ri = riArray[n] || 1.45;
                    const cr = ri === 0 ? 0 : ci / ri;

                    if (isNaN(cr)) {
                        $('#live-cr').text('NaN').removeClass('text-success').addClass('text-danger');
                        return;
                    }

                    $('#live-cr').text(Math.abs(cr).toFixed(4));

                    if (cr <= 0.1 && cr >= 0) {
                        $('#live-cr').removeClass('text-danger').addClass('text-success');
                        $('#live-status-badge').removeClass('badge-danger').addClass('badge-success').text('Konsisten');
                        $('#live-cr-msg').removeClass('text-danger').addClass('text-success').html('CR ≤ 0.1 = Konsisten');
                        $('#btn-simpan-ahp').prop('disabled', false).removeClass('btn-danger').addClass('btn-primary');
                        $('#btn-content-simpan').html('<i data-lucide="save" class="mr-2"></i>Simpan Bobot AHP');
                    } else {
                        $('#live-cr').removeClass('text-success').addClass('text-danger');
                        $('#live-status-badge').removeClass('badge-success').addClass('badge-danger').text('Tidak Konsisten');
                        $('#live-cr-msg').removeClass('text-success').addClass('text-danger').html('CR > 0.1 = Tidak Konsisten');
                        $('#btn-simpan-ahp').prop('disabled', true).removeClass('btn-primary').addClass('btn-danger');
                        $('#btn-content-simpan').html('<i data-lucide="alert-circle" class="mr-2"></i>CR Tidak Konsisten');
                    }

                    if (window.lucide && window.lucide.icons) {
                        window.lucide.createIcons({ icons: window.lucide.icons });
                    }

                    kriteriaData.forEach(k => {
                        let wPct = ((weights[k.id_kriteria] || 0) * 100).toFixed(2);
                        $('#live-bobot-' + k.id_kriteria).text(wPct + '%');
                    });
                } catch (e) {
                    $('#live-cr').text('ERR').removeClass('text-success').addClass('text-danger');
                    $('#live-cr-msg').text(e.message).removeClass('text-success').addClass('text-danger');
                    console.error("AHP Live Error: ", e);
                }
            }

            $('input[type="radio"]').on('change', liveAHP);
            liveAHP();

            // AJAX Form Submission
            $('form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const btn = $('#btn-simpan-ahp');
                const btnContent = $('#btn-content-simpan');
                const originalHtml = btnContent.html();

                // Show loading
                btn.prop('disabled', true);
                btnContent.html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Menyimpan...');

                // Remove existing dynamic alerts
                $('.dynamic-alert').remove();

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        btn.prop('disabled', false);
                        btnContent.html(originalHtml);

                        const alertClass = response.status === 'success' ? 'alert-success' : 'alert-danger';
                        const icon = response.status === 'success' ? 'check-circle' : 'alert-circle';

                        const alertHtml = `
                                <div class="alert ${alertClass} alert-dismissible fade show shadow-sm dynamic-alert" role="alert">
                                    <i data-lucide="${icon}" class="mr-2"></i>${response.message}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            `;

                        form.prepend(alertHtml);

                        if (window.lucide && window.lucide.icons) {
                            window.lucide.createIcons({ icons: window.lucide.icons });
                        }

                        // Scroll to top of form smoothly
                        $('html, body').animate({
                            scrollTop: form.offset().top - 100
                        }, 500);
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false);
                        btnContent.html(originalHtml);

                        const alertHtml = `
                                <div class="alert alert-danger alert-dismissible fade show shadow-sm dynamic-alert" role="alert">
                                    <i data-lucide="x-circle" class="mr-2"></i>Terjadi kesalahan saat menghubungi server.
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            `;
                        form.prepend(alertHtml);

                        if (window.lucide && window.lucide.icons) {
                            window.lucide.createIcons({ icons: window.lucide.icons });
                        }
                    }
                });
            });
        });
    </script>
@endpush