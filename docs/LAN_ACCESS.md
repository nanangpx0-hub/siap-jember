# Akses Aplikasi via LAN / MikroTik

Panduan agar aplikasi SIAP Jember bisa dibuka dari komputer lain dalam jaringan lokal (MikroTik).

## Kondisi saat ini (terverifikasi 2026-11-18)

| Item | Nilai |
| :--- | :--- |
| IP LAN komputer server | `192.168.10.254` (Ethernet, `/24`) |
| Server web | PHP Laravel listen `0.0.0.0:80` → sudah terbuka untuk LAN |
| Uji lokal `http://192.168.10.254/login` | **HTTP 200** |
| `hosts` lokal | `127.0.0.1 rekrutment.test` (hanya berlaku di komputer server) |
| `APP_URL` | `http://rekrutment.test` |
| Windows Firewall | Aktif, **belum ada aturan inbound port 80** |

---

## Langkah 1 — Izinkan port 80 di Windows Firewall (wajib)

Saya belum bisa melakukan ini karena PowerShell berjalan tanpa hak Administrator.

1. Buka folder `scripts/`.
2. **Klik kanan** `allow-lan-access.ps1` → **Run with PowerShell**.
3. Setujui prompt UAC. Skrip akan otomatis menaikkan haknya dan membuat aturan inbound TCP 80 yang hanya diizinkan dari subnet `192.168.10.0/24`.

Alternatif manual (PowerShell sebagai Administrator):

```powershell
New-NetFirewallRule -DisplayName 'SIAP Jember - HTTP LAN (port 80)' `
  -Direction Inbound -Protocol TCP -LocalPort 80 `
  -RemoteAddress '192.168.10.0/24' -Action Allow -Profile Any
```

Verifikasi:

```powershell
Get-NetFirewallRule -DisplayName 'SIAP Jember - HTTP LAN (port 80)' | Select-Object Enabled, Action
```

---

## Langkah 2 — Supaya `rekrutment.test` bisa dipakai client LAN (disarankan)

Entry `hosts` di komputer server **tidak** berlaku untuk komputer lain. Tambahkan static DNS di MikroTik:

```routeros
/ip dns static
add name=rekrutment.test address=192.168.10.254 ttl=1d comment="SIAP Jember LAN"
```

Pastikan DHCP Server MikroTik memberikan DNS Server = router (`192.168.10.1`) ke client, lalu client bisa buka:

- `http://rekrutment.test/`

Jika tidak ingin menambah DNS, client cukup buka `http://192.168.10.254/` (langkah 2 boleh dilewati).

### Firewall MikroTik

Secara default forward chain LAN→LAN biasanya `accept`. Jika ada aturan pembatasan, tambahkan (sesuaikan `in-interface`/daftar alamat LAN Anda):

```routeros
/ip firewall filter
add chain=forward action=accept protocol=tcp dst-address=192.168.10.254 dst-port=80 \
    in-interface=bridge comment="SIAP Jember HTTP LAN" place-before=0
```

---

## Langkah 3 — Pastikan server tetap berjalan

Aplikasi saat ini dijalankan dengan Laravel development server yang sudah listen di semua interface:

```powershell
php artisan serve --host=0.0.0.0 --port=80
```

Jangan memakai `--host=127.0.0.1` (kalau begitu LAN tidak bisa mengakses).

---

## Langkah 4 — Akses

| Dari | URL |
| :--- | :--- |
| Komputer server | `http://rekrutment.test/` atau `http://192.168.10.254/` |
| Komputer LAN (dengan DNS MikroTik) | `http://rekrutment.test/` |
| Komputer LAN (tanpa DNS) | `http://192.168.10.254/` |

---

## Catatan penting

1. **IP server sebaiknya statis.** Di MikroTik, buat DHCP lease statis untuk MAC Ethernet komputer server ke `192.168.10.254`, supaya IP tidak berubah. Jika IP berubah, update aturan firewall, static DNS, dan `APP_URL`.
2. **Link reset password** memakai `APP_URL` (`http://rekrutment.test`). Jika client LAN memakai IP dan tidak ada static DNS, email reset akan mengarah ke `rekrutment.test` yang tidak bisa dibuka. Solusinya: lakukan Langkah 2, atau ubah `APP_URL` di `.env` menjadi `http://192.168.10.254` lalu `php artisan config:clear`.
3. **Session/cookie** diikat ke hostname. Buka URL yang sama terus (jangan bolak-balik `rekrutment.test` dan IP) agar tidak perlu login ulang.
4. `php artisan serve` adalah **development server** (single-threaded). Untuk pemakaian banyak user bersamaan, sebaiknya pakai web server sungguhan (Apache/nginx dari FlyEnv atau IIS) dengan Document Root ke folder `public/`. Konfigurasi produksi ada di `docs/DEPLOY_CPANEL.md`.
5. Akses tetap tertutup: hanya akun yang sudah terdaftar yang bisa login; tidak ada registrasi publik.
