# 11 — Deploy KasirKita ke Supabase + Vercel

Pemilik dokumen: `backend-dev`. Dokumen ini menjelaskan cara menjalankan KasirKita
secara daring memakai **Supabase** (database PostgreSQL + penyimpanan berkas) dan
**Vercel** (hosting aplikasi).

> **Baca dulu — keterbatasan yang harus disadari kelompok.**
> Vercel tidak mendukung PHP/Laravel secara resmi. Yang dipakai adalah runtime
> komunitas `vercel-php`. Konsekuensinya ada di bagian 6. Untuk demo capstone hal
> ini sudah memadai, tetapi **jalankan juga versi lokal (XAMPP + MySQL)** sebagai
> cadangan saat sidang, karena versi lokal tidak bergantung pada layanan luar.
> Alternatif hosting PHP yang lebih lazim: Railway, Render, Fly.io, atau hosting
> cPanel biasa. Semuanya tetap bisa memakai database Supabase yang sama.

---

## 1. Gambaran arsitektur

| Bagian | Lokal (XAMPP) | Daring (Vercel) |
|---|---|---|
| Aplikasi PHP | `php artisan serve` | Vercel Function (`api/index.php`) |
| Database | MySQL `kasirkita` | Supabase PostgreSQL |
| Sesi, cache, queue | Tabel di MySQL | Tabel di Supabase |
| Foto menu & pengguna | `storage/app/public` | Supabase Storage (bucket `kasirkita`) |
| Aset CSS/JS | `npm run build` | `npm run build` saat deploy |

Kode aplikasinya **sama persis**. Yang berbeda hanya variabel lingkungan (`.env`).

---

## 2. Menyiapkan Supabase

### 2.1 Membuat proyek
1. Daftar di <https://supabase.com> (paket gratis cukup).
2. **New project** → isi nama `kasirkita`, pilih region terdekat
   (**Southeast Asia (Singapore)** untuk pengguna Indonesia).
3. Simpan **Database Password** yang dibuat. Password ini tidak bisa dilihat lagi.

### 2.2 Mengambil data koneksi database
Buka **Project Settings → Database → Connection string → PHP / URI**.

Supabase menyediakan tiga cara sambung. Pilih sesuai kebutuhan:

| Mode | Port | Dipakai untuk | Catatan |
|---|---|---|---|
| Direct connection | 5432 | — | Hanya IPv6, sering gagal dari Vercel |
| **Session pooler** | 5432 | **Migrasi & seeding dari laptop** | Mendukung prepared statement |
| **Transaction pooler** | 6543 | **Aplikasi di Vercel** | Hemat koneksi; wajib `DB_EMULATE_PREPARES=true` |

Catat: `host`, `port`, `database` (biasanya `postgres`), `user`
(berbentuk `postgres.xxxxxxxxxxxx`), dan password proyek.

### 2.3 Membuat bucket penyimpanan foto
1. **Storage → New bucket** → nama `kasirkita` → centang **Public bucket** → Save.
2. **Project Settings → Storage → S3 Connection** → **New access key**.
   Catat **Access key ID** dan **Secret access key** (hanya tampil sekali).
3. Catat juga **Endpoint** dan **Region** yang tertera di halaman itu.

> **Perhatikan host endpoint-nya.** Supabase memakai
> `https://<ref-proyek>.storage.supabase.co/...` — ada kata **`storage`**
> sebelum `supabase.co`. Ini berbeda dari host REST biasa
> (`<ref-proyek>.supabase.co`). Salin apa adanya dari halaman S3 Configuration;
> kalau salah, unggahan foto gagal tanpa pesan yang jelas.

### 2.4 Mematikan Data API (dianjurkan)

KasirKita menyambung ke PostgreSQL secara langsung lewat Laravel dan **tidak
memakai Data API** (PostgREST / `supabase-js`). Bila Data API dibiarkan aktif
tanpa RLS, seluruh tabel — termasuk `users` dan `transactions` — dapat dibaca
dari internet melalui endpoint REST publik.

Matikan saat membuat proyek: hapus centang **Enable Data API** di bagian
Security. Untuk proyek yang sudah terlanjur dibuat:
**Project Settings → API → Data API → nonaktifkan**.

---

## 3. Menyiapkan komputer lokal

Driver PostgreSQL harus aktif di PHP. Pada XAMPP, buka `C:\xampp\php\php.ini`,
hapus tanda `;` di dua baris berikut, lalu simpan:

```ini
extension=pdo_pgsql
extension=pgsql
```

Periksa hasilnya:

```bash
php -m | findstr pgsql
```

Harus muncul `pdo_pgsql` dan `pgsql`.

---

## 4. Mengisi database Supabase

Buat berkas `laravel/.env.supabase` (jangan di-commit) berisi konfigurasi
**session pooler** — port 5432, karena migrasi butuh prepared statement:

```dotenv
APP_NAME=KasirKita
APP_ENV=production
APP_KEY=                     # isi dengan hasil `php artisan key:generate --show`
APP_DEBUG=false
APP_URL=https://<nama-proyek>.vercel.app
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.xxxxxxxxxxxxxxxxxxxx
DB_PASSWORD=<password proyek Supabase>
DB_SSLMODE=require
DB_EMULATE_PREPARES=false
```

Jalankan migrasi dan data contoh (dari folder `laravel/`):

```bash
php artisan migrate:fresh --seed --force --env=supabase
```

Periksa di Supabase **Table Editor** — harus ada tabel `users`, `categories`,
`menus`, `transactions`, `transaction_items`, `activity_logs`, `sessions`,
`cache`, dan `jobs`.

