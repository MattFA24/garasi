# Sistem Penyewaan Garasi Kendaraan

Aplikasi web penyewaan garasi sepeda, mobil, dan mobil listrik secara harian atau bulanan.
Dibuat untuk Uji Kompetensi Pemrogram (LSP) oleh Matthew Fernando Anggrian.

## Fitur

**Penyewa**
- Mendaftar dengan NIK dan nomor telepon (menunggu verifikasi admin)
- Kendaraan Saya: menambah data kendaraan
- Sewa Garasi Baru: pilih kendaraan dan tanggal mulai, lihat garasi yang tersedia beserta biayanya, upload bukti bayar

**Admin**
- Verifikasi pendaftar (terima atau tolak)
- Buat garasi baru, beberapa sekaligus, dengan kode otomatis (S001, C001, E001)
- Perpanjang sewa dan hitung kekurangan biaya
- Laporan sewa per bulan, per hari, per tipe garasi, dan per penyewa

## Teknologi

Laravel 12, PHP 8.4 atau lebih baru, MySQL, Blade dan Tailwind (Laravel Breeze), Alpine.js, PHPUnit.

## Cara menjalankan

```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Sesuaikan bagian `DB_` di `.env` dengan MySQL di komputer masing-masing, lalu buka http://127.0.0.1:8000.

Akun admin: `admin@garasi.com` / `password`

## Menjalankan test

```bash
php artisan test
```

## File penting

| File | Isi |
|---|---|
| `app/Services/RentalCalculator.php` | Semua rumus tanggal dan biaya sewa |
| `tests/Unit/RentalCalculatorTest.php` | Unit test T01 sampai T16 |
| `app/Http/Controllers/RentalController.php` | Sewa garasi baru dengan transaksi dan `lockForUpdate` |
| `app/Http/Controllers/ExtensionController.php` | Perpanjangan sewa oleh admin |
| `app/Http/Controllers/ReportController.php` | Laporan sewa |
| `app/Http/Middleware/EnsureRole.php`, `EnsureApproved.php` | Hak akses admin dan penyewa |