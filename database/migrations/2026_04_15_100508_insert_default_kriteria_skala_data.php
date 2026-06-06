<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Migrasi untuk menyisipkan data default awal (seeding) sub-kriteria/skala kriteria
return new class extends Migration {
    public function up(): void
    {
        // Menyusun daftar opsi skala nilai default (skala 1 sampai 5) untuk C1 sampai C9
        $skala = [
            // Kriteria 1: Rasa
            ['id_kriteria' => 1, 'nilai_skala' => 1, 'deskripsi_skala' => 'Rasa/aroma menyimpang seperti tengik, asam tidak wajar, pahit, atau tidak sesuai karakter produk.', 'is_aktif' => true],
            ['id_kriteria' => 1, 'nilai_skala' => 2, 'deskripsi_skala' => 'Karakter rasa belum jelas, terdapat aftertaste/aroma mengganggu, atau tekstur kurang sesuai.', 'is_aktif' => true],
            ['id_kriteria' => 1, 'nilai_skala' => 3, 'deskripsi_skala' => 'Rasa utama sesuai jenis produk, namun kurang seimbang, kurang konsisten, atau daya tarik masih lemah.', 'is_aktif' => true],
            ['id_kriteria' => 1, 'nilai_skala' => 4, 'deskripsi_skala' => 'Rasa sesuai karakter produk, seimbang, aroma/tekstur mendukung, dan diterima konsumen.', 'is_aktif' => true],
            ['id_kriteria' => 1, 'nilai_skala' => 5, 'deskripsi_skala' => 'Rasa khas, seimbang, konsisten antar sampel, dan memiliki daya saing tinggi di pasar modern.', 'is_aktif' => true],

            // Kriteria 2: Harga
            ['id_kriteria' => 2, 'nilai_skala' => 1, 'deskripsi_skala' => 'Struktur harga sangat tinggi dan sulit diterima oleh retail tujuan.', 'is_aktif' => true],
            ['id_kriteria' => 2, 'nilai_skala' => 2, 'deskripsi_skala' => 'Harga melebihi batas retail tujuan dan memerlukan penyesuaian signifikan.', 'is_aktif' => true],
            ['id_kriteria' => 2, 'nilai_skala' => 3, 'deskripsi_skala' => 'Harga mendekati batas retail tujuan, tetapi masih perlu disesuaikan agar kompetitif.', 'is_aktif' => true],
            ['id_kriteria' => 2, 'nilai_skala' => 4, 'deskripsi_skala' => 'Harga sesuai dengan kisaran retail tujuan dan cukup layak untuk pasar modern.', 'is_aktif' => true],
            ['id_kriteria' => 2, 'nilai_skala' => 5, 'deskripsi_skala' => 'Harga sangat kompetitif dan sesuai dengan target retail tujuan.', 'is_aktif' => true],

            // Kriteria 3: Kapasitas produksi
            ['id_kriteria' => 3, 'nilai_skala' => 1, 'deskripsi_skala' => 'Kapasitas produksi kurang dari 40 unit per bulan.', 'is_aktif' => true],
            ['id_kriteria' => 3, 'nilai_skala' => 2, 'deskripsi_skala' => 'Kapasitas produksi 41-100 unit per bulan.', 'is_aktif' => true],
            ['id_kriteria' => 3, 'nilai_skala' => 3, 'deskripsi_skala' => 'Kapasitas produksi 101-240 unit per bulan.', 'is_aktif' => true],
            ['id_kriteria' => 3, 'nilai_skala' => 4, 'deskripsi_skala' => 'Kapasitas produksi 241-500 unit per bulan.', 'is_aktif' => true],
            ['id_kriteria' => 3, 'nilai_skala' => 5, 'deskripsi_skala' => 'Kapasitas produksi lebih dari 500 unit per bulan.', 'is_aktif' => true],

            // Kriteria 4: Masa kadaluwarsa
            ['id_kriteria' => 4, 'nilai_skala' => 1, 'deskripsi_skala' => 'Masa kadaluwarsa kurang dari 1 bulan.', 'is_aktif' => true],
            ['id_kriteria' => 4, 'nilai_skala' => 2, 'deskripsi_skala' => 'Masa kadaluwarsa 1-3 bulan.', 'is_aktif' => true],
            ['id_kriteria' => 4, 'nilai_skala' => 3, 'deskripsi_skala' => 'Masa kadaluwarsa 3-6 bulan.', 'is_aktif' => true],
            ['id_kriteria' => 4, 'nilai_skala' => 4, 'deskripsi_skala' => 'Masa kadaluwarsa 6-12 bulan.', 'is_aktif' => true],
            ['id_kriteria' => 4, 'nilai_skala' => 5, 'deskripsi_skala' => 'Masa kadaluwarsa lebih dari 12 bulan.', 'is_aktif' => true],

            // Kriteria 5: Kode produksi
            ['id_kriteria' => 5, 'nilai_skala' => 1, 'deskripsi_skala' => 'Tidak mencantumkan kode produksi sama sekali.', 'is_aktif' => true],
            ['id_kriteria' => 5, 'nilai_skala' => 2, 'deskripsi_skala' => 'Kode produksi tercantum, namun tidak jelas, sulit terbaca, atau tidak permanen pada kemasan.', 'is_aktif' => true],
            ['id_kriteria' => 5, 'nilai_skala' => 3, 'deskripsi_skala' => 'Kode produksi tercantum, namun penempatan, format, atau konsistensinya masih perlu diperbaiki.', 'is_aktif' => true],
            ['id_kriteria' => 5, 'nilai_skala' => 4, 'deskripsi_skala' => 'Kode produksi tercantum dengan jelas, mudah dibaca, dan dapat digunakan untuk identifikasi batch produksi.', 'is_aktif' => true],
            ['id_kriteria' => 5, 'nilai_skala' => 5, 'deskripsi_skala' => 'Kode produksi jelas, mudah dilihat, konsisten, dan memenuhi ketentuan label pangan olahan.', 'is_aktif' => true],

            // Kriteria 6: Uji nutrisi
            ['id_kriteria' => 6, 'nilai_skala' => 1, 'deskripsi_skala' => 'Tidak mencantumkan tabel Informasi Nilai Gizi (ING) pada kemasan.', 'is_aktif' => true],
            ['id_kriteria' => 6, 'nilai_skala' => 2, 'deskripsi_skala' => 'Tabel ING sangat terbatas atau format belum sesuai regulasi standar pangan.', 'is_aktif' => true],
            ['id_kriteria' => 6, 'nilai_skala' => 3, 'deskripsi_skala' => 'Tabel ING tersedia, namun masih terdapat beberapa kesalahan penulisan satuan atau format.', 'is_aktif' => true],
            ['id_kriteria' => 6, 'nilai_skala' => 4, 'deskripsi_skala' => 'Tabel ING lengkap dan benar sesuai standar (mencakup takaran saji, zat gizi wajib, AKG, dan format tabel sesuai).', 'is_aktif' => true],
            ['id_kriteria' => 6, 'nilai_skala' => 5, 'deskripsi_skala' => 'Tabel ING lengkap dan benar sesuai standar, serta didukung hasil uji laboratorium terakreditasi dengan dokumen tersedia.', 'is_aktif' => true],

            // Kriteria 7: Material
            ['id_kriteria' => 7, 'nilai_skala' => 1, 'deskripsi_skala' => 'Tidak memiliki tanda food grade/tara pangan dan material kemasan tidak layak untuk pangan.', 'is_aktif' => true],
            ['id_kriteria' => 7, 'nilai_skala' => 2, 'deskripsi_skala' => 'Kemasan tampak bersih tetapi peruntukan pangan dan tanda food grade belum jelas.', 'is_aktif' => true],
            ['id_kriteria' => 7, 'nilai_skala' => 3, 'deskripsi_skala' => 'Tidak ada tanda food grade, namun jenis kemasan sudah cukup sesuai dengan karakter produk.', 'is_aktif' => true],
            ['id_kriteria' => 7, 'nilai_skala' => 4, 'deskripsi_skala' => 'Kemasan berlabel food grade/tara pangan, memiliki kode material, dan jenisnya sesuai karakter produk.', 'is_aktif' => true],
            ['id_kriteria' => 7, 'nilai_skala' => 5, 'deskripsi_skala' => 'Kemasan berlabel food grade/tara pangan, memiliki kode material yang sesuai karakter produk, serta dilengkapi dokumen spesifikasi kemasan aman dari pemasok.', 'is_aktif' => true],

            // Kriteria 8: Desain
            ['id_kriteria' => 8, 'nilai_skala' => 1, 'deskripsi_skala' => 'Tampilan kemasan tidak rapi, identitas produk tidak jelas, atau visual mengganggu keterbacaan.', 'is_aktif' => true],
            ['id_kriteria' => 8, 'nilai_skala' => 2, 'deskripsi_skala' => 'Layout kemasan, warna/font, identitas merek, dan daya jual retail masih lemah.', 'is_aktif' => true],
            ['id_kriteria' => 8, 'nilai_skala' => 3, 'deskripsi_skala' => 'Identitas merek terlihat, namun komposisi visual, tipografi, warna, atau daya tarik rak perlu diperbaiki.', 'is_aktif' => true],
            ['id_kriteria' => 8, 'nilai_skala' => 4, 'deskripsi_skala' => 'Desain kemasan rapi, menarik, sesuai karakter produk/target pasar, dan informasi utama mudah dikenali.', 'is_aktif' => true],
            ['id_kriteria' => 8, 'nilai_skala' => 5, 'deskripsi_skala' => 'Desain kemasan profesional, identitas merek kuat, visual bersih, hierarki informasi baik, dan siap bersaing di pasar modern.', 'is_aktif' => true],

            // Kriteria 9: Informasi label
            ['id_kriteria' => 9, 'nilai_skala' => 1, 'deskripsi_skala' => 'Hanya mencantumkan nama produk saja, atau hanya nama produk dan berat bersih.', 'is_aktif' => true],
            ['id_kriteria' => 9, 'nilai_skala' => 2, 'deskripsi_skala' => 'Kurang dari 5 elemen informasi label wajib terpenuhi dengan benar.', 'is_aktif' => true],
            ['id_kriteria' => 9, 'nilai_skala' => 3, 'deskripsi_skala' => 'Memenuhi 5-8 elemen informasi label wajib, atau masih ada beberapa elemen yang tidak konsisten.', 'is_aktif' => true],
            ['id_kriteria' => 9, 'nilai_skala' => 4, 'deskripsi_skala' => 'Memenuhi 9 elemen label wajib (nama produk, komposisi, netto, produsen, halal, kode/tanggal produksi, kadaluwarsa, izin edar, dan info bahan tertentu).', 'is_aktif' => true],
            ['id_kriteria' => 9, 'nilai_skala' => 5, 'deskripsi_skala' => 'Memenuhi seluruh 9 elemen label wajib secara lengkap, serta fisik label tahan lama (tidak mudah lepas/luntur) dan mudah dibaca.', 'is_aktif' => true],
        ];

        // Menyisipkan data skala ke dalam tabel
        DB::table('kriteria_skala')->insert($skala);
    }

    public function down(): void
    {
        // Mengosongkan isi tabel kriteria_skala dengan mengabaikan foreign key constraints sementara
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        DB::table('kriteria_skala')->truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }
};
