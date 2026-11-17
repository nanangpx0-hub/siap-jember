# Spesifikasi Sistem — Rekrutmen Keputusan Kepala BPS Tahun 2027 (Kabupaten Jember)

## 1. Ringkasan Sistem
Website internal/tertutup untuk mengelola rangkaian seleksi Keputusan Kepala BPS Tahun 2027 di Kabupaten Jember.
- **Sifat Sistem:** Tertutup (hanya mitra yang dipilih Operator melalui import Excel yang memiliki akses seleksi aktif).
- **Sumber Data Awal Mitra:** File Excel `Mitra Kepka 2026` (3.447 baris data, termasuk `SOBAT ID` dan `Email`).
- **Lingkungan Produksi:** cPanel Jagoan Hosting (`bpsjember.com` / subdomain khusus), Apache 2.4, PHP 8.2, MariaDB 10.6.24, upload limit 2G, SMTP `@bpsjember.com`.

---

## 2. Peran Pengguna (RBAC)

| Role (`users.role`) | Deskripsi & Hak Akses |
| :--- | :--- |
| `admin` | Superuser: kelola akun `operator` & `pewawancara`, kelola kegiatan, dan akses penuh seluruh fitur. |
| `operator` | Pengelola teknis: import Master Mitra, import Excel **Peserta Terpilih**, atur **Alokasi Pembagian Tugas**, upload/kelola **Jadwal Wawancara**, upload **Berkas Penyelenggaraan** (per kegiatan), dan publikasi **Pengumuman Hasil**. |
| `pewawancara` | Tim penilai: melihat **Alokasi Tugas** & jadwal wawancaranya, melihat profil & CV peserta yang diuji, serta menginput **Nilai Multi-Aspek & Catatan Wawancara**. |
| `peserta` | Calon peserta terpilih: login via Email SOBAT, akses **Link Undangan**, **Link Jadwal**, **Link Upload Administrasi (CV)**, dan **Link Pengumuman Hasil**. |

---

## 3. Alur Autentikasi & Deep-Link
1. **Login (`/login`):** Menggunakan `email` + `password`.
2. **Password Awal Mitra:** Saat import master mitra / peserta terpilih, akun dibuat dengan `password = Hash::make(sobat_id)` dan `must_change_password = true`.
3. **Wajib Ganti Password:** Middleware memeriksa `must_change_password`; jika `true`, user diarahkan ke `/ganti-password` sebelum bisa mengakses menu lain.
4. **Lupa Password:** Menggunakan Laravel Password Reset melalui SMTP `@bpsjember.com`.
5. **Deep-Link Redirect:** Seluruh link utama (`/undangan`, `/jadwal`, `/administrasi`, `/pengumuman`, `/tugas`) berada di balik middleware `auth`. Jika dibuka sebelum login, Laravel menyimpan `url.intended` dan otomatis mengarahkan kembali setelah login berhasil.

---

## 4. Kontrak Route / Link Utama

| Kebutuhan | URL | Role Diizinkan | Fungsi |
| :--- | :--- | :--- | :--- |
| Link Undangan / Pemanggilan | `/undangan` | `peserta` | Melihat surat/detail pemanggilan seleksi & konfirmasi kehadiran (RSVP). |
| Link Jadwal & Detail Wawancara | `/jadwal` | `peserta`, `pewawancara`, `operator` | Peserta melihat jadwal personal; Pewawancara/Operator melihat jadwal tugas/keseluruhan. |
| Link Pengumuman Hasil | `/pengumuman` | `peserta`, `operator`, `admin` | Peserta melihat status kelulusan personal; Operator mengelola publikasi hasil. |
| Link Upload Administrasi | `/administrasi` | `peserta` (CV), `operator` (Penyelenggaraan) | Peserta upload CV (PDF); Operator upload daftar hadir, dokumentasi, & laporan penyelenggaraan per kegiatan. |
| Alokasi Pembagian Tugas | `/tugas` | `operator`, `pewawancara`, `admin` | Plotting pewawancara & panitia per kegiatan, plus akses form penilaian bagi pewawancara. |
| Panel Penilaian Wawancara | `/wawancara/{jadwal}` | `pewawancara` (pemilik jadwal), `admin` | Input nilai multi-aspek + catatan/rekomendasi; `pewawancara_id` penilaian selalu milik jadwal (tidak tertimpa admin). |
| Panel Operator / Import | `/operator/...` | `operator`, `admin` | Import Excel Master Mitra, Import Excel Peserta Terpilih, Import/Kelola Jadwal, Rekap Nilai. |

