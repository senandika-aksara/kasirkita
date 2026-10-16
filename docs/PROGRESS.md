# PROGRESS — KasirKita

Diupdate oleh `pm-flow` setiap kali tugas selesai / gate diputuskan. Tanda: `[ ]` belum · `[~]` dikerjakan · `[x]` selesai (lulus QA).

Status terakhir: **F0 sedang dikerjakan** — F0-03/04/05 **selesai & terverifikasi** di laptop pemilik proyek; F0-01/02/06/07/08 menunggu tindakan manusia · Diperbarui: 16 Okt 2026

## F0 Persiapan & fondasi repo (16–17 Okt)
- [ ] F0-01 Repo & anggota — butuh tindakan manusia (GitHub); `git init` di root belum dijalankan
- [ ] F0-02 Salin paket dokumen — AGENTS.md, CLAUDE.md, docs/, .claude/, .github/ sudah ada di root; tinggal commit pertama + push
- [x] F0-03 Instal Laravel di `laravel/` — **Laravel Framework 12.70.0** terpasang (`composer create-project laravel/laravel laravel "^12.0"`). `composer.json`: `"php": "^8.2"`, `"laravel/framework": "^12.0"`. Tidak ada `laravel/.git`. `laravel/.gitignore` ditambah ignore file SQLite.
- [x] F0-04 `.env.example` & timezone/locale — `.env.example` + `.env` diisi `APP_NAME=KasirKita`, `APP_TIMEZONE=Asia/Jakarta`, `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=en`, `APP_FAKER_LOCALE=id_ID`, blok MySQL di-uncomment ke `DB_DATABASE=kasirkita`. `config/app.php` baris 68 diperbaiki: skeleton Laravel 12 meng-hardcode `'timezone' => 'UTC'` → diubah `env('APP_TIMEZONE', 'Asia/Jakarta')`. Verifikasi tinker: `KasirKita | Asia/Jakarta | id | en | id_ID | mysql | 2026-10-16 15:27:34 WIB`.
- [x] F0-05 Build & serve jalan — `npm install` (87 paket, 0 vulnerability) + `npm run build` (vite 7.3.7) sukses: `public/build/manifest.json`, `app-qZZv816C.css`, `app-DMsN-rLE.js`. `php artisan serve` → **HTTP 200, 52.586 byte**, `<title>KasirKita</title>`, kedua aset build HTTP 200, tanpa error baru di `laravel.log`. Diuji dengan `.env` asli (`SESSION_DRIVER=database`) setelah MariaDB dinyalakan. `php artisan test` 2 passed; `pint --test` passed.
- [x] F0-05b (tambahan) Database MySQL siap — database `kasirkita` dibuat (`utf8mb4` / `utf8mb4_unicode_ci`) dan `php artisan migrate` sukses di MySQL (9 tabel bawaan: users, cache, cache_locks, jobs, job_batches, failed_jobs, migrations, password_reset_tokens, sessions). Session tercatat di tabel `sessions` → jalur DB terbukti hidup. Risiko "migrate ke MySQL belum teruji" ditutup.
- [ ] F0-06 GitHub Pages untuk `mockup/` — `mockup/index.html` sudah ada; aktivasi Pages butuh tindakan manusia
- [ ] F0-07 Pemetaan agent ↔ anggota — butuh keputusan kelompok
- [ ] F0-08 Ruang lingkup disetujui kelompok — butuh rapat kelompok
- [ ] **GATE F0** — tertahan oleh F0-01/02/06/07/08 (tindakan manusia) + syarat "Laravel jalan di minimal 2 laptop berbeda". Sign-off `qa-engineer` belum dijalankan.

## F1 Mockup statis (17–20 Okt)
- [ ] F1-01 Kerangka mockup
- [ ] F1-02 Pelanggan
- [ ] F1-03 Kasir: login, POS, pesanan masuk, pesanan aktif + bayar
- [ ] F1-04 Kasir: riwayat, detail, struk, rekap
- [ ] F1-05 Admin: dashboard
- [ ] F1-06 Admin: master data & pengaturan
- [ ] F1-07 Admin: laporan
- [ ] F1-08 Uji responsif & console
- [ ] F1-09 Sesi uji coba kelompok
- [ ] F1-10 Revisi & tag `mockup-v1`
- [ ] **GATE F1**

