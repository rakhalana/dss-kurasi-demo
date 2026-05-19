<?php

namespace App\Http\Controllers;

use App\Models\PeriodeKurasi;
use App\Models\PeriodeAlternatif;
use App\Models\Alternatif;
use App\Models\AhpSesi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodeKurasiController extends Controller
{
    // Menampilkan daftar periode kurasi di dashboard admin
    public function index()
    {
        $periode = PeriodeKurasi::with(['kurator', 'ahpSesi'])->withCount('periodeAlternatif')->latest()->get();
        $kurators = User::where('role', 'kurator')->get();
        $activeAHP = AhpSesi::where('status_aktif', true)->first();

        return view('admin.kurasi.index', compact('periode', 'kurators', 'activeAHP'));
    }

    // Menyimpan data periode kurasi baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'nama_periode' => 'required|string|max:100',
            'tanggal_kurasi' => 'required|date',
            'penanggung_jawab' => 'required|string|max:100',
            'id_kurator' => 'nullable|exists:users,id',
            'catatan_umum' => 'nullable|string',
        ]);

        // Memastikan ada sesi AHP yang aktif sebelum membuat periode kurasi baru
        $activeAHP = AhpSesi::where('status_aktif', true)->first();
        if (!$activeAHP) {
            return redirect()->back()->with('error', 'Gagal membuat periode: Tidak ada Sesi AHP yang aktif saat ini.');
        }

        $tanggal = \Carbon\Carbon::parse($request->tanggal_kurasi);

        PeriodeKurasi::create([
            'nama_periode' => $request->nama_periode,
            'tanggal_kurasi' => $request->tanggal_kurasi,
            'bulan' => $tanggal->month,
            'tahun' => $tanggal->year,
            'penanggung_jawab' => $request->penanggung_jawab,
            'id_kurator' => $request->id_kurator,
            'id_ahp_sesi' => $activeAHP->id_ahp_sesi,
            'status_kurasi' => 'belum',
            'catatan_umum' => $request->catatan_umum,
        ]);

        return redirect()->route('admin.kurasi.index')->with('success', 'Periode Kurasi berhasil ditambahkan.');
    }

    // Memperbarui data periode kurasi tertentu berdasarkan ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_periode' => 'required|string|max:100',
            'tanggal_kurasi' => 'required|date',
            'penanggung_jawab' => 'required|string|max:100',
            'id_kurator' => 'nullable|exists:users,id',
            'catatan_umum' => 'nullable|string',
            'status_kurasi' => 'required|in:belum,berlangsung,selesai',
        ]);

        $tanggal = \Carbon\Carbon::parse($request->tanggal_kurasi);
        $periode = PeriodeKurasi::findOrFail($id);

        // Validasi keamanan: hanya memperbolehkan edit jika status kurasi masih 'belum' dimulai
        if ($periode->status_kurasi !== 'belum') {
            return redirect()->back()->with('error', 'Periode tidak dapat diedit karena sudah berstatus "' . ucfirst($periode->status_kurasi) . '".');
        }

        $periode->update([
            'nama_periode' => $request->nama_periode,
            'tanggal_kurasi' => $request->tanggal_kurasi,
            'bulan' => $tanggal->month,
            'tahun' => $tanggal->year,
            'penanggung_jawab' => $request->penanggung_jawab,
            'id_kurator' => $request->id_kurator,
            'status_kurasi' => $request->status_kurasi,
            'catatan_umum' => $request->catatan_umum,
        ]);

        return redirect()->route('admin.kurasi.index')->with('success', 'Periode Kurasi berhasil diperbarui.');
    }

    // Menghapus data periode kurasi berdasarkan ID
    public function destroy($id)
    {
        $periode = PeriodeKurasi::findOrFail($id);
        
        // Memastikan hanya periode dengan status 'belum' yang boleh dihapus
        if ($periode->status_kurasi !== 'belum') {
            return redirect()->back()->with('error', 'Hanya periode dengan status "Belum" yang dapat dihapus.');
        }

        $periode->delete();

        return redirect()->route('admin.kurasi.index')->with('success', 'Periode Kurasi berhasil dihapus.');
    }

    // Mengelola produk alternatif yang diikutsertakan dalam periode kurasi tertentu
    public function manageProduk($id)
    {
        $periode = PeriodeKurasi::findOrFail($id);
        $alternatifs = Alternatif::all();
        $selectedAlternatifIds = PeriodeAlternatif::where('id_periode_kurasi', $id)
                                    ->pluck('id_alternatif')
                                    ->toArray();

        return view('admin.kurasi.produk', compact('periode', 'alternatifs', 'selectedAlternatifIds'));
    }

    // Menyimpan produk alternatif terpilih untuk masuk ke periode kurasi (menggunakan transaksi database)
    public function storeProduk(Request $request, $id)
    {
        $periode = PeriodeKurasi::findOrFail($id);
        
        // Memastikan hanya periode dengan status 'belum' yang produknya boleh diubah
        if ($periode->status_kurasi !== 'belum') {
            return redirect()->back()->with('error', 'Produk tidak dapat diubah karena periode sudah berstatus "' . ucfirst($periode->status_kurasi) . '".');
        }

        $request->validate([
            'alternatif_ids' => 'nullable|array',
            'alternatif_ids.*' => 'exists:alternatif,id_alternatif',
        ]);

        $selectedIds = $request->alternatif_ids ?? [];

        // Melakukan sinkronisasi produk terpilih dalam transaksi database
        DB::transaction(function () use ($periode, $selectedIds) {
            // Menghapus produk alternatif yang sudah tidak terpilih lagi
            PeriodeAlternatif::where('id_periode_kurasi', $periode->id_periode_kurasi)
                ->whereNotIn('id_alternatif', $selectedIds)
                ->delete();

            $existingIds = PeriodeAlternatif::where('id_periode_kurasi', $periode->id_periode_kurasi)
                ->pluck('id_alternatif')
                ->toArray();

            $newIds = array_diff($selectedIds, $existingIds);

            $dataToInsert = [];
            foreach ($newIds as $alternatifId) {
                // Memeriksa status kelayakan legalitas dari produk bersangkutan
                $legalitas = \App\Models\AlternatifLegalitas::where('id_alternatif', $alternatifId)->first();
                
                $dataToInsert[] = [
                    'id_periode_kurasi' => $periode->id_periode_kurasi,
                    'id_alternatif' => $alternatifId,
                    'status_lolos_legalitas' => $legalitas ? $legalitas->lolos_filter : false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($dataToInsert)) {
                PeriodeAlternatif::insert($dataToInsert);
            }
        });

        return redirect()->route('admin.kurasi.produk', $periode->id_periode_kurasi)
                         ->with('success', 'Daftar produk untuk kurasi berhasil diperbarui.');
    }
}
