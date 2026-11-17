# Spesifikasi Sistem — Rekrutmen Keputusan Kepala BPS Tahun 2027 (Kabupaten Jember)

## 1. Ringkasan Sistem
Website internal/tertutup untuk mengelola rangkaian seleksi Keputusan Kepala BPS Tahun 2027 di Kabupaten Jember.
- **Sifat Sistem:** Tertutup (hanya mitra yang dipilih Admin melalui import Excel yang memiliki akses seleksi aktif).
- **Sumber Data Awal Mitra:** File Excel `Mitra Kepka 2026` (3.447 baris data, termasuk `SOBAT ID` dan `Email`).
- **Lingkungan Produksi:** cPanel Jagoan Hosting (`bpsjember.com` / subdomain khusus), Apache 2.4, PHP 8.2, MariaDB 10.6.24, upload limit 2G, SMTP `@bpsjember.com`.

---

## 2. Peran Pengguna (RBAC)

| Role (`users.role`) | Deskripsi & Hak Akses |
| :--- | :--- |
| `superadmin` | Superuser: akses penuh seluruh fitur, termasuk override penilaian wawancara (dapat menginput nilai jadwal siapa pun). |
| `operator` | Pengelola teknis: import Master Mitra, import Excel **Peserta Terpilih**, atur **Alokasi Pembagian Tugas** (termasuk membuat akun `pewawancara`), upload/kelola **Jadwal Wawancara**, upload **Berkas Penyelenggaraan** (per kegiatan), dan publikasi **Pengumuman Hasil**. Tidak dapat menginput penilaian wawancara. |
| `pewawancara` | Tim penilai: melihat **Alokasi Tugas** & jadwal wawancaranya, melihat profil & CV peserta yang diuji, serta menginput **Nilai Multi-Aspek & Catatan Wawancara**. Akses dibatasi pada jadwal miliknya (`jadwal_wawancara.pewawancara_id`). |
| `peserta` | Calon peserta terpilih: login via Email SOBAT, akses **Link Undangan**, **Link Jadwal**, **Link Upload Administrasi (CV)**, dan **Link Pengumuman Hasil**. |

**Catatan:** `superadmin` memiliki akses penuh sebagai override, termasuk dapat membuka dan menginput penilaian jadwal milik pewawancara mana pun (`pewawancara_id` pada `penilaian_wawancara` tetap mengikuti jadwal, tidak tertimpa).

**Riwayat pemetaan role:**
- Migrasi `2027_01_01_000130`: `admin` lama → `superadmin`; `operator` → `admin`; `pewawancara` → `admin`; `peserta` tetap.
- Migrasi `2027_01_01_000140` (final, sesuai permintaan user): `superadmin` tetap; `admin` → `operator`; role `pewawancara` dipulihkan; `peserta` tetap (3.443 akun tidak berubah). Dua akun pewawancara lama (`pewawancara.uji@bpsjember.com`, `nanangpx@gmail.com`) dikembalikan ke `role=pewawancara` dari snapshot.

---

## 3. Alur Autentikasi & Deep-Link
1. **Login (`/login`):** Menggunakan `email` + `password`.
2. **Password Awal Mitra:** Saat import master mitra / peserta terpilih, akun dibuat dengan `password = Hash::make(sobat_id)` dan `must_change_password = true`.
3. **Wajib Ganti Password:** Middleware memeriksa `must_change_password`; jika `true`, user diarahkan ke `/ganti-password` sebelum bisa mengakses menu lain.
4. **Lupa Password:** Menggunakan Laravel Password Reset melalui SMTP `@bpsjember.com`.
5. **Deep-Link Redirect:** Seluruh link utama (`/undangan`, `/jadwal`, `/administrasi`, `/pengumuman`, `/tugas`) berada di balik middleware `auth`. Jika dibuka sebelum login, Laravel menyimpan `url.intended` dan otomatis mengarahkan kembali setelah login berhasil.
6. **Rate Limiting:** `POST /login` dibatasi 10 request/menit (`throttle:10,1`); `POST /lupa-password` dan `POST /reset-password` masing-masing 5 request/menit (`throttle:5,1`).

---

## 4. Kontrak Route / Link Utama

