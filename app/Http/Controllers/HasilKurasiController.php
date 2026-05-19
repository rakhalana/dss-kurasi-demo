<?php

namespace App\Http\Controllers;

use App\Models\PeriodeKurasi;
use App\Models\Kriteria;
use App\Models\AhpBobot;
use App\Models\PenilaianKurasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasilKurasiController extends Controller
{
    // Menampilkan daftar periode kurasi yang sudah selesai
    public function index()
    {
        $user = Auth::user();

        $query = PeriodeKurasi::withCount('periodeAlternatif')
            ->where('status_kurasi', 'selesai')
            ->orderBy('tanggal_kurasi', 'desc');

        // Kurator hanya diperbolehkan melihat hasil kurasi yang ditugaskan kepadanya
        if ($user->role === 'kurator') {
            $query->where('id_kurator', $user->id);
        }

        $periodes = $query->get();

        return view('admin.hasil.index', compact('periodes'));
    }

    // Menampilkan detail hasil kurasi (Leaderboard/peringkat) untuk periode tertentu
    public function detail($id)
    {
        $data = $this->prepareDetailData($id);

        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        return view('admin.hasil.detail', $data);
    }

    // Mencetak laporan hasil kurasi untuk periode tertentu
    public function cetak($id)
    {
        $data = $this->prepareDetailData($id);

        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        return view('admin.hasil.cetak', $data);
    }

    // Mempersiapkan data hasil kurasi, menghitung skor Profile Matching dan AHP untuk detail dan cetak
    private function prepareDetailData($id)
    {
        $user = Auth::user();
        
        $periode = PeriodeKurasi::with(['kurator', 'ahpSesi.bobot.kriteria', 'periodeAlternatif.alternatif.legalitas'])
            ->findOrFail($id);

        // Validasi keamanan: Pastikan kurator hanya mengakses periode miliknya sendiri
        if ($user->role === 'kurator' && $periode->id_kurator !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke hasil kurasi ini.');
        }

        if ($periode->status_kurasi !== 'selesai') {
            return redirect()->route('hasil.index')->with('error', 'Hasil kurasi hanya dapat dilihat untuk periode yang sudah selesai.');
        }

        $bobots = AhpBobot::where('id_ahp_sesi', $periode->id_ahp_sesi)
            ->pluck('bobot_prioritas', 'id_kriteria');

        $kriterias = Kriteria::with('scales')->orderBy('urutan_tampil')->get();

        $results = [];
        
        foreach ($periode->periodeAlternatif as $pa) {
            $totalScore = 0;
            $hasNegativeGap = false;
            $minGap = 0;
            $evaluations = [];
            $breakdown = [];

            foreach ($kriterias as $k) {
                $penilaian = PenilaianKurasi::where('id_periode_alternatif', $pa->id_periode_alternatif)
                    ->where('id_kriteria', $k->id_kriteria)
                    ->first();

                $nilaiAktual = $penilaian ? $penilaian->nilai_input : 0;
                $nilaiTarget = $k->target_nilai;
                
                // Menghitung selisih/gap (Aktual - Target)
                $gap = $nilaiAktual - $nilaiTarget;

                // Cek jika terdapat gap negatif (nilai aktual di bawah target kelulusan)
                if ($gap < 0) {
                    $hasNegativeGap = true;
                    if ($gap < $minGap) {
                        $minGap = $gap;
                    }

                    // Mengambil deskripsi skala target nilai yang belum tercapai untuk laporan evaluasi
                    $targetScale = $k->scales->where('nilai_skala', $nilaiTarget)->first();
                    $targetDesc = $targetScale ? $targetScale->deskripsi_skala : 'Standar target belum tercapai';

                    $evaluations[] = [
                        'kriteria' => $k->nama_kriteria,
                        'aktual' => $nilaiAktual,
                        'target' => $nilaiTarget,
                        'target_desc' => $targetDesc,
                        'gap' => $gap
                    ];
                }

                // Memetakan nilai gap ke bobot nilai Profile Matching
                $bobotGap = $this->mapGapToWeight($gap);
                $ahpWeight = $bobots[$k->id_kriteria] ?? 0;
                
                // Menghitung skor kriteria (Bobot Gap PM * Bobot AHP)
                $skorKriteria = $bobotGap * $ahpWeight;
                $totalScore += $skorKriteria;

                $breakdown[$k->id_kriteria] = [
                    'aktual' => $nilaiAktual,
                    'target' => $nilaiTarget,
                    'gap' => $gap,
                    'bobot_gap' => $bobotGap,
                    'ahp_weight' => $ahpWeight,
                    'skor' => $skorKriteria
                ];
            }

            $legalitas = $pa->alternatif->legalitas;
            $missingDocs = [];
            $isLolosLegalitas = $legalitas ? $legalitas->lolos_filter : true;

            // Jika legalitas tidak lolos, kumpulkan berkas yang kurang dan set skor 0 (gugur)
            if ($legalitas && !$legalitas->lolos_filter) {
                if (!$legalitas->is_nib)
                    $missingDocs[] = 'NIB';
                if (!$legalitas->is_bpom && !$legalitas->is_sp_pirt) {
                    $missingDocs[] = 'BPOM / SP-PIRT';
                }
                if (!$legalitas->is_sertifikat_halal)
                    $missingDocs[] = 'Sertifikat Halal';
                
                $totalScore = 0;
            }

            // Menentukan status kelulusan kurasi berdasarkan kriteria kelolosan dan gap
            if (!$isLolosLegalitas) {
                $statusLolos = 'tidak_lolos';
            } elseif (!$hasNegativeGap) {
                $statusLolos = 'lolos';
            } elseif ($totalScore >= 4.5 && $minGap >= -1) {
                $statusLolos = 'lolos_bersyarat';
            } else {
                $statusLolos = 'tidak_lolos';
            }

            $results[] = (object) [
                'pa' => $pa,
                'alternatif' => $pa->alternatif,
                'total_score' => $totalScore,
                'status_lolos' => $statusLolos,
                'min_gap' => $minGap,
                'evaluations' => $evaluations,
                'missing_docs' => $missingDocs,
                'is_lolos_legalitas' => $isLolosLegalitas,
                'breakdown' => $breakdown
            ];
        }

        // Mengurutkan produk alternatif berdasarkan skor akhir tertinggi (perankingan)
        usort($results, function ($a, $b) {
            if ($a->total_score == $b->total_score)
                return 0;
            return ($a->total_score > $b->total_score) ? -1 : 1;
        });

        return compact('periode', 'kriterias', 'results', 'bobots');
    }

    // Memetakan nilai selisih/gap menjadi bobot nilai standar Profile Matching
    private function mapGapToWeight($gap)
    {
        // Aturan pemetaan nilai gap ke bobot standar Profile Matching
        $map = [
            '0' => 5,
            '1' => 4.5,
            '-1' => 4,
            '2' => 3.5,
            '-2' => 3,
            '3' => 2.5,
            '-3' => 2,
            '4' => 1.5,
            '-4' => 1,
            '5' => 0.5,
            '-5' => 0,
        ];

        return $map[(string) $gap] ?? 0;
    }
}
