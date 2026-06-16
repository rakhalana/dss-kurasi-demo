<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('ahp_sesi')->insert([
            [
                'id_ahp_sesi' => 1,
                'nama_sesi' => 'Penilaian Bobot 2026-05-25 01:36',
                'tanggal_sesi' => '2026-05-25',
                'lambda_max' => '9.3205',
                'ci' => '0.0401',
                'cr' => '0.0276',
                'status_aktif' => 1,
                'dibuat_oleh' => 1,
                'created_at' => '2026-05-25 01:35:51',
                'updated_at' => '2026-05-24 18:36:07',
            ],
        ]);

        DB::table('ahp_bobot')->insert([
            [
                'id_ahp_bobot' => 1,
                'id_ahp_sesi' => 1,
                'id_kriteria' => 1,
                'bobot_prioritas' => '0.078997',
            ],
            [
                'id_ahp_bobot' => 2,
                'id_ahp_sesi' => 1,
                'id_kriteria' => 2,
                'bobot_prioritas' => '0.036435',
            ],
            [
                'id_ahp_bobot' => 3,
                'id_ahp_sesi' => 1,
                'id_kriteria' => 3,
                'bobot_prioritas' => '0.078997',
            ],
            [
                'id_ahp_bobot' => 4,
                'id_ahp_sesi' => 1,
                'id_kriteria' => 4,
                'bobot_prioritas' => '0.217325',
            ],
            [
                'id_ahp_bobot' => 5,
                'id_ahp_sesi' => 1,
                'id_kriteria' => 5,
                'bobot_prioritas' => '0.060812',
            ],
            [
                'id_ahp_bobot' => 6,
                'id_ahp_sesi' => 1,
                'id_kriteria' => 6,
                'bobot_prioritas' => '0.185001',
            ],
            [
                'id_ahp_bobot' => 7,
                'id_ahp_sesi' => 1,
                'id_kriteria' => 7,
                'bobot_prioritas' => '0.104835',
            ],
            [
                'id_ahp_bobot' => 8,
                'id_ahp_sesi' => 1,
                'id_kriteria' => 8,
                'bobot_prioritas' => '0.032395',
            ],
            [
                'id_ahp_bobot' => 9,
                'id_ahp_sesi' => 1,
                'id_kriteria' => 9,
                'bobot_prioritas' => '0.205203',
            ],
        ]);

        DB::table('ahp_perbandingan')->insert([
            [
                'id_ahp_perbandingan' => 1,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 1,
                'kriteria_2_id' => 2,
                'nilai_perbandingan' => '3.0000',
            ],
            [
                'id_ahp_perbandingan' => 2,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 1,
                'kriteria_2_id' => 3,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 3,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 1,
                'kriteria_2_id' => 4,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 4,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 1,
                'kriteria_2_id' => 5,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 5,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 1,
                'kriteria_2_id' => 6,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 6,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 1,
                'kriteria_2_id' => 7,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 7,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 1,
                'kriteria_2_id' => 8,
                'nilai_perbandingan' => '3.0000',
            ],
            [
                'id_ahp_perbandingan' => 8,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 1,
                'kriteria_2_id' => 9,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 9,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 2,
                'kriteria_2_id' => 3,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 10,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 2,
                'kriteria_2_id' => 4,
                'nilai_perbandingan' => '0.2000',
            ],
            [
                'id_ahp_perbandingan' => 11,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 2,
                'kriteria_2_id' => 5,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 12,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 2,
                'kriteria_2_id' => 6,
                'nilai_perbandingan' => '0.2000',
            ],
            [
                'id_ahp_perbandingan' => 13,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 2,
                'kriteria_2_id' => 7,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 14,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 2,
                'kriteria_2_id' => 8,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 15,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 2,
                'kriteria_2_id' => 9,
                'nilai_perbandingan' => '0.2000',
            ],
            [
                'id_ahp_perbandingan' => 16,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 3,
                'kriteria_2_id' => 4,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 17,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 3,
                'kriteria_2_id' => 5,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 18,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 3,
                'kriteria_2_id' => 6,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 19,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 3,
                'kriteria_2_id' => 7,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 20,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 3,
                'kriteria_2_id' => 8,
                'nilai_perbandingan' => '3.0000',
            ],
            [
                'id_ahp_perbandingan' => 21,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 3,
                'kriteria_2_id' => 9,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 22,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 4,
                'kriteria_2_id' => 5,
                'nilai_perbandingan' => '5.0000',
            ],
            [
                'id_ahp_perbandingan' => 23,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 4,
                'kriteria_2_id' => 6,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 24,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 4,
                'kriteria_2_id' => 7,
                'nilai_perbandingan' => '3.0000',
            ],
            [
                'id_ahp_perbandingan' => 25,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 4,
                'kriteria_2_id' => 8,
                'nilai_perbandingan' => '5.0000',
            ],
            [
                'id_ahp_perbandingan' => 26,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 4,
                'kriteria_2_id' => 9,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 27,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 5,
                'kriteria_2_id' => 6,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 28,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 5,
                'kriteria_2_id' => 7,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 29,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 5,
                'kriteria_2_id' => 8,
                'nilai_perbandingan' => '3.0000',
            ],
            [
                'id_ahp_perbandingan' => 30,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 5,
                'kriteria_2_id' => 9,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 31,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 6,
                'kriteria_2_id' => 7,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 32,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 6,
                'kriteria_2_id' => 8,
                'nilai_perbandingan' => '5.0000',
            ],
            [
                'id_ahp_perbandingan' => 33,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 6,
                'kriteria_2_id' => 9,
                'nilai_perbandingan' => '1.0000',
            ],
            [
                'id_ahp_perbandingan' => 34,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 7,
                'kriteria_2_id' => 8,
                'nilai_perbandingan' => '3.0000',
            ],
            [
                'id_ahp_perbandingan' => 35,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 7,
                'kriteria_2_id' => 9,
                'nilai_perbandingan' => '0.3333',
            ],
            [
                'id_ahp_perbandingan' => 36,
                'id_ahp_sesi' => 1,
                'kriteria_1_id' => 8,
                'kriteria_2_id' => 9,
                'nilai_perbandingan' => '0.2000',
            ],
        ]);

    }

    public function down()
    {
        // Hapus data jika migrasi dibatalkan
        DB::table('ahp_perbandingan')->truncate();
        DB::table('ahp_bobot')->truncate();
        DB::table('ahp_sesi')->truncate();
    }
};
