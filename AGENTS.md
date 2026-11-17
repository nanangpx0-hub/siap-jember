# AGENTS.md — Panduan Orkestrasi Multi-Agent

Proyek ini adalah **Website Rekrutmen Keputusan Kepala BPS Tahun 2027 (BPS Kabupaten Jember)** dengan **sistem tertutup**.
Dokumen ini menjadi aturan bersama untuk seluruh AI coding agent (**Antigravity, OpenCode, Cline, Kilo Code, Kiro**, dll.) yang bekerja di repositori `d:\Projek\rekrutmen`.

---

## 1. Stack & Target Lingkungan
- **Framework:** Laravel 11 (PHP 8.2)
- **Database:** MariaDB 10.6 / MySQL
- **Frontend:** Blade + Tailwind CSS + Alpine.js (ringan, tanpa SPA rumit, mudah di-deploy ke shared hosting)
- **Target Hosting:** cPanel Jagoan Hosting (`bpsjember.com`, Apache 2.4, PHP 8.2, MariaDB 10.6.24)
- **Email:** SMTP akun `@bpsjember.com`

---

## 2. Aturan Wajib Sebelum Menulis Kode
1. **Baca Kontrak Terlebih Dahulu:** Selalu baca `docs/SPEC.md` (skema database, route, dan hak akses) serta `docs/TASKS.md` (daftar tugas & status) sebelum mengubah apa pun.
2. **Klaim Tugas di `docs/TASKS.md`:**
   - Ubah status task yang dikerjakan dari `[ ]` menjadi `[/]` disertai nama agent (contoh: `[/] (OpenCode)` atau `[/] (Antigravity)`).
   - Setelah selesai dan terverifikasi, ubah menjadi `[x]` dan cantumkan ringkasan file yang dibuat/diubah.
3. **Hormati Batas Wilayah (Track Ownership):** Kerjakan hanya file dan modul yang termasuk dalam Track tugas yang Anda ambil agar tidak terjadi konflik merge antar-agent.
4. **Dilarang Mengubah Kontrak Skema Sepihak:** Nama tabel, kolom inti, nama role, dan route kontrak di `docs/SPEC.md` adalah kontrak lintas-modul. Jika ada kebutuhan perubahan, catat di `docs/SPEC.md` dan selesaikan di Track 1.

---

## 3. Aturan Keamanan & Sistem Tertutup
- **Tidak ada registrasi publik.** Route `/register` harus dinonaktifkan.
- **Autentikasi:** Login menggunakan `email` (dari data SOBAT). Password awal mitra adalah `sobat_id`, dan `must_change_password = true` wajib memaksa user mengganti password saat login pertama.
- **4 Role Resmi:** `admin`, `operator`, `pewawancara`, `peserta`. Gunakan middleware role di setiap group route.
- **File Privat:** CV peserta dan berkas penyelenggaraan (daftar hadir, dokumentasi, laporan) **wajib** disimpan di `storage/app/private/...` (bukan `public/`), dan hanya boleh diunduh melalui controller yang memverifikasi role & kepemilikan data.
- **Validasi Input & Upload:** Selalu validasi MIME type, ekstensi, dan ukuran file pada setiap endpoint upload.
