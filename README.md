# Sistem Informasi Rekam Medis (Bedah)

Sistem ini adalah aplikasi manajemen rekam medis berbasis web yang dirancang untuk mengelola data pasien, kunjungan, dan proses anamnesis.

## Fitur Utama
- **Autentikasi Pengguna**: Sistem login menggunakan NIP dan password untuk mengamankan akses data.
- **Dashboard**: Ringkasan informasi untuk tenaga medis.
- **Manajemen Pasien**: Pengelolaan data dasar pasien.
- **Kunjungan**: Pencatatan riwayat kunjungan pasien.
- **Anamnesis**: Modul untuk pengisian data anamnesis pasien.
- **Laporan**: Pembuatan laporan medis.
- **Detail Rekam Medis**: Melihat detail riwayat medis pasien secara lengkap.

## Struktur Folder
- `config/`: Konfigurasi koneksi database.
- `includes/`: File pendukung seperti header, footer, dan logika autentikasi.
- `views/`: File tampilan (UI) untuk setiap modul halaman.

## Teknologi yang Digunakan
- **PHP**: Bahasa pemrograman utama.
- **MySQL/MariaDB**: Database untuk penyimpanan data.
- **PDO**: Untuk interaksi database yang aman.

## Cara Instalasi
1. Clone repositori ini ke server lokal Anda.
2. Konfigurasikan database pada file `config/database.php`.
3. Pastikan database memiliki tabel `hrd.datadasar` untuk autentikasi pengguna.
4. Jalankan melalui web server (Apache/Nginx).