| Kebutuhan | URL | Role Diizinkan | Fungsi |
| :--- | :--- | :--- | :--- |
| Link Undangan / Pemanggilan | `/undangan` | `peserta` | Melihat surat/detail pemanggilan seleksi & konfirmasi kehadiran (RSVP). |
| Link Jadwal & Detail Wawancara | `/jadwal` | `peserta`, `superadmin`, `operator`, `pewawancara` | Peserta melihat jadwal personal; Pewawancara melihat jadwal miliknya; Superadmin/Operator melihat jadwal keseluruhan. |
| Link Pengumuman Hasil | `/pengumuman` | `peserta`, `superadmin`, `operator` | Peserta melihat status kelulusan personal; Superadmin/Operator mengelola publikasi hasil. |
| Link Upload Administrasi | `/administrasi` | `peserta` (CV), `superadmin`, `operator` (Penyelenggaraan) | Peserta upload CV (PDF); Operator upload daftar hadir, dokumentasi, & laporan penyelenggaraan per kegiatan. |
| Alokasi Pembagian Tugas | `/tugas` | `superadmin`, `operator`, `pewawancara` | Lihat plotting (semua); kelola plotting & buat akun pewawancara hanya `superadmin`/`operator`. |
| Kelola Kegiatan (di `/tugas`) | `POST /tugas/kegiatan`, `DELETE /tugas/kegiatan/{kegiatan}`, `POST /tugas/kegiatan/{kegiatan}/activate` | `superadmin`, `operator` | Tambah kegiatan (otomatis membuat 4 kriteria + 17 pertanyaan default dalam satu transaksi), aktifkan kembali kegiatan nonaktif (menonaktifkan kegiatan lain), dan hapus kegiatan. Penghapusan ditolak hanya bila masih ada data operasional: tugas panitia, peserta seleksi, jadwal wawancara, atau dokumen penyelenggaraan; kriteria & pertanyaan ikut terhapus via `cascadeOnDelete`. |
| Panel Penilaian Wawancara | `/wawancara/{jadwal}` | `pewawancara` (pemilik jadwal), `superadmin` (override) | Input nilai multi-aspek + catatan/rekomendasi; `pewawancara_id` penilaian selalu milik jadwal (tidak tertimpa superadmin). Operator ditolak 403. |
| Panel Operator / Import (URL `/operator/...` dipertahankan) | `/operator/...` | `superadmin`, `operator` | Import Excel Master Mitra, Import Excel Peserta Terpilih, Import/Kelola Jadwal, Rekap Nilai. |
| Export Rekap Nilai | `GET /operator/pengumuman/export-rekap` | `superadmin`, `operator` | Unduh XLSX rekap nilai wawancara & hasil seleksi per kegiatan (12 kolom: `sobat_id`, `email`, `nama_peserta`, `kecamatan`, `status_undangan`, `sudah_upload_cv`, `pewawancara`, `nilai_akhir`, `rekomendasi`, `catatan_wawancara`, `status_hasil`, `catatan_hasil`). Kolom `sobat_id`/`email`/`status_hasil`/`catatan_hasil` membuatnya kompatibel untuk di-impor kembali lewat "Import Hasil Akhir". |

---

## 5. Kontrak Skema Database (MariaDB)

### `users`
- `id`, `name`, `email` (unique), `password`, `role` (`enum: superadmin, operator, pewawancara, peserta`), `sobat_id` (nullable, unique), `no_telp` (nullable), `must_change_password` (boolean, default `false`), `is_active` (boolean, default `true`), `timestamps`

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
- Kolom: `id`, `kegiatan_id` (FK, cascade), `nama_aspek`, `deskripsi` (text nullable — panduan pertanyaan wawancara & rubrik skor per aspek, ditampilkan collapsible pada form penilaian pewawancara), `bobot_persen`, `urutan`, `timestamps`
- Inisialisasi default (4 kriteria + 17 pertanyaan di bawahnya) dilakukan oleh `Kegiatan::seedDefaultKriteriaDanPertanyaan()`, dipanggil baik oleh `DatabaseSeeder` maupun saat `POST /tugas/kegiatan`, sehingga kegiatan baru langsung bisa dinilai.

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
   - Operator upload `.xlsx` berisi **kolom `Email` saja** (SOBAT ID tidak diperlukan; template unduhan hanya berisi kolom `Email`). Kolom `SOBAT ID` pada file lama diabaikan.
   - Email dinormalisasi (trim + lowercase) dan divalidasi formatnya; sistem mencocokkan ke `users.email`, lalu membuat record `peserta_seleksi` pada kegiatan aktif.
   - Baris dengan email kosong/tidak valid atau email yang tidak ditemukan dihitung sebagai dilewati dan dicantumkan di rincian error.
3. **Import Akun Pewawancara (`/tugas` — Kelola Akun Pewawancara):**
   - Superadmin/Operator mengunduh template (kolom `Nama`, `Email`, opsional `No Telp` & `Password`) lalu mengunggah `.xlsx`/`.xls`/`.csv`.
   - Akun baru dibuat dengan `role=pewawancara`; password awal = kolom `Password` atau `email` bila kosong, dengan `must_change_password=true`.
   - Email yang sudah terdaftar sebagai `pewawancara` diperbarui nama/no-telp-nya; email yang dipakai role lain atau duplikat dalam file dilewati dan dicantumkan di rincian error.
4. **Jadwal Wawancara (idempoten):**
   - Import Excel (`POST /jadwal/import`) memakai kunci `(kegiatan_id, peserta_seleksi_id)`: baris baru → `success`, impor ulang → `updated` (tanpa duplikat), peserta yang muncul lebih dari sekali dalam satu file → `skipped` dengan pesan "duplikat peserta dalam file jadwal".
   - Input manual (`POST /jadwal`) memvalidasi `peserta_seleksi_id` harus milik `kegiatan_id` yang dipilih, `pewawancara_id` harus user aktif ber-role `pewawancara`/`superadmin`, dan `jam_selesai > jam_mulai` (format `H:i`). Input ulang untuk pasangan kegiatan+peserta yang sama memperbarui jadwal existing.
5. **Batas & validasi upload:** seluruh endpoint import Excel membatasi ukuran file maksimal **10 MB** (`max:10240`). Upload berkas penyelenggaraan (`POST /operator/administrasi`) hanya menerima `pdf,doc,docx,xls,xlsx,csv,ppt,pptx,jpg,jpeg,png,zip` dan maksimal **20 MB** (`max:20480`), disimpan privat di `storage/app/private/penyelenggaraan/`.
