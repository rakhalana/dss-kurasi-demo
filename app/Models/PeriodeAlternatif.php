<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model jembatan (pivot dengan data tambahan) antara tabel PeriodeKurasi dan Alternatif (Produk)
class PeriodeAlternatif extends Model
{
    protected $table = 'periode_alternatif';
    protected $primaryKey = 'id_periode_alternatif';

    protected $fillable = [
        'id_periode_kurasi',
        'id_alternatif',
        'status_lolos_legalitas',
        'urutan_input',
        'catatan_kurator',
    ];

    protected $casts = [
        'status_lolos_legalitas' => 'boolean',
    ];

    // Relasi balik ke model periode kurasi induk
    public function periodeKurasi(): BelongsTo
    {
        return $this->belongsTo(PeriodeKurasi::class, 'id_periode_kurasi', 'id_periode_kurasi');
    }

    // Relasi ke model data produk alternatif terkait
    public function alternatif(): BelongsTo
    {
        return $this->belongsTo(Alternatif::class, 'id_alternatif', 'id_alternatif');
    }

    // Relasi satu-ke-banyak ke daftar nilai kriteria produk dalam periode ini
    public function penilaian()
    {
        return $this->hasMany(PenilaianKurasi::class, 'id_periode_alternatif', 'id_periode_alternatif');
    }
}
