@extends('base.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid p-0">
        <div class="row no-gutters">
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Content -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-0 dashboard-main">
                @include('layouts.navbar')

                <div class="px-4 py-3 dashboard-content">

                    <div class="card card-welcome">
                        <div class="card-body">
                            <h5 class="card-title"> Selamat datang di Dashboard Admin</h5>
                            <p class="card-text ">Sebagai admin, Anda dapat mengelola data kriteria sistem AHP, data
                                master Produk UMKM, pengguna sistem, hingga pengaturan periode kurasi.</p>
                        </div>
                    </div>

                    <!-- Baris 1: Summary Cards dan Bobot Kriteria -->
                    <div class="row mb-4">
                        <!-- Kolom Summary Cards-->
                        <div class="col-12 col-md-8 col-custom-5-8">
                            <div class="summary-grid">
                                <!-- Card 1 -->
                                <a href="{{ route('admin.kriteria') }}" class="text-decoration-none">
                                    <div class="card card-stat h-100 shadow-sm border-0">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="stat-label">Total Kriteria</div>
                                                <div class="stat-icon text-muted"><i data-lucide="list"></i></div>
                                            </div>
                                            <div class="stat-value">{{ $totalKriteria }}</div>
                                        </div>
                                    </div>
                                </a>

                                <!-- Card 2 -->
                                <a href="{{ route('admin.kurasi.index') }}" class="text-decoration-none">
                                    <div class="card card-stat h-100 shadow-sm border-0">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="stat-label">Periode Kurasi</div>
                                                <div class="stat-icon text-muted"><i data-lucide="calendar"></i></div>
                                            </div>
                                            <div class="stat-value">{{ $totalPeriodeKurasi }}</div>
                                        </div>
                                    </div>
                                </a>

                                <!-- Card 3 -->
                                <a href="{{ route('admin.produk') }}" class="text-decoration-none">
                                    <div class="card card-stat h-100 shadow-sm border-0">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="stat-label">Total Produk</div>
                                                <div class="stat-icon text-muted"><i data-lucide="box"></i></div>
                                            </div>
                                            <div class="stat-value">{{ $totalProduk }}</div>
                                        </div>
                                    </div>
                                </a>

                                <!-- Card 4 -->
                                <a href="#" class="text-decoration-none">
                                    <div class="card card-stat h-100 shadow-sm border-0">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="stat-label">Produk Layak Retail</div>
                                                <div class="stat-icon text-muted"><i data-lucide="check-circle"></i></div>
                                            </div>
                                            <div>
                                                <div class="stat-value text-success">{{ $totalLayakRetail }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Kolom Bobot Kriteria -->
                        <div class="col-12 col-md-4 col-custom-3-8 mt-4 mt-md-0">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-header bg-white font-weight-bold">
                                    <i data-lucide="pie-chart" class="text-info mr-2"></i> Bobot Kriteria Aktif
                                </div>
                                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                                    @if(count($kriteriaBobots) > 0)
                                        <div style="position: relative; height: 250px; width: 100%;">
                                            <canvas id="kriteriaPieChart"></canvas>
                                        </div>
                                    @else
                                        <div class="text-muted text-center py-5"
                                            style="min-height: 250px; display:flex; flex-direction:column; justify-content:center;">
                                            <div class="display-4 text-secondary mb-3 font-weight-bold" style="line-height:1;">0
                                            </div>
                                            Belum ada data kriteria.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Baris 2: Top 5 Produk & Chart Tren -->
                    <div class="row">
                        <!-- Top 5 Produk -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                                    <span><i data-lucide="trophy" class="text-warning mr-2"></i> Top 5 Produk Kurasi</span>
                                    @if(count($periodeSelesaiList) > 0)
                                        <form method="GET" action="{{ route('dashboard') }}" id="formPeriodeDropdown" class="m-0 p-0">
                                            <select name="periode" id="periodeDropdown" class="form-control form-control-sm" style="width: auto;" onchange="document.getElementById('formPeriodeDropdown').submit()">
                                                @foreach($periodeSelesaiList as $p)
                                                    <option value="{{ $p->id_periode_kurasi }}" {{ ($latestPeriode && $latestPeriode->id_periode_kurasi == $p->id_periode_kurasi) ? 'selected' : '' }}>{{ $p->nama_periode }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                </div>
                                <div class="card-body p-0 position-relative" style="min-height: 300px;">
                                    @if(count($periodeSelesaiList) > 0)
                                        <div class="table-responsive h-100">
                                            <table class="table table-hover align-middle mb-0" id="top5Table">
                                                <thead class="bg-light text-muted small uppercase tracking-wider">
                                                    <tr>
                                                        <th class="pl-4 py-3 text-center" style="width: 50px;">Rank</th>
                                                        <th class="py-3">Produk & Brand</th>
                                                        <th class="py-3 text-center">Skor</th>
                                                        <th class="py-3 pr-4 text-right">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="top5TableBody">
                                                    @forelse($top5Produk as $index => $item)
                                                        <tr class="{{ $index < 3 ? 'bg-rank-' . ($index + 1) : '' }}">
                                                            <td class="pl-4 py-3 text-center">
                                                                @if($index === 0)
                                                                    <div class="rank-badge gold mx-auto"><i data-lucide="trophy"></i></div>
                                                                @elseif($index === 1)
                                                                    <div class="rank-badge silver mx-auto">2</div>
                                                                @elseif($index === 2)
                                                                    <div class="rank-badge bronze mx-auto">3</div>
                                                                @else
                                                                    <span class="font-weight-bold text-muted">#{{ $index + 1 }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="py-3">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="product-img-wrapper mr-3 shadow-sm border" style="width: 40px; height: 40px; border-radius: 6px; overflow: hidden; flex-shrink: 0;">
                                                                        @if($item->alternatif->foto_produk)
                                                                            <img src="{{ Storage::disk('supabase')->url($item->alternatif->foto_produk) }}" class="w-100 h-100" style="object-fit: cover;">
                                                                        @else
                                                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted"><i data-lucide="image" style="width: 16px;"></i></div>
                                                                        @endif
                                                                    </div>
                                                                    <div>
                                                                        <div class="font-weight-bold text-dark text-truncate" style="max-width: 180px;" title="{{ $item->alternatif->nama_produk }}">{{ $item->alternatif->nama_produk }}</div>
                                                                        <div class="small text-muted text-truncate" style="max-width: 180px;">{{ $item->alternatif->nama_brand_umkm }}</div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="py-3 text-center">
                                                                <span class="font-weight-bold text-primary">{{ number_format($item->total_score, 3) }}</span>
                                                            </td>
                                                            <td class="py-3 pr-4 text-right">
                                                                @if($item->status_layak === 'layak_retail')
                                                                    <span class="badge badge-pill badge-success px-3 py-1"><i data-lucide="check-circle" class="mr-1" style="width: 12px; height: 12px;"></i> Layak Retail</span>
                                                                @elseif($item->status_layak === 'layak_retail_bersyarat')
                                                                    <span class="badge badge-pill badge-warning px-3 py-1"><i data-lucide="alert-circle" class="mr-1" style="width: 12px; height: 12px;"></i> Layak Bersyarat</span>
                                                                @else
                                                                    <span class="badge badge-pill badge-danger px-3 py-1"><i data-lucide="x-circle" class="mr-1" style="width: 12px; height: 12px;"></i> Belum Layak</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada produk yang dikurasi pada periode ini.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 py-5">
                                            <div class="text-muted text-center py-4">
                                                <i data-lucide="inbox" class="d-block mb-3 text-secondary"
                                                    style="width: 48px; height: 48px; margin: 0 auto;"></i>
                                                Belum ada kurasi yang berjalan sebelumnya.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Chart Tren 5 Periode -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-header bg-white font-weight-bold">
                                    <i data-lucide="bar-chart-3" class="text-primary mr-2"></i> Tren 5 Periode Terakhir (Layak Retail)
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center">
                                    @if(count($trendData) > 0)
                                        <div style="position: relative; height: 300px; width: 100%;">
                                            <canvas id="trendLineChart"></canvas>
                                        </div>
                                    @else
                                        <div class="text-muted text-center py-5">
                                            <i data-lucide="trending-down" class="text-secondary d-block mb-3"
                                                style="width: 48px; height: 48px; margin: 0 auto;"></i>
                                            Belum ada kurasi yang berjalan sebelumnya.
                                        </div>
                                    @endif
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
        document.addEventListener('DOMContentLoaded', function () {
            // 1. PIE CHART BOBOT KRITERIA
            const kriteriaLabels = {!! json_encode($kriteriaBobots->pluck('nama_kriteria')) !!};
            let kriteriaBobot = {!! json_encode($kriteriaBobots->map(function ($k) {
                return $k->bobot_prioritas ? (float) $k->bobot_prioritas * 100 : 0; 
            })) !!};

            const totalBobot = kriteriaBobot.reduce((a, b) => a + b, 0);

            let backgroundColors = [
                '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545',
                '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'
            ];

            if (totalBobot === 0 && kriteriaLabels.length > 0) {
                kriteriaBobot = kriteriaBobot.map(() => 100 / kriteriaLabels.length);
                backgroundColors = kriteriaLabels.map(() => '#e9ecef');
            }

            const ctxPie = document.getElementById('kriteriaPieChart');
            if (ctxPie) {
                new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: kriteriaLabels,
                        datasets: [{
                            data: kriteriaBobot,
                            backgroundColor: backgroundColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                padding: 25,
                                labels: { font: { size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.label || '';
                                        if (label) label += ': ';
                                        if (totalBobot === 0) label += '0%';
                                        else label += Math.round(context.raw * 100) / 100 + '%';
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. LINE CHART TREN
            @if(count($trendData) > 0)
                const trendLabels = {!! json_encode($trendData->pluck('nama_periode')) !!};
                const trendValues = {!! json_encode($trendData->pluck('jumlah_layak')) !!};

                const ctxLine = document.getElementById('trendLineChart');
                if (ctxLine) {
                    new Chart(ctxLine, {
                        type: 'line',
                        data: {
                            labels: trendLabels,
                            datasets: [{
                                label: 'Produk Layak Retail',
                                data: trendValues,
                                borderColor: '#198754',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#198754',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#198754',
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.raw + ' Produk Layak';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            @endif


        });
    </script>
@endpush