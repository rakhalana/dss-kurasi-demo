<?php

namespace App\Http\Controllers;

use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\PenilaianKurasi;
use App\Models\PeriodeAlternatif;
use App\Models\PeriodeKurasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenilaianKuratorController extends Controller
{
    // Menampilkan daftar periode kurasi yang ditugaskan ke kurator yang sedang login
    public function index()
    {
        $userId = Auth::id();
        
        $periodes = PeriodeKurasi::where('id_kurator', $userId)
            ->orWhereNull('id_kurator')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($periodes as $periode) {
            $totalProdukLolos = PeriodeAlternatif::where('id_periode_kurasi', $periode->id_periode_kurasi)
                ->where('status_lolos_legalitas', true)
                ->count();
                
            $totalKriteria = Kriteria::count();
            $produkDinilai = 0;
            
            if ($totalProdukLolos > 0 && $totalKriteria > 0) {
                $periodeAlternatifs = PeriodeAlternatif::where('id_periode_kurasi', $periode->id_periode_kurasi)
                    ->where('status_lolos_legalitas', true)
                    ->get();
                    
                foreach ($periodeAlternatifs as $pa) {
                    $nilaiCount = PenilaianKurasi::where('id_periode_alternatif', $pa->id_periode_alternatif)
                        ->where('dinilai_oleh', $userId)
                        ->count();
                        
                    if ($nilaiCount >= $totalKriteria) {
                        $produkDinilai++;
                    }
                }
            }
            
            $periode->total_produk_lolos = $totalProdukLolos;
            $periode->produk_dinilai = $produkDinilai;
            $periode->progress_percentage = $totalProdukLolos > 0 ? round(($produkDinilai / $totalProdukLolos) * 100) : 0;
        }

        return view('kurator.penilaian.index', compact('periodes'));
    }

    // Menampilkan daftar produk alternatif dalam satu periode kurasi tertentu
    public function detailPeriode($id_periode)
    {
        $userId = Auth::id();
        $periode = PeriodeKurasi::findOrFail($id_periode);
        
        if ($periode->id_kurator != null && $periode->id_kurator != $userId) {
            abort(403, 'Anda tidak memiliki akses ke periode kurasi ini.');
        }

        $totalKriteria = Kriteria::count();

        $produkList = PeriodeAlternatif::with('alternatif')
            ->where('id_periode_kurasi', $id_periode)
            ->orderBy('urutan_input', 'asc')
            ->get();
            
        foreach ($produkList as $produk) {
            $nilaiCount = PenilaianKurasi::where('id_periode_alternatif', $produk->id_periode_alternatif)
                ->where('dinilai_oleh', $userId)
                ->count();
            
            $produk->is_dinilai = ($totalKriteria > 0 && $nilaiCount >= $totalKriteria);
        }

        return view('kurator.penilaian.detail', compact('periode', 'produkList'));
    }

    // Menampilkan lembar ruang kerja (Workspace / Wizard) untuk proses input penilaian
    public function workspace($id_periode, $id_alternatif = null)
    {
        $userId = Auth::id();
        $periode = PeriodeKurasi::findOrFail($id_periode);

        if ($periode->id_kurator != null && $periode->id_kurator != $userId) {
            abort(403, 'Anda tidak memiliki akses ke periode kurasi ini.');
        }

        // Jika status periode kurasi belum dimulai, ubah status menjadi berlangsung dan tugaskan ke kurator login
        if ($periode->status_kurasi == 'belum') {
            $periode->update([
                'status_kurasi' => 'berlangsung',
                'id_kurator' => $periode->id_kurator ?? $userId
            ]);
        }

        $antreanProduk = PeriodeAlternatif::with('alternatif')
            ->where('id_periode_kurasi', $id_periode)
            ->where('status_lolos_legalitas', true)
            ->orderBy('urutan_input', 'asc')
            ->get();

        if ($antreanProduk->isEmpty()) {
            return redirect()->route('kurator.penilaian.detail', $id_periode)
                ->with('error', 'Belum ada produk yang lolos legalitas untuk dinilai pada periode ini.');
        }

        $totalKriteria = Kriteria::count();

        foreach ($antreanProduk as $p) {
            $nilaiCount = PenilaianKurasi::where('id_periode_alternatif', $p->id_periode_alternatif)
                ->where('dinilai_oleh', $userId)
                ->count();
            $p->is_dinilai = ($totalKriteria > 0 && $nilaiCount >= $totalKriteria);
        }

        // Menentukan produk aktif yang sedang atau akan dinilai oleh kurator
        $produkAktif = null;
        if ($id_alternatif) {
            $produkAktif = $antreanProduk->firstWhere('id_alternatif', $id_alternatif);
            if (!$produkAktif) {
                abort(404, 'Produk tidak ditemukan atau tidak eligible untuk dinilai.');
            }
        } else {
            // Cari produk pertama dalam antrean yang belum dinilai oleh kurator
            $produkAktif = $antreanProduk->firstWhere('is_dinilai', false);
            
            if (!$produkAktif) {
                $semuaDinilai = true;
                $produkAktif = $antreanProduk->first();
            } else {
                return redirect()->route('kurator.penilaian.workspace', [
                    'id_periode' => $id_periode, 
                    'id_alternatif' => $produkAktif->id_alternatif
                ]);
            }
        }

        $semuaDinilai = $semuaDinilai ?? ($antreanProduk->every(fn($item) => $item->is_dinilai));

        $kriteriaList = Kriteria::with(['scales' => function($q) {
            $q->where('is_aktif', true)->orderBy('nilai_skala', 'desc');
        }])->orderBy('urutan_tampil', 'asc')->get();

        $penilaianExisting = PenilaianKurasi::where('id_periode_alternatif', $produkAktif->id_periode_alternatif)
            ->where('dinilai_oleh', $userId)
            ->get()
            ->keyBy('id_kriteria');

        return view('kurator.penilaian.workspace', compact(
            'periode', 
            'antreanProduk', 
            'produkAktif', 
            'kriteriaList',
            'penilaianExisting',
            'semuaDinilai'
        ));
    }

    // Menyimpan atau memperbarui nilai untuk satu kriteria tertentu (secara asinkron / AJAX)
    public function storePenilaian(Request $request, $id_periode, $id_alternatif, $id_kriteria)
    {
        $request->validate([
            'nilai_input' => 'required|integer|min:1|max:5',
        ]);

        $userId = Auth::id();
        
        $periodeAlternatif = PeriodeAlternatif::where('id_periode_kurasi', $id_periode)
            ->where('id_alternatif', $id_alternatif)
            ->firstOrFail();

        // Menugaskan kurator ke periode kurasi secara otomatis jika sebelumnya belum di-assign
        $periode = PeriodeKurasi::find($id_periode);
        if ($periode && $periode->id_kurator == null) {
            $periode->update(['id_kurator' => $userId]);
        }

        $penilaian = PenilaianKurasi::updateOrCreate(
            [
                'id_periode_alternatif' => $periodeAlternatif->id_periode_alternatif,
                'id_kriteria' => $id_kriteria,
            ],
            [
                'nilai_input' => $request->nilai_input,
                'dinilai_oleh' => $userId,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil disimpan',
            'data' => $penilaian
        ]);
    }

    // Mengubah status periode kurasi menjadi selesai
    public function selesaikanKurasi($id_periode)
    {
        $userId = Auth::id();
        $periode = PeriodeKurasi::findOrFail($id_periode);

        if ($periode->id_kurator != null && $periode->id_kurator != $userId) {
            abort(403, 'Anda tidak memiliki akses ke periode kurasi ini.');
        }

        $periode->update([
            'status_kurasi' => 'selesai',
        ]);

        return redirect()->route('kurator.penilaian.selesai', $id_periode);
    }

    // Menampilkan halaman sukses / rangkuman ketika kurasi telah selesai disubmit
    public function halamanSelesai($id_periode)
    {
        $userId = Auth::id();
        $periode = PeriodeKurasi::findOrFail($id_periode);

        if ($periode->id_kurator != null && $periode->id_kurator != $userId) {
            abort(403, 'Anda tidak memiliki akses ke periode kurasi ini.');
        }

        $totalProduk = PeriodeAlternatif::where('id_periode_kurasi', $id_periode)
            ->where('status_lolos_legalitas', true)
            ->count();

        $totalKriteria = Kriteria::count();

        return view('kurator.penilaian.selesai', compact('periode', 'totalProduk', 'totalKriteria'));
    }
}
