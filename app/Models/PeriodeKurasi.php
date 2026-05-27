<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Model representasi periode penyelenggaraan kurasi produk alternatif
class PeriodeKurasi extends Model
{
    protected $table = 'periode_kurasi';
    protected $primaryKey = 'id_periode_kurasi';

    protected $fillable = [
        'nama_periode',
        'tanggal_kurasi',
        'bulan',
        'tahun',
        'id_kurator',
        'penanggung_jawab',
        'status_kurasi',
        'id_ahp_sesi',
    ];

    protected $casts = [
        'tanggal_kurasi' => 'date',
    ];

    // Relasi ke pengguna sistem yang ditugaskan sebagai kurator periode ini
    public function kurator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_kurator', 'id');
    }

    // Relasi ke data sesi pembobotan kriteria AHP yang digunakan dalam periode
    public function ahpSesi(): BelongsTo
    {
        return $this->belongsTo(AhpSesi::class, 'id_ahp_sesi', 'id_ahp_sesi');
    }



    // Relasi satu-ke-banyak ke daftar produk alternatif yang terdaftar di periode ini
    public function periodeAlternatif(): HasMany
    {
        return $this->hasMany(PeriodeAlternatif::class, 'id_periode_kurasi', 'id_periode_kurasi');
    }
}