## F2 Analisis & desain (17–21 Okt)
- [ ] F2-01 Proses bisnis final
- [ ] F2-02 Kebutuhan final
- [ ] F2-03 ERD & kamus data final
- [ ] F2-04 Draf BAB III
- [ ] F2-05 Draf revisi BAB I.C
- [ ] **GATE F2**

## F3 Fondasi aplikasi (21–23 Okt)
- [ ] F3-01 Enum
- [ ] F3-02 Migrasi
- [ ] F3-03 Model & relasi
- [ ] F3-04 Factory & seeder demo
- [ ] F3-05 Auth & middleware
- [ ] F3-06 Tema Tailwind, Alpine, Chart.js
- [ ] F3-07 Layout & komponen dasar
- [ ] F3-08 Halaman login
- [ ] F3-09 Test auth & akses
- [ ] **GATE F3**

## F4 Master data (23–25 Okt)
- [ ] F4-01 Kategori
- [ ] F4-02 Menu
- [ ] F4-03 Pengaturan usaha
- [ ] F4-04 Akun kasir (P2)
- [ ] F4-05 Upload QRIS & foto (P2/P3)
- [ ] F4-06 Test master data
- [ ] **GATE F4**

## F5 Transaksi inti (26–31 Okt)
- [ ] F5-01 OrderService
- [ ] F5-02 POS
- [ ] F5-03 Halaman pelanggan + kirim pesanan
- [ ] F5-04 Status pelanggan + polling
- [ ] F5-05 Pesanan masuk
- [ ] F5-06 Pesanan aktif + batal
- [ ] F5-07 Pembayaran
- [ ] F5-08 Riwayat & detail
- [ ] F5-09 Struk (P2)
- [ ] F5-10 Rekap hari ini (P2)
- [ ] F5-11 Toggle habis dari POS (P2)
- [ ] F5-12 Info bayar di status (P2)
- [ ] F5-13 Test transaksi
- [ ] **GATE F5**

## F6 Dashboard & laporan (29 Okt – 3 Nov)
- [ ] F6-01 Period
- [ ] F6-02 Dashboard
- [ ] F6-03 L1 Penjualan
- [ ] F6-04 L2 Transaksi
- [ ] F6-05 L3 Menu terjual
- [ ] F6-06 L4 Pembayaran & rekonsiliasi (P2)
- [ ] F6-07 L5 & L6 (P2)
- [ ] F6-08 Export & cetak (P2)
- [ ] F6-09 Insight otomatis (P2)
- [ ] F6-10 Test angka
- [ ] **GATE F6**

## F7 QA & UAT (3–6 Nov)
- [ ] F7-01 Test otomatis hijau
- [ ] F7-02 Black box lengkap
- [ ] F7-03 Review keamanan
- [ ] F7-04 UAT
- [ ] F7-05 Bug Kritis/Tinggi ditutup
- [ ] F7-06 Uji instalasi bersih
- [ ] **GATE F7**

## F8 Finalisasi (5–9 Nov)
- [ ] F8-01 BAB IV
- [ ] F8-02 BAB V
- [ ] F8-03 Sinkron BAB I–III, pustaka, berita acara
- [ ] F8-04 README final
- [ ] F8-05 Rilis v1.0.0
- [ ] F8-06 Presentasi & demo
- [ ] **GATE F8 — PROYEK SELESAI**

---

## Masukan mockup (diisi saat F1-09)
| Dari | Halaman | Masukan | Wajib/Saran | Status |
|---|---|---|---|---|

