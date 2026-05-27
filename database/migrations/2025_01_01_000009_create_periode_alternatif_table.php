<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migrasi untuk membuat tabel relasi periode dan alternatif (jembatan/pivot)
return new class extends Migration {
    public function up(): void
    {
        // Membuat tabel daftar alternatif yang diikutsertakan pada setiap periode
        Schema::create('periode_alternatif', function (Blueprint $table) {
            $table->increments('id_periode_alternatif');
            $table->unsignedInteger('id_periode_kurasi'); // Periode kurasi terkait
            $table->unsignedInteger('id_alternatif'); // Alternatif produk terkait
            $table->boolean('status_lolos_legalitas')->nullable(); // Kelulusan seleksi awal berkas administrasi/legalitas
            $table->unsignedInteger('urutan_input')->nullable(); // Urutan produk saat kurator melakukan penilaian
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['id_periode_kurasi', 'id_alternatif'], 'uk_periode_alternatif');

            // Pengaturan kunci asing untuk integritas data referensial
            $table->foreign('id_periode_kurasi', 'fk_periode_alternatif_periode')
                ->references('id_periode_kurasi')->on('periode_kurasi')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('id_alternatif', 'fk_periode_alternatif_alternatif')
                ->references('id_alternatif')->on('alternatif')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        // Menghapus tabel jika migrasi dibatalkan
        Schema::dropIfExists('periode_alternatif');
    }
};
