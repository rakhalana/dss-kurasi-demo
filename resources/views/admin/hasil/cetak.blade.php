<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Kurasi - {{ $periode->nama_periode }}</title>

    {{-- Google Fonts: Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Bootstrap 4 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    {{-- Vite Assets --}}
    @vite(['resources/scss/app.scss'])
</head>

<body class="report-body">
    <div class="no-print mb-4 text-right">
        <button onclick="window.print()" class="btn btn-primary shadow-sm px-4">
            Cetak Laporan
        </button>
        <button onclick="window.close()" class="btn btn-light border px-4">Tutup</button>
    </div>

    <div class="report-header">
        <div class="report-title">Laporan Hasil Penilaian Kurasi Produk UMKM</div>
        <div>Periode: {{ $periode->nama_periode }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td width="150"><strong>Tanggal Pelaksanaan</strong></td>
                <td width="10">:</td>
                <td>{{ \Carbon\Carbon::parse($periode->tanggal_kurasi)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td><strong>Kurator Penilai</strong></td>
                <td>:</td>
                <td>{{ $periode->kurator->name }}</td>
            </tr>
            <tr>
                <td><strong>Status Kurasi</strong></td>
                <td>:</td>
                <td>Selesai</td>
            </tr>
            <tr>
                <td><strong>Total Produk</strong></td>
                <td>:</td>
                <td>{{ count($results) }} Produk</td>
            </tr>
        </table>
    </div>

    {{-- TABEL 1: PRODUK LAYAK RETAIL --}}
    <h5 class="font-weight-bold mb-3" style="text-decoration: underline;">I. Daftar Produk Layak Retail</h5>
    <table class="table-report">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 30%;">Nama Produk & Brand/UMKM</th>
                <th style="width: 20%;">Nama Pemilik</th>
                <th style="width: 15%; text-align: center;">Skor Akhir</th>
                <th style="width: 30%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $noLolos = 1; @endphp
            @foreach($results as $res)
                @if($res->status_layak === 'layak_retail')
                    <tr>
                        <td class="text-center">{{ $noLolos++ }}</td>
                        <td>
                            <strong>{{ $res->alternatif->nama_produk }}</strong><br>
                            <small
                                class="text-muted d-block mb-1">{{ $res->alternatif->brand ?? $res->alternatif->nama_brand_umkm }}</small>
                        </td>
                        <td>{{ $res->alternatif->nama_pemilik }}</td>
                        <td class="text-center font-weight-bold">{{ number_format($res->total_score, 3) }}</td>
                        <td>
                            <span class="font-weight-bold text-dark" style="font-size: 0.85rem;">Memenuhi Standar Kelayakan
                                Retail</span>
                        </td>
                    </tr>
                @endif
            @endforeach
            @if($noLolos == 1)
                <tr>
                    <td colspan="5" class="text-center text-muted font-italic">Tidak ada produk yang dinyatakan layak retail
                        murni.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- TABEL 2: PRODUK LAYAK RETAIL BERSYARAT --}}
    <h5 class="font-weight-bold mb-3" style="text-decoration: underline;">II. Daftar Produk Layak Retail Bersyarat</h5>
    <table class="table-report">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 30%;">Nama Produk & Brand/UMKM</th>
                <th style="width: 20%;">Nama Pemilik</th>
                <th style="width: 15%; text-align: center;">Skor Akhir</th>
                <th style="width: 30%;">Catatan Perbaikan</th>
            </tr>
        </thead>
        <tbody>
            @php $noLolosBersyarat = 1; @endphp
            @foreach($results as $res)
                @if($res->status_layak === 'layak_retail_bersyarat')
                    <tr>
                        <td class="text-center">{{ $noLolosBersyarat++ }}</td>
                        <td>
                            <strong>{{ $res->alternatif->nama_produk }}</strong><br>
                            <small
                                class="text-muted d-block mb-1">{{ $res->alternatif->brand ?? $res->alternatif->nama_brand_umkm }}</small>
                        </td>
                        <td>{{ $res->alternatif->nama_pemilik }}</td>
                        <td class="text-center font-weight-bold">{{ number_format($res->total_score, 3) }}</td>
                        <td>
                            <div class="eval-note text-justify">
                                <div class="font-weight-bold text-dark mb-2"
                                    style="font-size: 0.85rem; text-decoration: underline;">Daftar Perbaikan (To-Do):</div>
                                <div class="mb-2">
                                    @foreach($res->evaluations as $eval)
                                        <div class="eval-item text-dark mb-1" style="font-size: 0.82rem; line-height: 1.4;">
                                            <span class="todo-box">[ ]</span> Lakukan perbaikan pada kriteria
                                            <strong>{{ $eval['kriteria'] }}</strong> dengan memastikan
                                            {{ lcfirst($eval['target_desc']) }}
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    <strong class="text-dark d-block mb-1" style="font-size: 0.82rem;">Catatan Evaluasi Kurator:</strong>
                                    <span class="text-muted d-block" style="font-size: 0.8rem; line-height: 1.4;">
                                        {{ $res->pa->catatan_kurator ?? 'Lakukan penyesuaian pada aspek di atas agar memenuhi standar retail sepenuhnya.' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif
            @endforeach
            @if($noLolosBersyarat == 1)
                <tr>
                    <td colspan="5" class="text-center text-muted font-italic">Tidak ada produk yang dinyatakan layak retail
                        bersyarat.</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- TABEL 3: PRODUK BELUM LAYAK --}}
    <h5 class="font-weight-bold mb-3" style="text-decoration: underline;">III. Daftar Produk Belum Layak & Catatan
        Evaluasi</h5>
    <table class="table-report">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 30%;">Nama Produk & Brand/UMKM</th>
                <th style="width: 20%;">Nama Pemilik</th>
                <th style="width: 15%; text-align: center;">Skor Akhir</th>
                <th style="width: 30%;">Catatan Evaluasi</th>
            </tr>
        </thead>
        <tbody>
            @php $noTidakLolos = 1; @endphp
            @foreach($results as $res)
                @if($res->status_layak === 'belum_layak')
                    <tr>
                        <td class="text-center">{{ $noTidakLolos++ }}</td>
                        <td>
                            <strong>{{ $res->alternatif->nama_produk }}</strong><br>
                            <small
                                class="text-muted d-block mb-1">{{ $res->alternatif->brand ?? $res->alternatif->nama_brand_umkm }}</small>
                        </td>
                        <td>{{ $res->alternatif->nama_pemilik }}</td>
                        <td class="text-center font-weight-bold">
                            @if(!$res->is_lolos_legalitas)
                                <span
                                    style="font-size: 0.8rem; font-weight: bold; text-transform: uppercase; display: block; line-height: 1.2;">Gagal<br>Legalitas</span>
                            @else
                                {{ number_format($res->total_score, 3) }}
                            @endif
                        </td>
                        <td>
                            @if(!$res->is_lolos_legalitas)
                                <div class="eval-note text-justify">
                                    <div class="font-weight-bold text-dark mb-2"
                                        style="font-size: 0.85rem; text-decoration: underline;">Dokumen Wajib Belum Lengkap:</div>
                                    <div class="mb-2">
                                        <div class="eval-item text-dark mb-1" style="font-size: 0.82rem; line-height: 1.4;">
                                            <span class="todo-box text-dark font-weight-bold">[✗]</span>
                                            <strong>{{ implode(', ', $res->missing_docs) }}</strong>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <strong class="text-dark d-block mb-1" style="font-size: 0.82rem;">Rekomendasi Tindak
                                            Lanjut:</strong>
                                        <span class="text-muted d-block font-italic" style="font-size: 0.8rem; line-height: 1.4;">
                                            Segera lengkapi dokumen legalitas yang kurang untuk mengikuti kurasi periode berikutnya.
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="eval-note text-justify">
                                    <div class="font-weight-bold text-dark mb-2"
                                        style="font-size: 0.85rem; text-decoration: underline;">Aspek yang Belum Memenuhi Standar:
                                    </div>
                                    <div class="mb-2">
                                        @foreach($res->evaluations as $eval)
                                            <div class="eval-item text-dark mb-1" style="font-size: 0.82rem; line-height: 1.4;">
                                                <span class="todo-box">[ ]</span> Lakukan perbaikan pada kriteria
                                                <strong>{{ $eval['kriteria'] }}</strong> dengan memastikan
                                                {{ lcfirst($eval['target_desc']) }}
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-3">
                                        <strong class="text-dark d-block mb-1" style="font-size: 0.82rem;">Catatan Evaluasi Kurator:</strong>
                                        <span class="text-muted d-block" style="font-size: 0.8rem; line-height: 1.4;">
                                            {{ $res->pa->catatan_kurator ?? 'Lakukan perbaikan menyeluruh pada aspek di atas agar siap diajukan kembali pada periode kurasi berikutnya.' }}
                                        </span>
                                    </div>

                                </div>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
            @if($noTidakLolos == 1)
                <tr>
                    <td colspan="5" class="text-center text-muted font-italic">Seluruh produk memenuhi standar dasar
                        kelayakan.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="mt-4 p-3 border rounded bg-light" style="font-size: 0.85rem; page-break-inside: avoid;">
        <h6 class="font-weight-bold mb-3">Keterangan Status & Tahapan Lanjutan UMKM:</h6>
        <div class="row">
            <div class="col-4">
                <strong>1. Layak Retail:</strong><br>
                Produk direkomendasikan dan siap dipasarkan secara retail.
                <div class="mt-2 text-muted" style="font-size: 0.8rem; line-height: 1.4;">
                    <i class="font-weight-bold">Tahapan lanjutan:</i> UMKM dapat segera berkoordinasi dengan pengelola/retailer untuk proses onboarding, penyediaan stok, dan penandatanganan PKS.
                </div>
            </div>
            <div class="col-4 border-left border-right">
                <strong>2. Layak Retail Bersyarat:</strong><br>
                Produk berpotensi namun memerlukan sedikit penyesuaian.
                <div class="mt-2 text-muted" style="font-size: 0.8rem; line-height: 1.4;">
                    <i class="font-weight-bold">Tahapan lanjutan:</i> UMKM wajib melakukan perbaikan sesuai catatan kurator. Setelah diperbaiki, laporkan kembali untuk diverifikasi ulang tanpa proses kurasi awal.
                </div>
            </div>
            <div class="col-4">
                <strong>3. Belum Layak:</strong><br>
                Produk belum memenuhi standar dasar kelayakan retail.
                <div class="mt-2 text-muted" style="font-size: 0.8rem; line-height: 1.4;">
                    <i class="font-weight-bold">Tahapan lanjutan:</i> UMKM disarankan melakukan perbaikan menyeluruh secara mandiri atau mengikuti pendampingan, lalu dapat mendaftar pada periode kurasi selanjutnya.
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 pt-4">
        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 250px; text-align: center;">
                <p class="mb-5">Dicetak pada: {{ now()->translatedFormat('d F Y') }}</p>
                <br><br>
                <p class="font-weight-bold mb-0">( ____________________ )</p>
                <p class="small">Kurator Penilai</p>
            </div>
        </div>
    </div>

    <script>
        window.onload = function () {
            // window.print();
        }
    </script>
</body>

</html>