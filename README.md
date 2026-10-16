# KasirKita

Sistem informasi kasir (POS sederhana berbasis web) untuk UMKM kuliner — Capstone Project STSI4440, Kelompok B, Universitas Terbuka 2026.

> **Sistem Informasi Kasir untuk Pengelolaan Data Transaksi Penjualan pada UMKM Kuliner**

- **Pelanggan** memesan dari HP tanpa login dan memantau status pesanan.
- **Kasir** mencatat pesanan, mengonfirmasi pesanan pelanggan, dan menandai pembayaran manual (tunai, transfer, QRIS statis).
- **Admin/pemilik** melihat dashboard, laporan penjualan yang detail, dan mencocokkan pembayaran non-tunai.

Tanpa integrasi pihak ketiga dan tanpa biaya langganan.

## Mockup
Mockup statis: `https://<username>.github.io/kasirkita/` *(isi setelah GitHub Pages aktif)* — sumber di folder [`mockup/`](mockup/).

## Struktur repo
| Folder | Isi |
|---|---|
| `AGENTS.md` | Panduan utama proyek & tim agent |
| `docs/` | Ruang lingkup, proses bisnis, kebutuhan, desain data, route, laporan, UI, fase, standar, QA, progres |
| `.claude/agents/` | 8 agent: pm-flow, analis-bisnis, ui-ux-designer, backend-dev, frontend-dev, analis-data, qa-engineer, penulis-dokumen |
| `mockup/` | Mockup HTML statis (GitHub Pages) |
| `laravel/` | Aplikasi Laravel (dibuat di F0-03) |

## Menjalankan aplikasi (lokal)
*(Dilengkapi di F8-04 setelah aplikasi jadi.)*

Prasyarat: PHP & Composer sesuai versi Laravel yang dipakai, Node.js LTS, MySQL/MariaDB (Laragon atau XAMPP).

```bash
cd laravel
composer install
npm install
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate
# buat database "kasirkita" di MySQL, sesuaikan DB_* di .env
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Akun demo: *(diisi di F8-04)*

## Tim
Muhamad Taufik Badiu · Al Munzir · Endang Hermawan · Muhammad Rafly · Azka Mulia Saputra
