# Slipin Coffee — Setup Aplikasi

Aplikasi web (Laravel + MariaDB) yang bisa di-install sebagai PWA (jendela sendiri di desktop, ikon di HP).

## Syarat
- PHP 8.2+ (ekstensi: pdo_mysql, mbstring, xml, ctype, fileinfo, openssl, tokenizer)
- Composer
- MariaDB dengan database `slipin_coffee` yang sudah di-import dari `database/slipin_coffee_schema.sql`

## Langkah

### 1. Buat project Laravel baru
```bash
composer create-project laravel/laravel slipin-coffee
cd slipin-coffee
```
> Pakai `composer create-project` (bukan `laravel new` dengan pilihan MariaDB), supaya migration bawaan
> Laravel tidak ikut jalan ke database kamu dan bentrok dengan tabel `users` yang sudah ada.

### 2. Hapus migration bawaan (tabel sudah dibuat lewat SQL)
```bash
rm database/migrations/*.php
rm -f database/database.sqlite
```
**Jangan jalankan `php artisan migrate`.**

### 3. Salin file aplikasi
Extract zip ini, lalu salin isinya (folder `app`, `public`, `resources`, `routes`) ke dalam project Laravel
dan timpa file yang sama (`app/Models/User.php`, `routes/web.php`).
Hapus juga `resources/views/welcome.blade.php` (opsional).

### 4. Atur `.env`
Salin isi `env.slipin.txt` ke `.env`, lalu sesuaikan password database.
Kalau user database `slipin_app` belum dibuat:
```sql
CREATE USER 'slipin_app'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';
GRANT SELECT, INSERT, UPDATE, DELETE ON slipin_coffee.* TO 'slipin_app'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Siapkan storage (untuk foto nota) dan password owner
```bash
php artisan storage:link
php artisan slipin:set-password owner --role=owner
```
(User `owner` dari seed SQL sudah ada, perintah ini hanya mengisi password-nya.)

### 6. Jalankan
```bash
php artisan serve
```
Buka http://localhost:8000 → login dengan username `owner`.

### 7. Install sebagai aplikasi
Chrome/Edge → menu ⋮ → *Cast, save, and share* → *Install page as app* (Edge: *Apps → Install this site as an app*).
Di HP Android: menu ⋮ → *Install app* / *Add to Home screen*.
> Di server production, situs **wajib HTTPS** supaya opsi install muncul (localhost dikecualikan).

## Alur pemakaian
1. **Buka Sesi Jualan** → isi stok awal polycup, lokasi, modal kembalian.
2. Selama jualan, buka sesi → tekan +/− per menu → **Simpan**. Polycup tambahan/rusak juga bisa diisi.
3. Catat belanja (es batu, susu, dll) lewat **Pengeluaran** (bisa dikaitkan ke sesi, bisa foto nota).
4. **Tutup Sesi** → isi sisa polycup (hitung fisik), uang cash, uang QRIS. Sistem langsung menunjukkan selisih
   polycup dan selisih uang terhadap catatan penjualan.
5. Owner melihat **Laporan** bulanan (sales, cash/QRIS, pengeluaran, profit, menu terlaris, per lokasi) + export CSV.

## Hak akses
| Fitur | Owner | Crew |
|---|---|---|
| Dashboard, sesi, input penjualan, pengeluaran, lihat stok polycup | ✅ | ✅ |
| Laporan profit, master data, user, restock polycup | ✅ | ❌ |
| Hapus sesi/pengeluaran, buka lagi sesi yang sudah ditutup | ✅ | ❌ |

## Catatan
- Tampilan memakai Tailwind lewat CDN, jadi butuh internet saat membuka aplikasi. Bisa dipindah ke build Vite nanti.
- Mode offline saat ini hanya menampilkan halaman "offline". Input offline + sinkron otomatis bisa jadi fase berikutnya.
- Backup rutin: `mysqldump -u root -p slipin_coffee > backup_$(date +%F).sql` (jadwalkan via cron).
- Profit = total sales − seluruh pengeluaran periode itu (basis arus kas).

## Troubleshooting
- **Error "Table ... sessions/cache doesn't exist"** → `SESSION_DRIVER=file` dan `CACHE_STORE=file` belum diset di `.env`, lalu jalankan `php artisan config:clear`.
- **Foto nota tidak muncul** → jalankan `php artisan storage:link`.
- **Tombol install tidak muncul** → pastikan HTTPS (atau localhost) dan buka `/manifest.webmanifest` tidak error.
- **Setelah ubah `.env` tidak berubah** → `php artisan config:clear`.
