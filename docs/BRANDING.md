# Branding — Portal Rekrutmen Mitra Statistik BPS Kabupaten Jember

> **STATUS: Konsep A (SIAP Jember) telah DIPILIH dan diimplementasikan** (2026-11-17).
> Konsep B dan C dipertahankan sebagai arsip alternatif.

Dokumen ide. Konteks produk: sistem **tertutup** seleksi mitra statistik
untuk kegiatan berbasis **Keputusan Kepala BPS** tahun 2027 — penggunanya internal BPS
(admin, operator, pewawancara) dan calon mitra (peserta). Branding harus terasa
**resmi & tepercaya** (ini lembaga statistik pemerintah), tetapi tetap **manusiawi**
(pesertanya warga Jember yang mau jadi mitra statistik, bukan pegawai).

Prinsip bersama semua konsep:
- **Jangkar warna biru BPS** (identitas kelembagaan, aman untuk kop/surat resmi).
- Kontras aksesibilitas AA (teks di atas warna wajib lolos rasio 4.5:1).
- Nama harus enak **disebut di telepon** dan **singkat di header email** — hindari nama panjang resmi sebagai nama panggilan aplikasi; nama resmi tetap dipakai sebagai subjudul.

---

## Konsep A — `SIAP JEMBER` (rekomendasi)

| Elemen | Rincian |
|---|---|
| Nama | **SIAP** — *Sistem Informasi Administrasi & Penilaian* (Mitra Statistik) |
| Makna ganda | "SIAP" = sistemnya **siap** (tertutup, aman, terverifikasi) dan pesertanya **siap** tugas lapangan |
| Tagline utama | **"Siap Menjadi Mitra Statistik Terbaik"** |
| Tagline alt | "Tepat Memilih, Siap Bertugas" · "Satu Pintu Mitra Statistik BPS Jember" |
| Logo | Sketsa: 3 batang grafik menanjak membentuk huruf tangga, batang terakhir berujung **centang amber** — makna: seleksi bertahap (administrasi → wawancara → hasil) berujung kelulusan. Badge perisai lembut di belakang = sistem tertutup. |
| Palet | Utama `#0A4D8C` (navy BPS) · Sekunder `#1E88E5` (biru aksi/tombol) · Aksen `#FFB300` (amber "hasil/emas") · Latar `#F5F7FA` · Teks `#1F2937` |
| Tipografi | Judul: **Plus Jakarta Sans** (Semibold) · Isi: **Inter** — keduanya gratis, modern, dan Plus Jakarta Sans karya desainer Indonesia (cocok untuk brand pemerintah daerah) |
| Tone bahasa | Ramah-profesional: "Selamat! Anda terpilih sebagai kandidat mitra statistik…" — kalimat aktif, sapaan "Bapak/Ibu", tanpa jargon |

**Kenapa direkomendasikan:** akronim paling mudah diingat semua role (admin sampai peserta),
tidak terkunci ke tahun (cukup ganti subjudul "2027" → "2028"), dan makna gandanya menjual
proses seleksi itu sendiri.

---

## Konsep B — `SIMPATI 2027`

| Elemen | Rincian |
|---|---|
| Nama | **SIMPATI** — *Sistem Informasi Mitra Terpilih* |
| Makna ganda | "simpati" = kesan ramah dan memanusiakan seleksi; cocok menenangkan peserta yang gugup wawancara |
| Tagline utama | **"Dari Data, Untuk Jember"** |
| Tagline alt | "Memilih Mitra, Membangun Data" |
| Logo | Sketsa: sidik jari yang dibentuk dari **titik-titik data**, satu titik beriak amber = setiap mitra adalah manusia unik yang tercatat akurat. Alternatif: tangan salaman dari garis grafik. |
| Palet | Utama `#0A4D8C` (navy BPS) · Sekunder `#2E7D46` (hijau tembakau Jember — sentuhan lokal) · Aksen `#FFB300` · Latar `#FAFAF7` (krem hangat) |
| Tipografi | Judul: **Plus Jakarta Sans** · Isi: **Inter** |
| Tone bahasa | Hangat dan mendukung: "Jangan khawatir, wawancara Anda bersama tim yang ramah" |

**Kapan dipilih:** jika ingin nuansa khas Jember (hijau tembakau, krem) dan kesan
paling manusiawi. Risiko: kurang cocok bila ingin tampil sangat formal-instansi.

---

## Konsep C — `SIMREK 27` (konservatif instansi)

| Elemen | Rincian |
|---|---|
| Nama | **SIMREK** — *Sistem Rekrutmen* BPS Kabupaten Jember |
| Tagline | "Seleksi Mitra Statistik yang Akuntabel dan Transparan" |
| Logo | Perisai segi lima (sistem tertutup) berisi monogram **"J27"** + garis tren naik; satu warna penuh agar aman untuk kop hitam-putih |
| Palet | Utama `#0A3D7C` · Netral `#37474F` · Aksen tipis `#1E88E5` — tanpa warna cerah |
| Tipografi | **Source Sans Pro** (sudah default AdminLTE — berarti nol usaha ganti font) |
| Tone bahasa | Resmi-birokratis: "Berdasarkan hasil seleksi, ditetapkan peserta berikut…" |

**Kapan dipilih:** jika aturan kelembagaan menghendaki nama literal dan tampilan
sedingin mungkin. Paling mudah disetujui pimpinan, paling mudah dilupakan peserta.

---

## Penerapan praktis (apa pun konsep terpilih)

1. **`.env`** → `APP_NAME="SIAP Jember"` (terpakai otomatis di `<title>` semua halaman via `config('app.name')`).
2. **Sidebar brand** (`layouts/app.blade.php`) → logo SVG 32px + "SIAP **Jember**".
3. **Halaman login** → kartu dengan logo, tagline satu baris, dan catatan "Portal tertutup — akses hanya untuk peserta & panitia terdaftar" (memperkuat positioning sistem tertutup).
4. **Email** (undangan, reset password) → header biru navy + logo + footer "BPS Kabupaten Jember · bpsjember.com".
5. **Badge status peserta** → palet konsisten: `lulus` hijau, `cadangan` amber, `belum_ditentukan` abu, `tidak_lulus` merah bata.
6. **Dokumen undangan/PDF** — opsi motif batik Jember (tembakau) sebagai ornamen tipis di header, hanya jika Konsep B atau permintaan khusus.

## Langkah implementasi setelah arah dipilih

- Ganti `APP_NAME` + sidebar + login (≈ setengah hari kerja).
- Buat aset logo final (SVG + ekspor PNG 512/192/favicon) di `public/img/brand/`.
- (Opsional) ganti font AdminLTE ke Plus Jakarta Sans via CDN di kedua layout.
- Catat pilihan branding di dokumen ini (ubah status "ide" → "dipilih") sebelum dieksekusi.
