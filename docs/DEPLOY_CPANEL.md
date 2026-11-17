# Deploy ke cPanel Jagoan Hosting (bpsjember.com)

Dokumen ini untuk deployment aplikasi **Rekrutmen Keputusan Kepala BPS Tahun 2027** (Laravel 11) ke **cPanel Jagoan Hosting**.

## 1. Spesifikasi & Asumsi Target
- **Domain/subdomain:** `rekrutmen2027.bpsjember.com` (contoh) atau subdomain lain di bawah `bpsjember.com`.
- **Web server:** Apache 2.4 (cPanel)
- **PHP:** 8.2 (pilih/aktifkan PHP 8.2 di MultiPHP Manager/Select PHP Version; pastikan ekstensi umum Laravel aktif: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `zip`, `gd`/`imagick` sesuai kebutuhan).
- **Database:** MariaDB 10.6.24 (buat database & user via "MySQL Databases" di cPanel).
- **SMTP:** akun `@bpsjember.com` (Email Accounts di cPanel).

## 2. Persiapan File Aplikasi
1. **Upload source code** ke direktori aplikasi, misal `/home/<cpanel_user>/apps/rekrutmen2027` (di luar `public_html` lebih aman) atau sesuai kebijakan hosting. Sertakan folder `vendor/` (jika tidak menjalankan Composer di server) dan `public/build` jika ada aset build; aplikasi ini menggunakan Tailwind/Alpine via CDN sehingga **tidak wajib** proses build Node di server.
2. Pastikan file/folder penting ada: `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `vendor/`, `.env` (dibuat di server), `artisan`, `composer.json`.
3. Jangan upload `.env` dari lokal; buat `.env` produksi di server.

## 3. Document Root Subdomain (public Laravel)
Di cPanel → **Domains/Subdomains**:
- Buat subdomain `rekrutmen2027.bpsjember.com`.
- Set **Document Root** ke folder `public/` aplikasi, contoh: `/home/<cpanel_user>/apps/rekrutmen2027/public`.

> Jika tidak memungkinkan mengarahkan Document Root langsung ke `public/` (kebijakan tertentu), opsi alternatif: letakkan isi `public/` ke `public_html` subdomain dan sesuaikan path `__DIR__.'/../...'` di `public/index.php` agar menunjuk ke root aplikasi di luar `public_html`. **Opsi yang direkomendasikan adalah Document Root ke `public/`.**

## 4. Konfigurasi `.env` Produksi (contoh)
Buat salin dari `.env.example` dan sesuaikan:

```env
APP_NAME="Rekrutmen BPS 2027"
APP_ENV=production
APP_KEY=base64:... (generate sekali)
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://rekrutmen2027.bpsjember.com

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=<cpanelprefix>_rekrutmen2027
DB_USERNAME=<cpanelprefix>_dbuser
DB_PASSWORD=<password_db>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mail.bpsjember.com
MAIL_PORT=465
MAIL_USERNAME=noreply@bpsjember.com
MAIL_PASSWORD=<password_email>
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@bpsjember.com
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=local
```

Catatan:
- Generate `APP_KEY` (via SSH/terminal cPanel jika tersedia): `php artisan key:generate --force` atau buat lokal lalu salin.
- Sesuaikan `MAIL_HOST/PORT/ENCRYPTION` dengan konfigurasi SMTP akun `@bpsjember.com` yang berlaku di hosting (umum: 465/ssl atau 587/tls).
- `APP_DEBUG=false` wajib di produksi.

## 5. Izin Folder (Permissions)
Pastikan web server (user PHP/Apache) dapat menulis ke:
- `storage/` (seluruhnya, termasuk `storage/app/private/...`, `storage/framework/*`, `storage/logs`)
- `bootstrap/cache/`

Umum di cPanel (sesuaikan): folder `755`, file `644`, dan pastikan kepemilikan sesuai akun cPanel. Hindari `777` kecuali terpaksa.

## 6. Migrasi, Seed Awal, dan Optimasi
Jalankan via **Terminal/SSH** di cPanel (jika tersedia) dari root aplikasi. Gunakan PHP 8.2 yang sesuai (mis. `php`/`php82`/`ea-php82` tergantung server):

```bash
php -v   # pastikan 8.2.x

php artisan migrate --force
php artisan db:seed --force

php artisan storage:link   # opsional; file privat (CV, penyelenggaraan) tidak memakai public storage link
php artisan config:cache
php artisan route:cache
php artisan view:cache
# atau: php artisan optimize
```

**Seed awal** (`db:seed`) membuat akun admin/operator, 1 kegiatan 2027 aktif, dan 4 kriteria penilaian default (lihat `database/seeders/DatabaseSeeder.php`). **Ganti password default** akun seed segera setelah login pertama di produksi.

## 7. .htaccess (Apache/cPanel)
- File `public/.htaccess` sudah disertakan (standar Laravel): rewrite ke `index.php`, penanganan `Authorization` header, nonaktifkan `MultiViews/Indexes`, redirect trailing slash. Cocok untuk Apache 2.4 + `mod_rewrite` di cPanel.
- Pastikan **`mod_rewrite` aktif** dan subdomain mengizinkan override (`AllowOverride All`) agar `.htaccess` berlaku (umumnya default di cPanel).

## 8. Keamanan & Operasional Produksi
- `APP_DEBUG=false`, `APP_ENV=production`, HTTPS aktif (pasang SSL/TLS untuk subdomain via AutoSSL/cPanel) dan pastikan `APP_URL` https.
- Route `/register` dinonaktifkan (sistem tertutup). Password awal mitra = `sobat_id` dengan `must_change_password=true` (dipaksa ganti saat login pertama).
- File privat (CV peserta, berkas penyelenggaraan) disimpan di `storage/app/private/...` dan diakses melalui controller berproteksi role/kepemilikan (bukan via `public/`).
- Backup berkala: database MariaDB + folder `storage/app/private`.
- Jika menggunakan cron (queue email terjadwal, dsb.): daftarkan `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1` dan sesuaikan `QUEUE_CONNECTION` bila memakai worker.

## 9. Verifikasi Pasca-Deploy
- Akses `https://rekrutmen2027.bpsjember.com/login` (redirect `/` ke login).
- Login akun seed, pastikan paksa ganti password (jika diberlakukan), cek role dashboard (`/operator/dashboard`, `/pewawancara/dashboard`, `/peserta/dashboard`).
- Uji route kontrak: `/undangan`, `/jadwal`, `/administrasi`, `/pengumuman`, `/tugas` sesuai role.
- Uji kirim email reset password via SMTP `@bpsjember.com`.
- Konfirmasi `APP_DEBUG=false` dan halaman error tidak menampilkan stack trace.
