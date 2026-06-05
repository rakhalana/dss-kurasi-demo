# PRD — Sistem Pendukung Keputusan (DSS) Kurasi Produk UMKM
## Kabupaten Sidoarjo

Sistem ini dirancang untuk mendigitalkan dan mengobjektifkan proses kurasi produk pangan UMKM di Kabupaten Sidoarjo menggunakan metode hybrid **AHP** (untuk pembobotan) dan **Profile Matching** (untuk pemeringkatan).

---

## 1. Visi & Tujuan
Proses kurasi manual seringkali subjektif dan sulit dipertanggungjawabkan. Sistem ini hadir untuk:
- **Objektivitas**: Menghilangkan bias personal dalam penilaian.
- **Transparansi**: Memberikan alasan logis (gap analisis) mengapa sebuah produk lolos atau tidak.
- **Efisiensi**: Mempercepat proses perolehan hasil dari ribuan produk UMKM.

---

## 2. Peran Pengguna (User Roles)

| Peran | Tanggung Jawab Utama |
| :--- | :--- |
| **Admin** | Mengelola Kriteria, mengatur Bobot AHP, Manajemen Produk (Data Master), dan memantau seluruh Periode Kurasi. |
| **Kurator** | Melakukan penilaian lapangan/aktual terhadap produk pada periode yang sedang berjalan. |

---

## 3. Metodologi DSS (Logic Flow)

Sistem menggunakan dua algoritma utama yang bekerja secara berurutan:

### A. Analytical Hierarchy Process (AHP)
Digunakan untuk menentukan **Bobot Kepentingan** setiap kriteria.
1. **Pairwise Comparison**: Admin membandingkan kriteria A vs B (Skala Saaty 1-9).
2. **Consistency Check**: Sistem menghitung Consistency Ratio (CR). Harus **≤ 0.1** untuk dianggap valid.
3. **Output**: Nilai Prioritas (Eigenvector) yang akan menjadi pengali di tahap Profile Matching.

### B. Profile Matching
Digunakan untuk menghitung **Ranking Produk** berdasarkan kesesuaian dengan profil ideal.
1. **Gap Analysis**: `Gap = Nilai Aktual (Kurator) - Nilai Target (Admin)`.
2. **Bobot Gap**: Mengonversi nilai Gap menjadi bobot (0 s/d 5) sesuai tabel standar.
3. **Total Scoring**: `Final Score = Σ (Bobot Gap * Bobot AHP)`.
4. **Ranking**: Produk diurutkan berdasarkan Score tertinggi.

---

## 4. Detil Modul & Fitur

### 🛠️ Modul 1: Fondasi Sistem (Core)
Status: ✅ **DONE**
- **Autentikasi**: Sistem login aman untuk Admin dan Kurator. Dilengkapi fitur *Remember Me* dan proteksi logout (modal konfirmasi).
- **Dashboard Dynamic**: Tampilan berbeda untuk tiap role (Admin melihat statistik global, Kurator melihat tugas aktif).
- **UI/UX Consistency**: Menggunakan Bootstrap 4 dengan SCSS kustom, AOS animations, dan standarisasi breadcrumb untuk tampilan premium.
- **Efficient Styling**: Arsitektur SCSS modular dengan utility global untuk konsistensi antar-peran.

### 📋 Modul 2: Manajemen Kriteria & Skala
Status: ✅ **DONE**
- **CRUD Kriteria**: Admin dapat menambah/edit kriteria (Contoh: "Rasa", "Harga", "Higienitas").
- **Kategorisasi Aspek**: Setiap kriteria dikelompokkan ke dalam "Kualitas Produk" atau "Kemasan".
- **Target Nilai (1-5)**: Admin menetapkan skor ideal (profil target) untuk setiap kriteria.
- **Manajemen Skala (Rubrik)**: Setiap kriteria memiliki 5 tingkatan rubrik (1-5). Skala yang nonaktif tidak akan muncul di form kurator.

### ⚖️ Modul 3: Kalkulasi AHP (Pembobotan)
Status: ✅ **DONE**
- **Live Calculation Matrix**: Antarmuka matriks $n \times n$ dengan perhitungan real-time di sisi klien.
- **Auto-Reciprocal**: Pengisian nilai otomatis berpasangan (A vs B = 3, maka B vs A = 0.33).
- **Uji Konsistensi (CR)**: Perhitungan $\lambda_{max}$, CI, dan CR otomatis. Sistem memblokir simpan jika CR > 0.1.
- **Sesi AHP Active**: Mendukung multi-sesi, namun hanya satu sesi aktif yang digunakan sebagai acuan pembobotan periode kurasi baru.

### 📦 Modul 4: Manajemen Produk & Legalitas
Status: ✅ **DONE**
- **Data Master Produk**: CRUD lengkap dengan upload foto dan tracking brand.
- **Auto-Legalitas Generation**: Data legalitas otomatis terbuat saat produk ditambahkan.
- **Filter Legalitas (Entry Barrier)**: Produk wajib lolos filter NIB, Halal, dan (BPOM atau PIRT).
- **Dynamic Sync**: Perubahan legalitas pada data master otomatis sinkron ke periode kurasi berstatus "Belum". Periode yang sudah "Berlangsung/Selesai" tetap menggunakan snapshot data lama demi integritas penilaian.

### ✍️ Modul 5: Manajemen Periode & Penilaian (Kurator)
Status: ✅ **DONE**
- **Batching (Periode Kurasi)**: Manajemen jadwal kurasi dengan sistem status (Belum, Berlangsung, Selesai).
- **Locking Mechanism**: Admin dilarang mengedit detail periode atau daftar produk jika status sudah "Berlangsung/Selesai".
- **Wizard Workspace**: Antarmuka penilaian kurator yang intuitif (wizard-style) dengan navigasi produk independen.
- **Progress Tracker & Completion**: Pelacakan progres real-time dan alur penyelesaian tugas kurasi yang formal.

### 📊 Modul 6: Hasil, Ranking & Laporan
Status: ✅ **DONE**
- **Profile Matching Engine**: Kalkulasi gap dan konversi bobot sesuai parameter yang diset admin.
- **Leaderboard**: Tabel ranking produk berdasarkan skor final gabungan AHP-PM.
- **Export PDF**: Generasi laporan resmi per periode.

---

## 5. Development Roadmap (Updated)

1. **Phase 1 (Core)**: Setup Auth, Dashboard, & CRUD Kriteria. ✅
2. **Phase 2 (AHP)**: Implementasi Matriks Perbandingan & Uji Konsistensi. ✅
3. **Phase 3 (Product & Legal)**: CRUD Produk & Logika Filter Legalitas Otomatis. ✅
4. **Phase 4 (Assessment)**: Manajemen Periode & Workspace Penilaian Kurator. ✅
5. **Phase 5 (Calculation & Reporting)**: Integrasi Profile Matching & Export PDF. ✅

---

## 6. Tech Stack
- **Backend**: Laravel 11.x (PHP 8.2+)
- **Frontend**: Blade, Vanilla CSS + SCSS Modular, Lucide Icons, AOS (Animate On Scroll)
- **Build Tool**: Vite
- **Database**: MySQL 8.0