# SIAP Jember — Sistem Informasi Administrasi & Penilaian Mitra Statistik 2027

<p align="center">
  <img src="public/img/brand/siap-logo.svg" width="120" alt="Logo SIAP Jember">
  <br>
  <strong>BPS Kabupaten Jember</strong>
  <br>
  <em>"Siap Menjadi Mitra Statistik Terbaik"</em>
</p>

---

## 📌 Tentang Aplikasi

**SIAP Jember** (*Sistem Informasi Administrasi & Penilaian*) adalah aplikasi web internal dengan **sistem tertutup** yang dirancang khusus untuk mengelola rangkaian seleksi calon mitra statistik Keputusan Kepala BPS Tahun 2027 di lingkungan **Badan Pusat Statistik (BPS) Kabupaten Jember**.

Aplikasi ini mengotomatiskan seluruh alur seleksi dari data master mitra, pemanggilan peserta terpilih, pembagian tugas panitia & jadwal wawancara, konfirmasi kehadiran (RSVP), pengunggahan berkas administrasi (CV), penilaian wawancara multi-aspek dengan rubrik otomatis, hingga penetapan kelulusan dan pengumuman hasil.

---

## 🚀 Fitur Utama

### 1. 🔒 Sistem Tertutup & Keamanan Akun
- **Tanpa Registrasi Publik:** Akses sistem hanya dibuka bagi akun mitra hasil import Excel resmi BPS serta panitia/pewawancara yang ditugaskan.
- **Wajib Ganti Password Awal:** Akun mitra baru menggunakan kata sandi awal `sobat_id` dan diwajibkan mengganti password saat pertama kali login via middleware `MustChangePassword`.
- **Proteksi Berkas Privat:** Berkas CV peserta dan dokumen penyelenggaraan (daftar hadir, dokumentasi, SK) disimpan secara privat di `storage/app/private/...` dan diakses melalui verifikasi hak akses ketat.
- **Keamanan Tambahan:** Dilengkapi rate limiting (`throttle`) pada endpoint login/reset password serta validasi ketat tipe MIME/ekstensi file upload.

### 2. 👥 4 Peran Pengguna (RBAC)
- `superadmin`: Akses penuh ke seluruh fitur dan pengaturan, termasuk hak *override* nilai wawancara.
- `operator`: Manajemen teknis seleksi — import data master mitra, peserta terpilih per kegiatan aktif, penugasan panitia (`/tugas`), penjadwalan wawancara (`/jadwal`), arsip berkas penyelenggaraan, dan publikasi pengumuman kelulusan.
- `pewawancara`: Akses jadwal wawancara miliknya, profil & CV peserta yang diuji, serta form input nilai multi-aspek.
- `peserta`: Calon mitra terpilih yang dapat melihat surat pemanggilan seleksi (undangan), konfirmasi kehadiran (RSVP), melihat jadwal tes, mengunggah CV (PDF), dan melihat pengumuman kelulusan saat dipublikasikan.

### 3. 🎯 Penilaian Wawancara Multi-Aspek & Bank Pertanyaan Dinamis
- 4 aspek penilaian default (bobot masing-masing 25%):
  1. **Komunikasi**
  2. **Pengalaman Lapangan**
  3. **Penguasaan Teknologi / CAPI**
  4. **Komitmen & Integritas**
- Mendukung bank pertanyaan dinamis (`binier`, `kategori`, `naratif`) yang secara otomatis mengkalkulasi skor aspek dan nilai akhir berbobot secara real-time via Alpine.js serta divalidasi ulang di sisi server.
- Setiap kegiatan baru yang ditambahkan otomatis mewarisi 4 kriteria dan 17 pertanyaan default.

### 4. 📊 Pengolahan Data Excel Berkinerja Tinggi
- **Import Master Mitra:** Dioptimalkan dengan *in-memory user preloading* dan transaksi batch per-chunk sehingga mampu memproses ribuan data mitra Kepka tanpa risiko timeout di server hosting.
- **Import Peserta Terpilih:** Cukup menggunakan satu kolom `Email`.
- **Import & Kelola Jadwal:** Bersifat *idempotent* (mencegah duplikasi jadwal saat import ulang).
- **Export Rekap Nilai:** Fitur ekspor Excel lengkap dengan format yang kompatibel langsung untuk pengunggahan hasil kelulusan.

