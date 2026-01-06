# Sistem Informasi Pengajuan Cuti (SIP CUTI) - PN Makassar

Aplikasi berbasis Web (PHP) untuk mengelola proses pengajuan cuti pegawai secara digital. Aplikasi ini mendukung otomasi pembuatan dokumen formulir cuti dalam format `.docx` (Microsoft Word) sesuai dengan standar Mahkamah Agung.

## 🚀 Fitur Utama
- **Dashboard Pegawai**: Pantau status cuti dan sisa kuota cuti tahunan.
- **Otomasi Dokumen (.docx)**:
  - **Template Umum (`FORM_CUTI_fixx.docx`)**: Digunakan oleh staf/pegawai umum (memerlukan tanda tangan atasan langsung).
  - **Template Khusus (`FORM_CUTI1.docx`)**: Digunakan khusus oleh **Panitera, Sekretaris, dan Wakil Ketua** (langsung ditujukan kepada Ketua).
- **Sistem Persetujuan (Approval)**: Alur kerja persetujuan dari Atasan Langsung hingga pimpinan tertinggi.
- **Tanda Tangan Digital**: Menyisipkan file tanda tangan (`.png`) secara otomatis ke dalam dokumen cetak.
- **Manajemen User**: Pengelolaan data pegawai, jabatan, dan hak akses.

## 🛠️ Teknologi yang Digunakan
- **PHP 8.x** (Native)
- **MySQL/MariaDB**
- **Tailwind CSS** (Interface)
- **Composer** (Dependency Manager)
- **PHPWord Library** (Template Processing)

## ⚙️ Cara Instalasi (Localhost)

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di lingkungan lokal menggunakan XAMPP:

### 1. Persiapan Repositori
Clone proyek ini ke dalam folder `htdocs` Anda:
```bash
git clone [https://github.com/starsmart12/sip-cuti-app.git](https://github.com/starsmart12/sip-cuti-app.git)
cd sip-cuti-app
composer install

```

### 2. Konfigurasi Database (phpMyAdmin)

1. Buka browser dan akses `http://localhost/phpmyadmin`.
2. **Buat Database Baru**: Klik menu **New**, beri nama database **`db_cuti_karyawan`**, lalu klik **Create**.
3. **Impor Skema**:
* Pilih database `db_cuti_karyawan`.
* Klik tab **Import**.
* Klik **Choose File** dan pilih file `database_schema.sql` dari folder proyek.
* Gulir ke bawah dan klik **Go** / **Import**.



### 3. Konfigurasi Koneksi PHP

1. Masuk ke folder `config/` di dalam proyek Anda.
2. Salin file `config.php.example` dan ubah namanya menjadi **`config.php`**.
3. Buka file `config.php` menggunakan teks editor (VS Code/Notepad++) dan sesuaikan kredensial database Anda:
```php
$host = "localhost";
$user = "root";      // Default user XAMPP
$pass = "";          // Default password XAMPP (kosong)
$db   = "db_cuti_karyawan";  // Nama database yang Anda buat

s

### 4. Konfigurasi Tanda Tangan

Aplikasi mencari tanda tangan berdasarkan NIP. Pastikan file tanda tangan disimpan di:
`assets/img/ttd/[NIP_PEGAWAI]_ttd.png` (Contoh: `19920101_ttd.png`).

### 5. Jalankan Aplikasi

Akses melalui browser di alamat: `http://localhost/sip_cuti`

## 📂 Struktur Folder Utama

* `assets/` : Menyimpan CSS, Gambar, Tanda Tangan, dan **Template DOCX**.
* `config/` : Pengaturan koneksi database.
* `modules/` : Inti logika aplikasi (Proses Cuti, Auth, User).
* `vendor/` : Library pihak ketiga (dihasilkan melalui Composer).