<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Migrasi untuk menyisipkan data default awal (seeding) kriteria penilaian
return new class extends Migration {
    public function up(): void
    {
        // Data default kriteria awal (C1 sampai C9)
        $kriteria = [
            [
                'id_kriteria' => 1,
                'kode_kriteria' => 'C1',
                'nama_kriteria' => 'Rasa',
                'aspek' => 'kualitas_produk',
                'deskripsi_kriteria' => 'Penilaian terhadap cita rasa produk, yang menjadi salah satu faktor utama penentu kualitas.',
                'target_nilai' => 4,
                'urutan_tampil' => 1,
            ],
            [
                'id_kriteria' => 2,
                'kode_kriteria' => 'C2',
                'nama_kriteria' => 'Harga',
                'aspek' => 'kualitas_produk',
                'deskripsi_kriteria' => 'Kesesuaian harga produk dengan nilai yang ditawarkan dan daya beli target pasar.',
                'target_nilai' => 5,
                'urutan_tampil' => 2,
            ],
            [
                'id_kriteria' => 3,
                'kode_kriteria' => 'C3',
                'nama_kriteria' => 'Kapasitas produksi',
                'aspek' => 'kualitas_produk',
                'deskripsi_kriteria' => 'Kemampuan UMKM dalam memproduksi barang secara kontinu dalam jumlah yang memadai.',
                'target_nilai' => 4,
                'urutan_tampil' => 3,
            ],
            [
                'id_kriteria' => 4,
                'kode_kriteria' => 'C4',
                'nama_kriteria' => 'Masa kadaluwarsa',
                'aspek' => 'kualitas_produk',
                'deskripsi_kriteria' => 'Ketahanan produk atau masa berlaku produk yang aman untuk dikonsumsi.',
                'target_nilai' => 4,
                'urutan_tampil' => 4,
            ],
            [
                'id_kriteria' => 5,
                'kode_kriteria' => 'C5',
                'nama_kriteria' => 'Kode produksi',
                'aspek' => 'kualitas_produk',
                'deskripsi_kriteria' => 'Ketersediaan dan kejelasan kode produksi sebagai bentuk pelacakan (traceability) dan jaminan mutu.',
                'target_nilai' => 5,
                'urutan_tampil' => 5,
            ],
            [
                'id_kriteria' => 6,
                'kode_kriteria' => 'C6',
                'nama_kriteria' => 'Uji nutrisi',
                'aspek' => 'kualitas_produk',
                'deskripsi_kriteria' => 'Hasil dari pengujian nutrisi yang menampilkan kandungan gizi dan jaminan standar kualitas dari produk.',
                'target_nilai' => 5,
                'urutan_tampil' => 6,
            ],
            [
                'id_kriteria' => 7,
                'kode_kriteria' => 'C7',
                'nama_kriteria' => 'Material',
                'aspek' => 'kemasan',
                'deskripsi_kriteria' => 'Bahan yang digunakan untuk mengemas produk, meliputi aspek keamanan pangan dan kepraktisan.',
                'target_nilai' => 5,
                'urutan_tampil' => 7,
            ],
            [
                'id_kriteria' => 8,
                'kode_kriteria' => 'C8',
                'nama_kriteria' => 'Desain',
                'aspek' => 'kemasan',
                'deskripsi_kriteria' => 'Estetika dan daya tarik visual dari Kemasan yang dapat menarik minat pembeli.',
                'target_nilai' => 4,
                'urutan_tampil' => 8,
            ],
            [
                'id_kriteria' => 9,
                'kode_kriteria' => 'C9',
                'nama_kriteria' => 'Informasi label',
                'aspek' => 'kemasan',
                'deskripsi_kriteria' => 'Kelengkapan informasi pada label Kemasan, seperti nama produk, komposisi, berat bersih, dan legalitas pangan.',
                'target_nilai' => 5,
                'urutan_tampil' => 9,
            ],
        ];

        // Menyisipkan data ke database
        DB::table('kriteria')->insert($kriteria);
    }

    public function down(): void
    {
        // Mengosongkan kembali tabel kriteria dengan mengabaikan integrity constraints sementara
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        DB::table('kriteria')->truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }
};