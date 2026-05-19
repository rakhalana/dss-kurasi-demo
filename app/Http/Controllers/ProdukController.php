<?php

namespace App\Http\Controllers;

use App\Models\Alternatif;
use App\Models\AlternatifLegalitas;
use App\Models\PeriodeAlternatif;
use App\Models\PeriodeKurasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;

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
                $path = $file->storeAs('produk', $filename, 'public');
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
                Storage::disk('public')->delete($produk->foto_produk);
            }

            $file = $request->file('foto_produk');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('produk', $filename, 'public');
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
            Storage::disk('public')->delete($produk->foto_produk);
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

    // Mengunduh berkas template Excel untuk proses import data produk dan legalitas secara massal
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        
        // Mempersiapkan Sheet 1: Detail Produk
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Detail Produk');
        $sheet1->setCellValue('A1', 'Nama Produk');
        $sheet1->setCellValue('B1', 'Nama Brand UMKM');
        $sheet1->setCellValue('C1', 'Nama Pemilik');
        $sheet1->setCellValue('D1', 'Deskripsi Produk');
        $sheet1->setCellValue('E1', 'Foto Produk');
        
        $sheet1->getStyle('A1:E1')->getFont()->setBold(true);
        foreach(range('A','D') as $columnID) {
            $sheet1->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet1->getColumnDimension('E')->setWidth(20);

        // Memformat Sheet 1 sebagai tabel bergaya Medium9 agar terlihat rapi dan premium
        $table1 = new Table('A1:E101', 'TableDetailProduk');
        $tableStyle1 = new TableStyle();
        $tableStyle1->setTheme(TableStyle::TABLE_STYLE_MEDIUM9);
        $tableStyle1->setShowRowStripes(true);
        $table1->setStyle($tableStyle1);
        $sheet1->addTable($table1);

        // Mempersiapkan Sheet 2: Legalitas
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Legalitas');
        $sheet2->setCellValue('A1', 'Nama Produk');
        $sheet2->setCellValue('B1', 'Nama Brand UMKM');
        $sheet2->setCellValue('C1', 'NIB (Ya/Tidak)');
        $sheet2->setCellValue('D1', 'No NIB');
        $sheet2->setCellValue('E1', 'BPOM (Ya/Tidak)');
        $sheet2->setCellValue('F1', 'No BPOM');
        $sheet2->setCellValue('G1', 'SP-PIRT (Ya/Tidak)');
        $sheet2->setCellValue('H1', 'No SP-PIRT (15 digit)');
        $sheet2->setCellValue('I1', 'Halal (Ya/Tidak)');
        $sheet2->setCellValue('J1', 'No Halal (17 digit)');
        $sheet2->setCellValue('K1', 'Keterangan');

        // Menuliskan formula untuk menyalin nama produk dan brand dari Sheet 1 secara otomatis
        for ($i = 2; $i <= 101; $i++) {
            $sheet2->setCellValue("A$i", "='Detail Produk'!A$i");
            $sheet2->setCellValue("B$i", "='Detail Produk'!B$i");
        }

        $sheet2->getStyle('A1:K1')->getFont()->setBold(true);
        foreach(range('A','K') as $columnID) {
            $sheet2->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Memformat Sheet 2 sebagai tabel bergaya Medium9
        $table2 = new Table('A1:K101', 'TableLegalitas');
        $tableStyle2 = new TableStyle();
        $tableStyle2->setTheme(TableStyle::TABLE_STYLE_MEDIUM9);
        $tableStyle2->setShowRowStripes(true);
        $table2->setStyle($tableStyle2);
        $sheet2->addTable($table2);

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_produk.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Mengimpor data produk beserta legalitasnya dari berkas Excel yang diunggah
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file_excel');
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
        
        DB::beginTransaction();
        try {
            // Memproses Sheet 1: Detail Produk
            $sheet1 = $spreadsheet->getSheetByName('Detail Produk');
            if (!$sheet1) {
                throw new \Exception('Sheet "Detail Produk" tidak ditemukan.');
            }

            // Mengambil semua objek gambar/foto produk dari Sheet 1 (Memetakan cell koordinat ke baris)
            $drawings = $sheet1->getDrawingCollection();
            $rowImages = [];
            foreach ($drawings as $drawing) {
                $coordinate = $drawing->getCoordinates();
                if (preg_match('/^E(\d+)$/', $coordinate, $matches)) {
                    $rowImages[$matches[1]] = $drawing;
                }
            }

            $rows1 = $sheet1->toArray();
            $header1 = array_shift($rows1);

            $excelRowIndex = 2;
            foreach ($rows1 as $row) {
                if (empty($row[0]) || empty($row[1])) {
                    $excelRowIndex++;
                    continue;
                }

                $nama_produk = trim($row[0]);
                $brand = trim($row[1]);
                $pemilik = $row[2] ?? '';
                $deskripsi = $row[3] ?? null;

                $produk = Alternatif::where('nama_produk', $nama_produk)
                    ->where('nama_brand_umkm', $brand)
                    ->first();

                $updateData = [
                    'nama_pemilik' => $pemilik,
                    'deskripsi_produk' => $deskripsi,
                ];

                // Memproses ekstraksi file gambar produk dari cell Excel jika ada
                if (isset($rowImages[$excelRowIndex])) {
                    $drawing = $rowImages[$excelRowIndex];
                    $imageContents = '';
                    $extension = '';

                    // Ekstraksi gambar tipe MemoryDrawing
                    if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                        ob_start();
                        call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                        $imageContents = ob_get_contents();
                        ob_end_clean();
                        $extension = 'png';
                    } else {
                        // Ekstraksi gambar tipe file biasa di dalam struktur zip xlsx
                        $zipReader = fopen($drawing->getPath(), 'r');
                        while (!feof($zipReader)) {
                            $imageContents .= fread($zipReader, 1024);
                        }
                        fclose($zipReader);
                        $extension = $drawing->getExtension();
                    }

                    if ($imageContents) {
                        $filename = 'import_' . time() . '_' . uniqid() . '.' . $extension;
                        $path = 'produk/' . $filename;
                        
                        if ($produk && $produk->foto_produk) {
                            Storage::disk('public')->delete($produk->foto_produk);
                        }
                        
                        Storage::disk('public')->put($path, $imageContents);
                        $updateData['foto_produk'] = $path;
                    }
                }

                if (!$produk) {
                    $updateData['nama_produk'] = $nama_produk;
                    $updateData['nama_brand_umkm'] = $brand;
                    $updateData['is_aktif'] = false;
                    $produk = Alternatif::create($updateData);
                } else {
                    $produk->update($updateData);
                }

                if (!$produk->legalitas) {
                    AlternatifLegalitas::create([
                        'id_alternatif' => $produk->id_alternatif,
                    ]);
                }
                
                $excelRowIndex++;
            }

            // Memproses Sheet 2: Legalitas
            $sheet2 = $spreadsheet->getSheetByName('Legalitas');
            if ($sheet1) {
                if ($sheet2) {
                    $rows2 = $sheet2->toArray();
                    $header2 = array_shift($rows2);

                    foreach ($rows2 as $row) {
                        if (empty($row[0]) || empty($row[1])) continue;

                        // Memeriksa apakah terdapat data legalitas yang diisi di kolom C sampai K
                        $hasLegalitasData = false;
                        for ($i = 2; $i <= 10; $i++) {
                            if (isset($row[$i]) && trim($row[$i]) !== '') {
                                $hasLegalitasData = true;
                                break;
                            }
                        }

                        if (!$hasLegalitasData) continue;

                        $nama_produk = trim($row[0]);
                        $brand = trim($row[1]);

                        $produk = Alternatif::where('nama_produk', $nama_produk)
                            ->where('nama_brand_umkm', $brand)
                            ->first();

                        if ($produk) {
                            $legalitas = $produk->legalitas;
                            
                            $is_nib = (strtolower(trim($row[2] ?? '')) === 'ya' || trim($row[2] ?? '') == '1');
                            $no_nib = trim($row[3] ?? '') ?: null;
                            
                            $is_bpom = (strtolower(trim($row[4] ?? '')) === 'ya' || trim($row[4] ?? '') == '1');
                            $no_bpom_raw = trim($row[5] ?? '');
                            
                            $is_pirt = (strtolower(trim($row[6] ?? '')) === 'ya' || trim($row[6] ?? '') == '1');
                            $no_pirt_raw = trim($row[7] ?? '');
                            
                            $is_halal = (strtolower(trim($row[8] ?? '')) === 'ya' || trim($row[8] ?? '') == '1');
                            $no_halal_raw = trim($row[9] ?? '');
                            
                            $keterangan = trim($row[10] ?? '') ?: null;

                            $updateData = [
                                'is_nib' => $is_nib,
                                'no_nib' => $no_nib,
                                'is_bpom' => $is_bpom,
                                'is_sp_pirt' => $is_pirt,
                                'is_sertifikat_halal' => $is_halal,
                                'keterangan' => $keterangan,
                            ];

                            // Formatting BPOM
                            if ($is_bpom && $no_bpom_raw) {
                                $updateData['no_bpom'] = $no_bpom_raw;
                            } else {
                                $updateData['no_bpom'] = null;
                            }

                            // Formatting Halal (menambahkan prefiks ID)
                            if ($is_halal && $no_halal_raw) {
                                $updateData['no_sertifikat_halal'] = 'ID' . $no_halal_raw;
                            } else {
                                $updateData['no_sertifikat_halal'] = $no_halal_raw ?: null;
                            }

                            // Formatting SP-PIRT (memisahkan digit ke format XXXXXXXXXXXXX-XX jika panjang input tepat 15 digit)
                            if ($is_pirt && strlen($no_pirt_raw) === 15) {
                                $updateData['no_sp_pirt'] = substr($no_pirt_raw, 0, 13) . '-' . substr($no_pirt_raw, 13, 2);
                            } elseif ($is_pirt && $no_pirt_raw) {
                                $updateData['no_sp_pirt'] = $no_pirt_raw;
                            } else {
                                $updateData['no_sp_pirt'] = null;
                            }

                            // Menghitung status kelolosan legalitas otomatis
                            $lolos = $is_nib && $is_halal && ($is_bpom || $is_pirt);
                            $updateData['lolos_filter'] = $lolos;

                            $legalitas->update($updateData);
                            $produk->update(['is_aktif' => true]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data produk berhasil diimport.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}