---

## 5. Kontrak Skema Database (MariaDB)

### `users`
- `id`, `name`, `email` (unique), `password`, `role` (`enum: admin, operator, pewawancara, peserta`), `sobat_id` (nullable, unique), `no_telp` (nullable), `must_change_password` (boolean, default `false`), `is_active` (boolean, default `true`), `timestamps`

### `mitra_profiles`
Menyimpan atribut dari Excel `Mitra Kepka 2026`:
- `id`, `user_id` (FK -> `users.id`, cascade), `posisi`, `status_seleksi_2026`, `posisi_daftar`
- `alamat_detail`, `alamat_provinsi`, `alamat_kab_kota`, `alamat_kecamatan`, `alamat_desa_kel`
- `ttl`, `umur`, `jenis_kelamin`, `status_kawin`, `pendidikan`, `pekerjaan`
- `terikat_kontrak_kldi`, `nama_kldi`, `punya_motor`, `bisa_motor`, `berpengalaman_capi`, `punya_android`, `merk_hp`, `tipe_hp`, `ram_hp`, `bisa_komputer`, `punya_laptop`
- `pernah_pendataan_bps`, `pernah_sp`, `pernah_st`, `pernah_se`, `pernah_susenas`, `pernah_sakernas`, `pernah_sbh`, `status_nik`
- `timestamps`

### `kegiatan`
- `id`, `nama_kegiatan`, `tahun` (default `2027`), `deskripsi`, `tanggal_mulai`, `tanggal_selesai`, `is_active` (boolean), `pengumuman_dipublikasikan` (boolean, default `false`), `timestamps`

### `peserta_seleksi`
Menghubungkan mitra yang **dipilih lewat Excel Operator** ke suatu `kegiatan`:
- `id`, `kegiatan_id` (FK), `user_id` (FK -> `users.id`)
- `status_undangan` (`enum: diundang, hadir, tidak_hadir`, default `diundang`), `waktu_konfirmasi` (nullable)
- `cv_path` (nullable), `cv_uploaded_at` (nullable)
- `status_hasil` (`enum: belum_ditentukan, lulus, tidak_lulus, cadangan`, default `belum_ditentukan`)
- `catatan_hasil` (nullable)
- `timestamps` (unique constraint: `[kegiatan_id, user_id]`)

### `tugas_panitia`
Alokasi pembagian tugas pewawancara & panitia per kegiatan:
- `id`, `kegiatan_id` (FK), `user_id` (FK -> `users.id`), `peran_tugas` (string: `Pewawancara`, `Moderator`, `Administrasi`, `Dokumentasi`, dll.), `wilayah_atau_ruang` (nullable), `keterangan` (nullable), `timestamps`

### `jadwal_wawancara`
- `id`, `kegiatan_id` (FK), `peserta_seleksi_id` (FK), `pewawancara_id` (FK -> `users.id`)
- `tanggal` (date), `jam_mulai` (time), `jam_selesai` (time), `sesi` (nullable), `metode` (`enum: luring, daring`), `lokasi_atau_link` (string), `catatan_jadwal` (nullable), `timestamps`

