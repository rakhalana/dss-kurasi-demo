<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migrasi untuk membuat tabel periode kurasi
return new class extends Migration {
    public function up(): void
    {
        // Membuat tabel periode penyelenggaraan kurasi
        Schema::create('periode_kurasi', function (Blueprint $table) {
            $table->increments('id_periode_kurasi');
            $table->string('nama_periode', 100);
            $table->date('tanggal_kurasi');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedBigInteger('id_kurator'); // Kurator utama yang menilai
            $table->string('penanggung_jawab', 100); // Nama penanggung jawab kegiatan kurasi
            $table->enum('status_kurasi', ['belum', 'berlangsung', 'selesai'])->default('belum'); // Progres siklus periode
            $table->unsignedInteger('id_ahp_sesi'); // Sesi pembobotan AHP yang dirujuk
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Aturan integritas kunci asing ke tabel-tabel terkait
            $table->foreign('id_kurator', 'fk_periode_kurasi_kurator')
                ->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('id_ahp_sesi', 'fk_periode_kurasi_ahp_sesi')
                ->references('id_ahp_sesi')->on('ahp_sesi')
                ->onUpdate('cascade')->onDelete('restrict');


        });
    }

    public function down(): void
    {
        // Menghapus tabel periode_kurasi jika migrasi dibatalkan
        Schema::dropIfExists('periode_kurasi');
    }
};
