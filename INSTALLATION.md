# Panduan Instalasi & Setup Proyek (DSS Kurasi)

Dokumen ini berisi panduan lengkap langkah-langkah instalasi dan setup proyek SPK (Sistem Pendukung Keputusan) Kurasi Produk dari awal di perangkat baru.

---

## 🛠️ 1. Persyaratan Sistem (Prerequisites)

Sebelum memulai, pastikan perangkat Anda sudah terinstal software berikut:

1. **Laragon (Rekomendasi untuk Windows)**
   * Digunakan sebagai local server environment (Apache/Nginx & MySQL).
2. **PHP (Versi >= 8.2)**
   * Laravel 12 membutuhkan PHP minimal versi 8.2.
3. **Composer**
   * Digunakan untuk mengelola library/dependency PHP.
4. **Node.js (Versi >= 20.19 atau >= 22.12)**
   * Vite 7 dan Tailwind CSS v4 membutuhkan Node.js versi modern (minimal versi 20).

---

## ⚙️ 2. Konfigurasi Awal Lingkungan (Environment Setup)

### A. Mengaktifkan Ekstensi PHP di Laragon
Proyek ini membutuhkan beberapa ekstensi PHP aktif agar dapat berjalan dengan baik (termasuk untuk manipulasi file Excel dan koneksi database).

1. Buka aplikasi Laragon.
2. **Klik kanan** pada jendela Laragon.
3. Arahkan ke **PHP** > **Extensions**.
4. Pastikan ekstensi berikut **dicentang**:
   * [x] **`zip`** (Dibutuhkan untuk import/export Excel)
   * [x] **`pdo_mysql`** (Dibutuhkan jika menggunakan database MySQL)
5. Klik **Stop** lalu **Start All** pada Laragon untuk merestart server.

### B. Mengatur Kebijakan Keamanan PowerShell (Windows)
Untuk menghindari error pemblokiran script (seperti `npm.ps1` atau `vite.ps1` cannot be loaded), ubah kebijakan eksekusi PowerShell Anda:

1. Buka menu Start Windows, cari **PowerShell**.
2. Klik kanan **Windows PowerShell** > pilih **Run as Administrator**.
3. Jalankan perintah berikut:
   ```powershell
   Set-ExecutionPolicy RemoteSigned -Force
   ```
4. Tutup jendela PowerShell tersebut.

---

## 📂 3. Langkah-langkah Setup Proyek

### Langkah 1: Clone Proyek & Masuk Direktori
Letakkan folder proyek di dalam direktori root Laragon Anda (biasanya di `C:\laragon\www\dss-kurasi`). Buka terminal (disarankan menggunakan **Terminal bawaan Laragon**).

### Langkah 2: Buat Database di phpMyAdmin (Jika Menggunakan MySQL)
1. Buka phpMyAdmin di browser ([http://localhost/phpmyadmin](http://localhost/phpmyadmin)).
2. Buat database baru kosong dengan nama, contoh: **`dss_kurasi`**.

### Langkah 3: Konfigurasi File `.env`
1. Di dalam folder proyek, salin file `.env.example` menjadi `.env`.
2. Buka file `.env` dan sesuaikan pengaturan database Anda:
   * **Jika menggunakan MySQL (Direkomendasikan):**
     ```ini
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=dss_kurasi
     DB_USERNAME=root
     DB_PASSWORD=
     ```
   * **Jika menggunakan SQLite:**
     ```ini
     DB_CONNECTION=sqlite
     ```

### Langkah 4: Jalankan Setup Otomatis
Jalankan perintah berikut di terminal proyek untuk menginstal semua dependency PHP & Node.js, melakukan migrasi database, dan membangun aset frontend:

```bash
composer run setup
```

> **Catatan:** Perintah di atas secara otomatis menjalankan rangkaian perintah berikut:
> 1. `composer install` (Instal library PHP)
> 2. Copy `.env.example` ke `.env` (jika belum ada)
> 3. `php artisan key:generate` (Membuat kunci enkripsi aplikasi)
> 4. `php artisan migrate --force` (Membuat tabel database)
> 5. `npm install` (Instal library JavaScript/CSS)
> 6. `npm run build` (Membangun aset produksi)

---

## 🚀 4. Menjalankan Aplikasi dalam Mode Pengembangan (Dev Mode)

Setelah proses setup selesai dengan sukses, jalankan server pengembangan menggunakan:

```bash
composer run dev
```

Perintah ini akan menjalankan server lokal Laravel (`php artisan serve`) sekaligus compiler aset Vite (`npm run dev`) secara bersamaan dalam satu terminal.

Akses aplikasi melalui browser di alamat:
🔗 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔍 Troubleshooting Masalah Umum

* **Error: `could not find driver`**
  * Solusi: Pastikan ekstensi `pdo_mysql` (untuk MySQL) atau `pdo_sqlite` (untuk SQLite) sudah dicentang di menu Laragon PHP Extensions dan server telah di-restart.
* **Error: `Failed to open stream: No such file or directory (vendor/autoload.php)`**
  * Solusi: Folder `vendor` belum terbuat. Jalankan `composer install` secara manual.
* **Error: `'vite' is not recognized...`**
  * Solusi: Folder `node_modules` belum terbuat atau proses instalasi NPM gagal. Jalankan `npm install` secara manual.