### `kriteria_penilaian`
Konfigurasi aspek penilaian wawancara per kegiatan (default saat seeder/buat kegiatan):
1. `Komunikasi` (bobot 25%, skor 0–100)
2. `Pengalaman Lapangan` (bobot 25%, skor 0–100)
3. `Penguasaan Teknologi / CAPI` (bobot 25%, skor 0–100)
4. `Komitmen & Integritas` (bobot 25%, skor 0–100)
- Kolom: `id`, `kegiatan_id` (FK), `nama_aspek`, `deskripsi` (text nullable — panduan pertanyaan wawancara & rubrik skor per aspek, ditampilkan collapsible pada form penilaian pewawancara), `bobot_persen`, `urutan`, `timestamps`

### `penilaian_wawancara` & `penilaian_detail`
- `penilaian_wawancara`: `id`, `jadwal_wawancara_id` (FK, unique), `pewawancara_id` (FK), `nilai_akhir` (decimal 5,2), `rekomendasi` (`enum: sangat_direkomendasikan, direkomendasikan, dipertimbangkan, tidak_direkomendasikan`), `catatan` (text), `submitted_at`, `timestamps`
- `penilaian_detail`: `id`, `penilaian_wawancara_id` (FK), `kriteria_penilaian_id` (FK), `skor` (integer 0–100), `timestamps`

### `pertanyaan_wawancara` & `jawaban_pertanyaan`
Bank pertanyaan wawancara per kegiatan + jawaban tersimpan per pertanyaan (skor aspek otomatis):
- `pertanyaan_wawancara`: `id`, `kegiatan_id` (FK), `kriteria_penilaian_id` (FK), `teks` (text), `jenis` (`enum: binier, kategori, naratif`), `pilihan` (JSON nullable; array `[{teks, skor}]` untuk binier/kategori — skor opsi 0–100), `bobot` (tinyint default 1; bobot pertanyaan dalam aspek), `urutan`, `timestamps`.
- `jawaban_pertanyaan`: `id`, `penilaian_wawancara_id` (FK), `pertanyaan_wawancara_id` (FK), `jawaban` (text nullable; jawaban naratif), `pilihan` (varchar nullable; teks opsi terpilih), `skor` (tinyint 0–100), `timestamps`, unique `(penilaian_wawancara_id, pertanyaan_wawancara_id)` index `jawaban_pertanyaan_unik`.
- **Aturan konversi skor aspek** (server-side, saat submit `/wawancara/{jadwal}`): aspek yang memiliki pertanyaan → `penilaian_detail.skor` = rata-rata tertimbang bobot dari skor tiap pertanyaan (binier/kategori: skor dari opsi terpilih; naratif: skor manual pewawancara 0–100). Aspek tanpa pertanyaan → input skor manual per aspek (kompatibel mundur). `nilai_akhir` tetap = jumlah `skor_aspek × bobot_persen / 100`.

### `dokumen_penyelenggaraan`
Diupload oleh **Operator** per **kegiatan**:
- `id`, `kegiatan_id` (FK), `kategori` (`enum: daftar_hadir, dokumentasi, laporan_penyelenggaraan, sk_atau_surat, lainnya`), `judul`, `file_path`, `original_filename`, `mime_type`, `file_size`, `uploaded_by` (FK -> `users.id`), `timestamps`

---

## 6. Spesifikasi Import Excel
1. **Import Master Mitra Kepka 2026:**
   - Membaca file `.xlsx` (seperti `2026_3509_exportmitrakepka_2026-11-13_173609.xlsx`).
   - Kolom kunci: `SOBAT ID` dan `Email`.
   - Validasi & laporan hasil import: jumlah berhasil, jumlah diperbarui, jumlah dilewati (jika email kosong/duplikat/tidak valid) agar Operator bisa menindaklanjuti.
2. **Import Peserta Terpilih (Undangan Seleksi 2027):**
   - Operator upload `.xlsx` berisi kolom `SOBAT ID` dan/atau `Email` mitra yang dipanggil.
   - Sistem mencocokkan ke `users`, lalu membuat record `peserta_seleksi` pada kegiatan aktif.
