<?php

namespace App\Services;

use App\Models\PeriodeKurasi;
use App\Models\Kriteria;
use App\Models\AhpBobot;
use App\Models\PenilaianKurasi;

/**
 * Service untuk menghitung skor kurasi dan menentukan status kelayakan retail produk.
 * Logika ini digunakan oleh HasilKurasiController dan Dashboard.
 */
class KurasiScoreService
{
    // Memetakan nilai selisih/gap menjadi bobot nilai standar Profile Matching
    private function mapGapToWeight($gap)
    {
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

    /**
     * Menghitung skor dan status kelayakan retail untuk semua produk dalam satu periode.
     * Mengembalikan array of objects yang sudah diurutkan berdasarkan skor tertinggi.
     */
    public function calculateResults(PeriodeKurasi $periode): array
    {
        // memastikan relasi sudah dimuat
        if (!$periode->relationLoaded('periodeAlternatif')) {
            $periode->load(['periodeAlternatif.alternatif.legalitas', 'ahpSesi.bobot.kriteria']);
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

                    // Mengambil deskripsi skala target nilai yang belum tercapai
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

            // Menentukan status kelayakan retail berdasarkan kriteria dan gap
            if (!$isLolosLegalitas) {
                $statusLayak = 'belum_layak';
            } elseif (!$hasNegativeGap) {
                $statusLayak = 'layak_retail';
            } elseif ($totalScore >= 4.5 && $minGap >= -1) {
                $statusLayak = 'layak_retail_bersyarat';
            } else {
                $statusLayak = 'belum_layak';
            }

            $results[] = (object) [
                'pa' => $pa,
                'alternatif' => $pa->alternatif,
                'total_score' => $totalScore,
                'status_layak' => $statusLayak,
                'min_gap' => $minGap,
                'evaluations' => $evaluations,
                'missing_docs' => $missingDocs,
                'is_lolos_legalitas' => $isLolosLegalitas,
                'breakdown' => $breakdown
            ];
        }

        // Mengurutkan produk berdasarkan skor akhir tertinggi (perankingan)
        usort($results, function ($a, $b) {
            if ($a->total_score == $b->total_score)
                return 0;
            return ($a->total_score > $b->total_score) ? -1 : 1;
        });

        return $results;
    }

    /**
     * Mendapatkan jumlah produk layak retail (layak_retail + layak_retail_bersyarat)
     * dari satu periode kurasi.
     */
    public function countLayakRetail(PeriodeKurasi $periode): int
    {
        $results = $this->calculateResults($periode);
        return collect($results)->whereIn('status_layak', ['layak_retail', 'layak_retail_bersyarat'])->count();
    }

    /**
     * Mendapatkan jumlah produk layak retail dari semua periode yang selesai.
     */
    public function countTotalLayakRetailAllPeriode(): int
    {
        $periodes = PeriodeKurasi::with(['periodeAlternatif.alternatif.legalitas', 'ahpSesi.bobot.kriteria'])
            ->where('status_kurasi', 'selesai')
            ->get();

        $layakIds = [];
        foreach ($periodes as $periode) {
            $results = $this->calculateResults($periode);
            foreach ($results as $res) {
                if (in_array($res->status_layak, ['layak_retail', 'layak_retail_bersyarat'])) {
                    $layakIds[$res->alternatif->id_alternatif] = true;
                }
            }
        }

        return count($layakIds);
    }

    /**
     * Mendapatkan Top N produk berdasarkan skor tertinggi dalam satu periode.
     */
    public function getTopProducts(PeriodeKurasi $periode, int $limit = 5): array
    {
        $results = $this->calculateResults($periode);
        return array_slice($results, 0, $limit);
    }

    /**
     * Mendapatkan data tren jumlah produk layak retail dari N periode terakhir yang selesai.
     * Mengembalikan collection [{nama_periode, jumlah_layak, tanggal_kurasi}]
     */
    public function getTrendData(int $limit = 5)
    {
        $periodes = PeriodeKurasi::with(['periodeAlternatif.alternatif.legalitas', 'ahpSesi.bobot.kriteria'])
            ->where('status_kurasi', 'selesai')
            ->orderBy('tanggal_kurasi', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return $periodes->map(function ($periode) {
            return (object) [
                'nama_periode' => $periode->nama_periode,
                'jumlah_layak' => $this->countLayakRetail($periode),
                'tanggal_kurasi' => $periode->tanggal_kurasi,
            ];
        });
    }
}
