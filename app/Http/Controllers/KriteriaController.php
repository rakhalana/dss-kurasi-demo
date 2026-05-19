<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use App\Models\KriteriaSkala;
use Illuminate\Http\Request;

class KriteriaController extends Controller
{
    // Menampilkan halaman daftar kriteria beserta skala penilaiannya
    public function index()
    {
        $kriteria = Kriteria::with('scales')->orderBy('urutan_tampil')->get();
        return view('admin.kriteria', compact('kriteria'));
    }

    // Memperbarui data kriteria tertentu berdasarkan ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kriteria' => 'required|string|max:100',
            'aspek' => 'required|in:kualitas_produk,kemasan',
            'deskripsi_kriteria' => 'nullable|string',
            'target_nilai' => 'required|integer|between:1,5',
        ]);

        $kriteria = Kriteria::findOrFail($id);
        $kriteria->update($request->all());

        return redirect()->back()->with('success', 'Kriteria berhasil diperbarui.');
    }

    // Mengubah status aktif/nonaktif skala penilaian kriteria secara asinkron (AJAX)
    public function toggleSkala(Request $request)
    {
        $id_kriteria = $request->id_kriteria;
        $nilai_skala = $request->nilai_skala;

        $skala = KriteriaSkala::where('id_kriteria', $id_kriteria)
            ->where('nilai_skala', $nilai_skala)
            ->firstOrFail();

        $skala->is_aktif = !$skala->is_aktif;
        $skala->save();

        return response()->json([
            'success' => true,
            'is_aktif' => $skala->is_aktif,
            'message' => 'Status skala berhasil diubah.'
        ]);
    }

    // Memperbarui deskripsi dan status aktif skala penilaian kriteria
    public function updateSkala(Request $request)
    {
        $request->validate([
            'id_kriteria'     => 'required|integer',
            'nilai_skala'     => 'required|integer',
            'deskripsi_skala' => 'required|string|max:255',
            'is_aktif'        => 'required|in:0,1',
        ]);

        KriteriaSkala::where('id_kriteria', $request->id_kriteria)
            ->where('nilai_skala', $request->nilai_skala)
            ->update([
                'deskripsi_skala' => $request->deskripsi_skala,
                'is_aktif'        => (bool) $request->is_aktif,
            ]);

        return redirect()->back()->with('success', 'Skala berhasil diperbarui.');
    }
}
