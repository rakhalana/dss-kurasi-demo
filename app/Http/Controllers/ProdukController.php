<?php

namespace App\Http\Controllers;

use App\Models\Alternatif;
use App\Models\AlternatifLegalitas;
use App\Models\PeriodeAlternatif;
use App\Models\PeriodeKurasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    // Menampilkan halaman daftar produk alternatif beserta data legalitasnya
    public function index()
    {
        $produk = Alternatif::with('legalitas')->get();
        return view('admin.produk', compact('produk'));
    }

    // Menyimpan data produk alternatif baru ke database beserta foto produk
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:150',
            'nama_brand_umkm' => 'required|string|max:150',
            'nama_pemilik' => 'required|string|max:150',
            'deskripsi_produk' => 'nullable|string',
            'foto_produk' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except('foto_produk');
            $data['is_aktif'] = false;
            
            if ($request->hasFile('foto_produk')) {
                $file = $request->file('foto_produk');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('produk', $filename, 'supabase');
                $data['foto_produk'] = $path;
            }

            $produk = Alternatif::create($data);

            AlternatifLegalitas::create([
                'id_alternatif' => $produk->id_alternatif,
                'is_nib' => false,
                'is_bpom' => false,
                'is_sp_pirt' => false,
                'is_sertifikat_halal' => false,
                'lolos_filter' => false,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    // Memperbarui data detail produk alternatif berdasarkan ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pemilik' => 'required|string|max:150',
            'deskripsi_produk' => 'nullable|string',
            'foto_produk' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $produk = Alternatif::findOrFail($id);
        $data = $request->except('foto_produk');

        if ($request->hasFile('foto_produk')) {
            if ($produk->foto_produk) {
                Storage::disk('supabase')->delete($produk->foto_produk);
            }

            $file = $request->file('foto_produk');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('produk', $filename, 'supabase');
            $data['foto_produk'] = $path;
        }

        $produk->update($data);
        return redirect()->back()->with('success', 'Produk berhasil diperbarui.');
    }

    // Menghapus data produk alternatif dari database beserta file fotonya
    public function destroy($id)
    {
        $produk = Alternatif::findOrFail($id);
        
        if ($produk->foto_produk) {
            Storage::disk('supabase')->delete($produk->foto_produk);
        }

        $produk->delete();
        return redirect()->route('admin.produk')->with('success', 'Produk berhasil dihapus.');
    }

    // Memperbarui data dokumen legalitas produk alternatif beserta status kelolosannya
    public function updateLegalitas(Request $request, $id)
    {
        // Menentukan status ketersediaan dokumen secara otomatis berdasarkan keberadaan nomor dokumen
        $request->merge([
            'is_nib' => $request->no_nib ? 1 : $request->is_nib,
            'is_bpom' => $request->no_bpom ? 1 : $request->is_bpom,
            'is_sp_pirt' => ($request->no_sp_pirt_1 || $request->no_sp_pirt_2) ? 1 : $request->is_sp_pirt,
            'is_sertifikat_halal' => $request->no_sertifikat_halal ? 1 : $request->is_sertifikat_halal,
        ]);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'is_nib' => 'required|boolean',
            'no_nib' => 'required_if:is_nib,1|nullable|numeric|digits:13',
            'is_bpom' => 'required|boolean',
            'no_bpom' => 'required_if:is_bpom,1|nullable|string|max:100',
            'is_sp_pirt' => 'required|boolean',
            'no_sp_pirt_1' => 'required_if:is_sp_pirt,1|nullable|numeric|digits:13',
            'no_sp_pirt_2' => 'required_if:is_sp_pirt,1|nullable|numeric|digits:2',
            'is_sertifikat_halal' => 'required|boolean',
            'no_sertifikat_halal' => 'required_if:is_sertifikat_halal,1|nullable|numeric|digits:17',
            'keterangan' => 'nullable|string',
        ], [
            'required_if' => 'Nomor dokumen wajib diisi jika status tersedia.',
            'digits' => 'Nomor :attribute harus berjumlah :digits angka.',
            'numeric' => 'Nomor :attribute harus berupa angka.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error_id', $id);
        }

        $legalitas = \App\Models\AlternatifLegalitas::where('id_alternatif', $id)->firstOrFail();
        $data = $request->except(['no_sp_pirt_1', 'no_sp_pirt_2']);

        // Format penulisan nomor Sertifikat Halal dengan menambahkan prefiks 'ID' jika diisi
        if ($request->is_sertifikat_halal && $request->no_sertifikat_halal) {
            $data['no_sertifikat_halal'] = 'ID' . $request->no_sertifikat_halal;
        }

        // Format penggabungan nomor SP-PIRT dari dua bagian input menjadi format XXXXXXXXXXXXX-XX
        if ($request->is_sp_pirt && $request->no_sp_pirt_1 && $request->no_sp_pirt_2) {
            $data['no_sp_pirt'] = $request->no_sp_pirt_1 . '-' . $request->no_sp_pirt_2;
        }

        if ($request->is_bpom && $request->no_bpom) {
            $data['no_bpom'] = $request->no_bpom;
        }

        // Menghitung status kelolosan legalitas: Wajib memiliki NIB, Sertifikat Halal, dan salah satu dari BPOM / SP-PIRT
        $lolos = $request->is_nib && 
                 $request->is_sertifikat_halal && 
                 ($request->is_bpom || $request->is_sp_pirt);

        $legalitas->update(array_merge($data, ['lolos_filter' => $lolos]));
        $legalitas->alternatif->update(['is_aktif' => true]);

        $this->syncLegalitasToPeriodesBeelum($id, $lolos);

        return redirect()->back()->with('success', 'Legalitas produk berhasil diperbarui.');
    }

    // Menyinkronkan perubahan kelolosan legalitas ke tabel periode_alternatif untuk periode berstatus 'belum' dimulai
    private function syncLegalitasToPeriodesBeelum($idAlternatif, $lolosFilter)
    {
        $periodeIds = PeriodeKurasi::where('status_kurasi', 'belum')->pluck('id_periode_kurasi');

        if ($periodeIds->isNotEmpty()) {
            PeriodeAlternatif::where('id_alternatif', $idAlternatif)
                ->whereIn('id_periode_kurasi', $periodeIds)
                ->update(['status_lolos_legalitas' => $lolosFilter]);
        }
    }

    protected $excelService;

    /**
     * Constructor untuk injeksi ProdukExcelService.
     */
    public function __construct(\App\Services\ProdukExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * Mengunduh berkas template Excel untuk proses import data produk dan legalitas secara massal.
     */
    public function downloadTemplate()
    {
        $spreadsheet = $this->excelService->generateTemplate();
        $filename = 'template_import_produk.xlsx';

        return response()->streamDownload(
            $this->excelService->getDownloadStream($spreadsheet),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /**
     * Mengimpor data produk beserta legalitasnya dari berkas Excel yang diunggah.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file_excel');
        
        try {
            $this->excelService->importData($file->getRealPath());
            return redirect()->back()->with('success', 'Data produk berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}