## Keputusan & catatan
| Tanggal | Keputusan | Oleh |
|---|---|---|
| 16 Okt 2026 | **Pasang Laravel 12.x, bukan 13.x.** Laravel stabil terbaru 13.11.0 mensyaratkan PHP ^8.3; laptop kerja memakai PHP 8.2.12 (XAMPP). Laravel 12 mensyaratkan PHP ^8.2 sehingga cocok tanpa upgrade PHP. Alasan: proyek berakhir 9 Nov 2026, Laravel 12 masih dalam masa dukungan, dan upgrade PHP di 5 laptop anggota menambah risiko di F0. Perintah dipatok versi: `composer create-project laravel/laravel:^12.0 laravel`. Catatan: AGENTS.md §4 menulis "Laravel versi stabil terbaru" → dibaca sebagai "versi stabil terbaru yang kompatibel dengan PHP terpasang". | pm-flow + kelompok |
| 16 Okt 2026 | **Aktifkan ekstensi PHP `zip` dan `intl`** di `C:\xampp\php\php.ini` (uncomment `extension=zip`, `extension=intl`), dengan backup `php.ini` sebelum diubah. `zip` wajib agar Composer mengekstrak paket tanpa fallback lambat; `intl` untuk format angka/tanggal Indonesia. `sqlite3` dibiarkan nonaktif (`pdo_sqlite` sudah aktif; keputusan DB test ditinjau ulang di F3). | pm-flow + kelompok |
| 16 Okt 2026 | Lingkungan kerja terverifikasi di laptop pemilik proyek: PHP 8.2.12 (XAMPP, ZTS, Win x64), Composer 2.8.4, Node v24.13.1, npm 11.8.0, Git 2.38.0.windows.1, MariaDB 10.4.32 (service `mysqld` belum berjalan → wajib start sebelum `php artisan migrate`). | pm-flow |
| 16 Okt 2026 | **Versi Laravel final: 12.70.0** (rilis 13 Okt 2026). `SESSION_DRIVER` dan `CACHE_STORE` **dibiarkan `database`** sesuai default skeleton, bukan diturunkan ke `file` — konsekuensinya MariaDB wajib hidup sebelum membuka aplikasi. Alasan: lebih mirip kondisi nyata dan mencegah kejutan di F5/F6; langkah "nyalakan MySQL dulu" masuk README (F8-04). | pm-flow |
| 16 Okt 2026 | Database `kasirkita` dibuat di MariaDB lokal (`utf8mb4` / `utf8mb4_unicode_ci`) dan migrasi bawaan Laravel dijalankan untuk membuktikan koneksi MySQL berhasil. MariaDB dimatikan kembali setelah verifikasi — **mulai sesi kerja berikutnya, nyalakan MySQL dari XAMPP Control Panel lebih dulu**. | pm-flow |
| 16 Okt 2026 | `laravel/database/database.sqlite` (dibuat otomatis oleh `composer create-project`) dibiarkan ada dan sudah masuk `.gitignore`. Test memakai SQLite `:memory:` sesuai `phpunit.xml` bawaan. | backend-dev |

## Risiko
| Risiko | Dampak | Penanganan |
|---|---|---|
| Waktu < 4 minggu | Fitur P2/P3 tidak selesai | Kerjakan paralel F1/F2 & F5/F6; potong P3 dulu |
| Laravel tidak jalan di laptop anggota | Kerja tertahan | Samakan Laragon/XAMPP & versi PHP di F0 |
| Versi PHP anggota berbeda (ada yang < 8.2 atau ≥ 8.3) | `composer install` gagal / versi Laravel tak seragam | Patok Laravel **12.x** di `composer.json` (`"laravel/framework": "^12.0"`), syarat minimum **PHP 8.2** didokumentasikan di README (F8-04); anggota dengan PHP < 8.2 wajib update XAMPP; `composer.lock` ikut di-commit agar versi identik |
| Ekstensi PHP `zip`/`intl` belum aktif di laptop anggota | Composer lambat/gagal, format angka-tanggal error | Instruksi aktivasi `php.ini` masuk README; verifikasi dengan `php -m` sebelum mulai kerja. **Di laptop pemilik proyek sudah aktif** (backup: `C:\xampp\php\php.ini.bak-kasirkita-20261016`) |
| Service MariaDB (`mysqld`) tidak berjalan | `php artisan migrate` gagal **dan setiap halaman web 500**, karena `SESSION_DRIVER=database` | Start MySQL dari XAMPP Control Panel sebelum kerja; langkah ini ditulis di README. Gejala khas di log: `SQLSTATE[HY000] [2002] ... Connection: mysql ... SQL: select * from sessions` |
| XAMPP tidak mendaftarkan MariaDB sebagai Windows service | Anggota bingung karena `Get-Service` tidak menemukan MySQL; harus lewat Control Panel tiap kali | Dokumentasikan di README: buka `C:\xampp\xampp-control.exe` lalu Start pada baris MySQL. Alternatif baris perintah: `C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone` |
| Angka laporan salah | Inti aplikasi tidak dipercaya | Test angka wajib (F6-10) |
| Proposal tidak sesuai aplikasi | Dinilai tidak konsisten | Revisi BAB I.C di F2-05, sinkron di F8-03 |
