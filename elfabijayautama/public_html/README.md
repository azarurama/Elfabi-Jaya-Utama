# PT Elfabi Jaya Utama - Company Profile Website

Website profil perusahaan PT Elfabi Jaya Utama yang menampilkan layanan, portofolio, dan informasi kontak perusahaan.

## Fitur

- Halaman Beranda dengan tampilan menarik
- Halaman Tentang Kami yang informatif
- Daftar Layanan perusahaan
- Galeri Portofolio proyek
- Daftar klien mitra
- Halaman Kontak dengan peta interaktif
- Panel Admin untuk mengelola konten
- Backup & Restore database
- Responsive design untuk semua perangkat

## Persyaratan Sistem

- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Web server (Apache/Nginx)
- Composer (untuk dependensi PHP)
- Node.js & NPM (untuk aset frontend)

## Instalasi

1. Clone repository ini ke direktori web server Anda:
   ```
   git clone [repo-url] elfabi
   cd elfabi
   ```

2. Salin file konfigurasi:
   ```
   cp config/config.example.php config/config.php
   ```

3. Edit file konfigurasi (`config/config.php`) dan sesuaikan dengan pengaturan database Anda.

4. Import database:
   - Buat database baru
   - Import file `database/elfabi.sql` ke database yang telah dibuat
   - Atau akses `install.php` di browser untuk melakukan instalasi otomatis

5. Atur izin direktori:
   ```
   chmod -R 755 uploads/
   chmod -R 755 backups/
   ```

6. Akses website di browser:
   ```
   http://localhost/elfabi
   ```

7. Login ke admin panel:
   ```
   http://localhost/elfabi/admin
   ```
   - Email: admin@example.com
   - Password: password123 (segera ganti setelah login pertama)

## Struktur Direktori

```
elfabi/
├── admin/              # Panel admin
├── assets/             # Aset frontend (CSS, JS, gambar)
├── config/             # File konfigurasi
├── core/               # File inti aplikasi
├── uploads/            # File yang diunggah
│   ├── clients/        # Logo klien
│   └── portfolio/      # Gambar portofolio
├── views/              # Template view
└── index.php           # File masuk utama
```

## Keamanan

- Selalu gunakan HTTPS di production
- Simpan file `config.php` di luar root web server jika memungkinkan
- Selalu update ke versi terbaru dari dependensi
- Backup database secara berkala
- Gunakan password yang kuat untuk akun admin

## Kontribusi

1. Fork repository ini
2. Buat branch untuk fitur baru (`git checkout -b fitur-baru`)
3. Commit perubahan Anda (`git commit -am 'Menambahkan fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## Lisensi

Proprietary - © 2025 PT Elfabi Jaya Utama. Seluruh hak cipta dilindungi.