---

## 🛠️ Tech Stack & Arsitektur

- **Backend:** Laravel 11 (PHP 8.2+)
- **Database:** MariaDB 10.6+ / MySQL 8.0+
- **Frontend / UI:** Blade + AdminLTE 3.2 (Bootstrap 4.6, FontAwesome 5, jQuery) + Alpine.js
- **Excel Engine:** `maatwebsite/excel` (PhpSpreadsheet)
- **Target Deployment:** Apache 2.4 / cPanel Hosting (`bpsjember.com` / subdomain)

---

## 💻 Panduan Instalasi Lokal

### 1. Prasyarat
- PHP 8.2 atau lebih tinggi dengan ekstensi: `pdo_mysql`, `mbstring`, `fileinfo`, `zip`, `gd`/`imagick`, `xml`
- Composer 2.x
- MySQL / MariaDB Server

### 2. Langkah Pemasangan
```bash
# 1. Clone repositori
git clone https://github.com/nanangpx0-hub/siap-jember.git
cd siap-jember

# 2. Pasang dependensi
composer install

# 3. Konfigurasi file Environment
cp .env.example .env
php artisan key:generate

# Sesuaikan kredensial database di file .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=rekrutmen_bps_2027
# DB_USERNAME=root
# DB_PASSWORD=

# 4. Jalankan migrasi dan seeder awal
php artisan migrate --seed

# 5. Jalankan server lokal
php artisan serve
```

Akses portal di peramban: `http://localhost:8000`

---

## 🔑 Akun Bawaan (Default Seeder)

| Role | Email | Password Awal |
| :--- | :--- | :--- |
| **Super Administrator** | `admin@bpsjember.com` | `password` |
| **Operator** | `operator@bpsjember.com` | `password` |

> ⚠️ **PENTING:** Segera ubah kata sandi akun bawaan setelah login pertama di lingkungan produksi melalui menu **Ganti Password**.

---

## 🧪 Pengujian Otomatis (Automated Tests)

Seluruh modul dan kontrak keamanan diuji menggunakan PHPUnit:
```bash
php artisan test
```
Cakupan pengujian:
- **Auth & Access:** Proteksi `/register`, login email, redirect ganti password paksa, `url.intended`.
- **Role Isolation:** Penolakan akses lintas role (peserta, operator, pewawancara, superadmin).
- **Data & Privacy Isolation:** Proteksi CV antar-peserta, isolasi jadwal wawancara, perlindungan berkas penyelenggaraan privat.
- **Business Flow:** Alur seleksi lengkap (RSVP, upload CV, penilaian wawancara, pengumuman hasil).
- **Management & Import:** Idempotensi import jadwal, pencegahan duplikasi, aktivasi kegiatan, proteksi upload file berbahaya.

---

## 🌐 Dokumentasi Teknis Tambahan

Dokumentasi detail lainnya tersedia pada direktori `docs/`:
- [docs/SPEC.md](docs/SPEC.md) — Kontrak teknis route, skema database, dan aturan RBAC.
- [docs/TASKS.md](docs/TASKS.md) — Papan kerja implementasi & riwayat task multi-agent.
- [docs/DEPLOY_CPANEL.md](docs/DEPLOY_CPANEL.md) — Panduan deployment ke cPanel Jagoan Hosting.
- [docs/BRANDING.md](docs/BRANDING.md) — Panduan identitas visual dan logo SIAP Jember.
- [docs/LAN_ACCESS.md](docs/LAN_ACCESS.md) — Panduan konfigurasi akses jaringan lokal (LAN/MikroTik).

---

## 📄 Lisensi

Aplikasi ini dikembangkan untuk keperluan dinas internal **Badan Pusat Statistik Kabupaten Jember**.
