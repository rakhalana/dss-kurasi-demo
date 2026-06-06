<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\KurasiScoreService;

class AuthController extends Controller
{
    // Mengautentikasi pengguna yang mencoba login
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Regenerasi sesi untuk mencegah session fixation
            $request->session()->regenerate();

            // Simpan email di cookie selama 30 hari jika opsi "Ingat Saya" aktif
            if ($remember) {
                \Illuminate\Support\Facades\Cookie::queue('remember_email', $request->email, 43200); // 30 hari
            } else {
                \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('remember_email'));
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Mengarahkan pengguna ke halaman dashboard sesuai dengan role masing-masing
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $totalKriteria = \App\Models\Kriteria::count();
            $totalPeriodeKurasi = \App\Models\PeriodeKurasi::count();
            $totalProduk = \App\Models\Alternatif::count();

            // Mendapatkan sesi AHP yang saat ini berstatus aktif
            $activeSesi = \Illuminate\Support\Facades\DB::table('ahp_sesi')->where('status_aktif', true)->first();

            // Mengambil daftar kriteria beserta bobot prioritas terbarunya dari sesi AHP aktif
            $kriteriaBobots = \Illuminate\Support\Facades\DB::table('kriteria')
                ->leftJoin('ahp_bobot', function ($join) use ($activeSesi) {
                    $join->on('kriteria.id_kriteria', '=', 'ahp_bobot.id_kriteria');
                    if ($activeSesi) {
                        $join->where('ahp_bobot.id_ahp_sesi', '=', $activeSesi->id_ahp_sesi);
                    } else {
                        $join->where('ahp_bobot.id_ahp_sesi', '=', -1);
                    }
                })
                ->orderBy('kriteria.urutan_tampil')
                ->select('kriteria.nama_kriteria', 'ahp_bobot.bobot_prioritas')
                ->get();

            // === FITUR DASHBOARD BARU ===
            $scoreService = app(KurasiScoreService::class);

            // 1. Total Produk Layak Retail dari semua periode yang selesai
            $totalLayakRetail = $scoreService->countTotalLayakRetailAllPeriode();

            // 2. Daftar semua periode selesai untuk dropdown Top 5
            $periodeSelesaiList = \App\Models\PeriodeKurasi::where('status_kurasi', 'selesai')
                ->orderBy('tanggal_kurasi', 'desc')
                ->get();

            // 3. Top 5 produk dari periode yang dipilih atau terakhir selesai
            $selectedPeriodeId = request('periode');
            if ($selectedPeriodeId) {
                $latestPeriode = $periodeSelesaiList->firstWhere('id_periode_kurasi', $selectedPeriodeId);
            } else {
                $latestPeriode = $periodeSelesaiList->first();
            }

            $top5Produk = [];
            if ($latestPeriode) {
                $latestPeriode->load(['periodeAlternatif.alternatif.legalitas', 'ahpSesi.bobot.kriteria']);
                $top5Produk = $scoreService->getTopProducts($latestPeriode, 5);
            }

            // 4. Data tren 5 periode terakhir (jumlah produk layak retail per periode)
            $trendData = $scoreService->getTrendData(5);

            return view('admin.dashboard', compact(
                'totalKriteria',
                'totalPeriodeKurasi',
                'totalProduk',
                'kriteriaBobots',
                'totalLayakRetail',
                'periodeSelesaiList',
                'top5Produk',
                'latestPeriode',
                'trendData'
            ));
        } elseif ($user->role === 'kurator') {
            $userId = Auth::id();

            // Mengambil tugas kurasi aktif yang ditugaskan kepada kurator login
            $recentActiveTask = \App\Models\PeriodeKurasi::where('id_kurator', $userId)
                ->whereIn('status_kurasi', ['berlangsung', 'belum'])
                ->orderBy('created_at', 'desc')
                ->first();

            $progress = [
                'assessed' => 0,
                'total' => 0,
                'percentage' => 0
            ];

            // Jika terdapat tugas aktif, hitung progress penilaian kurasi
            if ($recentActiveTask) {
                $totalKriteria = \App\Models\Kriteria::count();
                $totalProdukLolos = \App\Models\PeriodeAlternatif::where('id_periode_kurasi', $recentActiveTask->id_periode_kurasi)
                    ->where('status_lolos_legalitas', true)
                    ->count();

                $produkDinilai = 0;
                if ($totalProdukLolos > 0 && $totalKriteria > 0) {
                    $paIds = \App\Models\PeriodeAlternatif::where('id_periode_kurasi', $recentActiveTask->id_periode_kurasi)
                        ->where('status_lolos_legalitas', true)
                        ->pluck('id_periode_alternatif');

                    foreach ($paIds as $paId) {
                        // Periksa apakah kriteria yang dinilai oleh kurator untuk produk ini sudah lengkap
                        $count = \App\Models\PenilaianKurasi::where('id_periode_alternatif', $paId)
                            ->where('dinilai_oleh', $userId)
                            ->count();
                        if ($count >= $totalKriteria) {
                            $produkDinilai++;
                        }
                    }
                }

                $progress = [
                    'assessed' => $produkDinilai,
                    'total' => $totalProdukLolos,
                    'percentage' => $totalProdukLolos > 0 ? round(($produkDinilai / $totalProdukLolos) * 100) : 0
                ];
            }

            $activeTasksCount = \App\Models\PeriodeKurasi::where('id_kurator', $userId)
                ->whereIn('status_kurasi', ['berlangsung', 'belum'])
                ->count();

            $completedTasksCount = \App\Models\PeriodeKurasi::where('id_kurator', $userId)
                ->where('status_kurasi', 'selesai')
                ->count();

            // $totalProductsCount = \App\Models\PeriodeAlternatif::whereHas('periodeKurasi', function ($q) use ($userId) {
            //     $q->where('id_kurator', $userId);
            // })->count();
            $totalProductsCount = \App\Models\PenilaianKurasi::where('dinilai_oleh', $userId)
                ->distinct('id_periode_alternatif')
                ->count();


            return view('kurator.dashboard', compact(
                'recentActiveTask',
                'progress',
                'activeTasksCount',
                'completedTasksCount',
                'totalProductsCount'
            ));
        }

        Auth::logout();
        return redirect('/')->withErrors(['email' => 'Role pengguna tidak valid.']);
    }



    // Melakukan logout pengguna dari sistem
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