> **Peringatan:** `migrate:fresh` **menghapus seluruh tabel** lebih dulu.
> Setelah aplikasi dipakai sungguhan, gunakan `php artisan migrate --force` saja.

---

## 5. Deploy ke Vercel

### 5.1 Berkas yang sudah disiapkan di repo

| Berkas | Fungsi |
|---|---|
| `laravel/vercel.json` | Mengatur runtime `vercel-php`, perintah build, dan perutean |
| `laravel/api/index.php` | Titik masuk; memindahkan folder `storage/` ke `/tmp` |
| `laravel/.vercelignore` | Mencegah berkas yang tidak perlu ikut terunggah |

### 5.2 Langkah deploy
1. Push repo ke GitHub.
2. Di Vercel: **Add New → Project** → pilih repo `kasirkita`.
3. **Root Directory** → ubah menjadi **`laravel`** (penting — bukan akar repo).
4. Framework Preset: **Other**. Biarkan perintah build mengikuti `vercel.json`.
5. Isi **Environment Variables** (bagian 5.3), lalu **Deploy**.

### 5.3 Environment Variables di Vercel

```dotenv
APP_NAME=KasirKita
APP_ENV=production
APP_KEY=base64:...            # WAJIB — hasil `php artisan key:generate --show`
APP_DEBUG=false
APP_URL=https://<nama-proyek>.vercel.app
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

# Transaction pooler (port 6543) — hemat koneksi untuk serverless
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.xxxxxxxxxxxxxxxxxxxx
DB_PASSWORD=<password proyek Supabase>
DB_SSLMODE=require
DB_EMULATE_PREPARES=true

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr

# Foto menu & pengguna disimpan di Supabase Storage
UPLOAD_DISK=supabase
SUPABASE_S3_ENDPOINT=https://<ref-proyek>.storage.supabase.co/storage/v1/s3
SUPABASE_S3_URL=https://<ref-proyek>.storage.supabase.co/storage/v1/object/public/kasirkita
SUPABASE_S3_REGION=ap-southeast-1
SUPABASE_S3_BUCKET=kasirkita
SUPABASE_S3_ACCESS_KEY_ID=<access key dari Supabase>
SUPABASE_S3_SECRET_ACCESS_KEY=<secret key dari Supabase>
```

`LOG_CHANNEL=stderr` dipakai karena berkas log tidak bertahan di Vercel; catatan
log dibaca lewat **Vercel → Deployment → Runtime Logs**.

### 5.4 Uji setelah deploy
1. Buka `https://<nama-proyek>.vercel.app` → harus diarahkan ke halaman login.
2. Masuk sebagai `ahmad.mgr` / `rahasia123` → dashboard tampil dengan angka.
3. Buat satu transaksi di panel kasir → pastikan tersimpan.
4. Unggah foto menu → pastikan foto muncul (berarti Supabase Storage berfungsi).

---

## 6. Keterbatasan Vercel yang perlu dicatat di laporan

| Hal | Penjelasan | Penanganan di KasirKita |
|---|---|---|
| Sistem berkas baca-saja | Hanya `/tmp` yang bisa ditulis, dan isinya hilang setiap permintaan baru | `api/index.php` memindahkan `storage/` ke `/tmp`; unggahan diarahkan ke Supabase Storage |
| `php artisan` tidak bisa dijalankan di server | Tidak ada akses shell | Migrasi & seeding dijalankan dari laptop (bagian 4) |
| Cold start | Permintaan pertama lebih lambat (± 1–3 detik) | Wajar untuk demo; sebutkan apa adanya di BAB IV |
| Batas koneksi database | Setiap permintaan membuka koneksi baru | Memakai transaction pooler Supabase (port 6543) |
| Runtime tidak resmi | `vercel-php` dikelola komunitas | Versi lokal XAMPP tetap dipelihara sebagai cadangan demo |

---

## 7. Masalah yang sering muncul

| Gejala | Penyebab | Solusi |
|---|---|---|
| `composer: command not found` (build gagal, exit 127) | `vercel.json` memanggil `composer` sendiri | Jangan panggil composer di `installCommand`. Runtime `vercel-php` sudah menjalankan `composer install` otomatis saat membangun fungsi. Isi `installCommand` cukup `npm ci` |
| `could not find driver` | Ekstensi `pdo_pgsql` belum aktif | Lihat bagian 3 |
| `prepared statement "..." already exists` | Memakai port 6543 tanpa emulasi | Set `DB_EMULATE_PREPARES=true` |
| `SSL connection is required` | SSL belum diaktifkan | Set `DB_SSLMODE=require` |
| Halaman 500 tanpa pesan | `APP_KEY` kosong | Isi `APP_KEY` di Environment Variables, lalu redeploy |
| Tampilan tanpa CSS | Aset belum di-build | Pastikan `npm run build` sukses di log build Vercel |
| Foto gagal diunggah | Kunci S3 salah / bucket bukan public | Periksa bagian 2.3 |
| `Connection refused` dari laptop | Memakai direct connection (IPv6) | Pakai session pooler (bagian 2.2) |

---

## 8. Tetap bisa jalan di lokal

Konfigurasi lokal **tidak berubah**. Dengan `.env` yang memakai
`DB_CONNECTION=mysql` dan `UPLOAD_DISK=public`, aplikasi tetap berjalan di XAMPP
seperti biasa:

```bash
cd laravel
php artisan migrate:fresh --seed
npm run build
php artisan serve
```
