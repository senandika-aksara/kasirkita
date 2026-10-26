# KasirKita

Sistem informasi kasir (POS sederhana berbasis web) untuk UMKM kuliner — Capstone Project STSI4440, Kelompok B, Universitas Terbuka 2026.

> **Sistem Informasi Kasir untuk Pengelolaan Data Transaksi Penjualan pada UMKM Kuliner**

Aplikasi ini membantu UMKM kuliner mencatat pesanan pelanggan, menandai transaksi
sebagai lunas beserta kembaliannya, dan melihat rekapitulasi penjualan lewat
dashboard — tanpa integrasi pihak ketiga dan tanpa biaya langganan.

## Tiga peran pengguna

| Peran | Yang dikerjakan |
|---|---|
| **Kasir** | Mencatat pesanan dari katalog menu, mengisi nomor meja, menandai transaksi Lunas / Belum Bayar, menghitung kembalian, mencetak struk |
| **Manajer / Admin** | Dashboard penjualan, riwayat seluruh transaksi, kelola menu (CRUD), kelola pengguna (CRUD) |
| **Superadmin** | Seluruh akses manajer, ditambah hak mengelola akun superadmin |

Manajer dan superadmin juga dapat memakai panel kasir.

## Fitur yang sudah jalan

**Panel kasir**
- Katalog menu dengan pencarian dan tab kategori; menu berstatus Habis tidak dapat dipilih
- Daftar pesanan dengan pengaturan jumlah, subtotal per baris, dan total berjalan
- Nomor transaksi otomatis berurutan per hari (`KSRK-MMDD-NNN`)
- Status bayar Belum Bayar / Sudah Bayar; kembalian dihitung otomatis
- Daftar transaksi hari ini dengan tab Semua / Belum Bayar / Lunas
- Pelunasan transaksi yang tertunda dan halaman cetak struk

**Panel manajer**
- Dashboard: total transaksi hari ini & bulan ini, transaksi belum bayar, jumlah menu tersedia
- Grafik transaksi & omzet 8 hari terakhir (Chart.js) dan grafik menu terlaris
- Ringkasan aktivitas terbaru pengguna sistem
- Riwayat transaksi dengan filter status, kasir, rentang tanggal, dan pencarian kode
- CRUD menu lengkap dengan unggah foto dan konfirmasi hapus
- CRUD pengguna lengkap dengan foto, level akses, dan status aktif

## Aturan penting

- Semua nilai uang disimpan sebagai **rupiah bulat (integer)**, tidak memakai desimal
- Total transaksi **selalu dihitung ulang di server** dari harga menu di database; nilai dari browser diabaikan
- Item transaksi menyimpan **salinan nama & harga** saat transaksi dibuat, sehingga perubahan harga menu tidak mengubah transaksi lama
- Transaksi yang sudah **Lunas tidak dapat dihapus atau dilunasi ulang**
- Menu yang **sudah pernah terjual tidak dapat dihapus** (ubah statusnya menjadi Habis)
- Pengguna yang sudah memiliki transaksi tidak dapat dihapus (nonaktifkan saja)

## Struktur repo

| Folder | Isi |
|---|---|
| `AGENTS.md` | Panduan utama proyek & tim agent |
| `docs/` | Ruang lingkup, proses bisnis, kebutuhan, desain data, UI, fase, standar kode, QA, progres, panduan deploy |
| `docs new/` | Rancangan antarmuka & struktur tabel yang disepakati kelompok (acuan implementasi) |
| `.claude/agents/` | 8 agent: pm-flow, analis-bisnis, ui-ux-designer, backend-dev, frontend-dev, analis-data, qa-engineer, penulis-dokumen |
| `laravel/` | Aplikasi Laravel |

## Menjalankan aplikasi di komputer sendiri

Prasyarat: **PHP 8.2+**, **Composer**, **Node.js LTS**, dan **MySQL/MariaDB** (XAMPP atau Laragon).

```bash
cd laravel

composer install
npm install

cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

# Buat database bernama "kasirkita" lewat phpMyAdmin,
# lalu sesuaikan DB_* di .env bila perlu.

php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve
```

Buka <http://localhost:8000>.

### Akun demo

Seeder membuat data contoh **30 hari terakhir** (±580 transaksi) dan lima akun berikut.
Password semuanya **`rahasia123`**.

| Username | Nama | Peran | Status |
|---|---|---|---|
| `superadmin` | Taufik Badiu | Superadmin | Aktif |
| `ahmad.mgr` | Ahmad Subagjo | Manajer | Aktif |
| `nurul.ksr` | Nurul Azizah | Kasir | Aktif |
| `budi.ksr` | Budi Santoso | Kasir | Aktif |
| `siti.ksr` | Siti Nurjanah | Kasir | **Non-aktif** (untuk menguji penolakan login) |

> Akun demo ini hanya untuk pengembangan dan demo capstone.
> Ganti seluruh password sebelum dipakai usaha sungguhan.

### Menjalankan pengujian

```bash
cd laravel
php artisan test
```

Saat ini: **92 pengujian lolos (218 asersi)** — autentikasi, hak akses per peran,
alur transaksi kasir, CRUD menu, CRUD pengguna, dan ketepatan angka dashboard.

## Menjalankan secara daring (Supabase + Vercel)

Aplikasi dapat dijalankan memakai **Supabase** (PostgreSQL + penyimpanan berkas)
dan **Vercel** (hosting). Langkah lengkap, variabel lingkungan, serta
keterbatasan yang perlu dicatat di laporan ada di
**[`docs/11-deploy-supabase-vercel.md`](docs/11-deploy-supabase-vercel.md)**.

Versi daring yang sedang berjalan: <https://kasirkita-gamma.vercel.app>

Ringkasnya: kode aplikasinya sama, yang berbeda hanya isi `.env`.

```dotenv
DB_CONNECTION=pgsql     # Supabase PostgreSQL
UPLOAD_DISK=supabase    # foto disimpan di Supabase Storage
```

## Teknologi

| Lapisan | Pilihan |
|---|---|
| Backend | Laravel 12, PHP 8.2 |
| Database | MySQL/MariaDB (lokal) atau PostgreSQL/Supabase (daring) |
| Tampilan | Blade + Tailwind CSS v4 + Alpine.js |
| Grafik | Chart.js (dibundel lokal, tanpa CDN) |
| Font | Plus Jakarta Sans (dibundel lokal, aplikasi tetap rapi saat offline) |
| Pengujian | PHPUnit |

## Tim

Muhamad Taufik Badiu · Al Munzir · Endang Hermawan · Muhammad Rafly · Azka Mulia Saputra
