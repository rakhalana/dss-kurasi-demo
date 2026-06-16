<?php

namespace App\Services;

use App\Models\Alternatif;
use App\Models\AlternatifLegalitas;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;

/**
 * Class ProdukExcelService
 * Menangani logika export template Excel dan import data Produk & Legalitas.
 */
class ProdukExcelService
{
    /**
     * Mempersiapkan spreadsheet untuk diunduh sebagai template import.
     */
    public function generateTemplate()
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

        return $spreadsheet;
    }

    /**
     * Menyimpan spreadsheet ke format Excel (Xlsx) dan mengembalikan file resource/path/response.
     * Dalam kasus ini kita akan menggunakan writer.
     */
    public function getDownloadStream(Spreadsheet $spreadsheet)
    {
        $writer = new Xlsx($spreadsheet);
        return function() use ($writer) {
            $writer->save('php://output');
        };
    }

    /**
     * Memproses file Excel dan mengimpor datanya.
     */
    public function importData($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);

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
                            Storage::disk('supabase')->delete($produk->foto_produk);
                        }
                        
                        Storage::disk('supabase')->put($path, $imageContents);
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
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
