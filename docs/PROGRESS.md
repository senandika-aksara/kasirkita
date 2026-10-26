# PROGRESS — KasirKita

Diupdate oleh `pm-flow` setiap kali tugas selesai / gate diputuskan. Tanda: `[ ]` belum · `[~]` dikerjakan · `[x]` selesai (lulus QA).

Status terakhir: **APLIKASI LARAVEL v1 TERBANGUN** (22 Okt 2026) — seluruh ruang lingkup pada dokumen **"Alur Kerja Capstone - Kasir Kita"** (folder `docs new/`) sudah diwujudkan menjadi aplikasi Laravel 12 yang berjalan: 3 peran (superadmin / manajer / kasir), panel kasir (katalog menu, daftar pesanan, status bayar, kembalian, daftar transaksi, struk), dan panel manajer (dashboard + grafik, riwayat transaksi + filter, CRUD menu, CRUD pengguna). **92 pengujian otomatis lolos (218 asersi)**; seluruh halaman diverifikasi HTTP 200 untuk tiap peran dan 403 untuk kasir yang mencoba membuka panel manajer. Aplikasi juga disiapkan agar dapat dijalankan daring memakai **Supabase (PostgreSQL + Storage) di Vercel** — lihat `docs/11-deploy-supabase-vercel.md`. **Tugas berikutnya: review `qa-engineer` + UAT kelompok, lalu penyusunan BAB IV.** · Diperbarui: 22 Okt 2026

> **Perubahan ruang lingkup penting (22 Okt 2026).** Implementasi mengikuti dokumen di folder `docs new/` yang disepakati kelompok, yang **lebih sempit** daripada rencana lama di `docs/01`–`docs/07`. Yang **tidak** dibangun: halaman pemesanan mandiri pelanggan, status pesanan via tautan publik, metode pembayaran transfer/QRIS beserta rekonsiliasinya, dan enam modul laporan terpisah. Sebagai gantinya: tiga peran yang semuanya login, pembayaran tunai dengan kembalian, serta rekapitulasi lewat dashboard. Ruang lingkup baru ini **sejalan dengan BAB I.C proposal**, sehingga revisi besar BAB I.C yang dicatat di `docs/01` §5 **tidak lagi diperlukan**. Dokumen `docs/01`–`docs/07` berstatus **arsip rencana awal**; acuan yang berlaku sekarang adalah `docs new/` + `README.md`.

## F0 Persiapan & fondasi repo (16–17 Okt)
- [ ] F0-01 Repo & anggota — **sebagian selesai**: repo `https://github.com/senandika-aksara/kasirkita.git` ada, `git init` sudah dijalankan, branch `main` terhubung ke `origin/main`. **Sisa butuh tindakan manusia:** (a) undang 5 anggota kelompok, (b) aktifkan branch protection `main` (wajib PR + 1 review, sesuai `09` §1). Belum dicentang sampai kedua butir itu dikonfirmasi pemilik repo.
- [x] F0-02 Salin paket dokumen — **selesai & terverifikasi 16 Okt 2026**: AGENTS.md, CLAUDE.md, docs/, .claude/, .github/ ada di root dan sudah masuk commit awal `a62e470 chore: dokumen awal proyek KasirKita`, **sudah di-push ke `origin/main`**. **83 file terlacak.** `.gitignore` terbukti bekerja: `laravel/vendor` & `laravel/node_modules` **0 file terlacak**, `laravel/.env` tidak terlacak, `laravel/composer.lock` **terlacak** (sesuai penanganan risiko "versi PHP/Laravel anggota berbeda"). Catatan: hasil F1-01/F1-02 belum di-commit (lihat Perbaikan terbuka / Keputusan branch+PR).
- [x] F0-03 Instal Laravel di `laravel/` — **Laravel Framework 12.70.0** terpasang (`composer create-project laravel/laravel laravel "^12.0"`). `composer.json`: `"php": "^8.2"`, `"laravel/framework": "^12.0"`. Tidak ada `laravel/.git`. `laravel/.gitignore` ditambah ignore file SQLite.
- [x] F0-04 `.env.example` & timezone/locale — `.env.example` + `.env` diisi `APP_NAME=KasirKita`, `APP_TIMEZONE=Asia/Jakarta`, `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=en`, `APP_FAKER_LOCALE=id_ID`, blok MySQL di-uncomment ke `DB_DATABASE=kasirkita`. `config/app.php` baris 68 diperbaiki: skeleton Laravel 12 meng-hardcode `'timezone' => 'UTC'` → diubah `env('APP_TIMEZONE', 'Asia/Jakarta')`. Verifikasi tinker: `KasirKita | Asia/Jakarta | id | en | id_ID | mysql | 2026-10-16 15:27:34 WIB`.
- [x] F0-05 Build & serve jalan — `npm install` (87 paket, 0 vulnerability) + `npm run build` (vite 7.3.7) sukses: `public/build/manifest.json`, `app-qZZv816C.css`, `app-DMsN-rLE.js`. `php artisan serve` → **HTTP 200, 52.586 byte**, `<title>KasirKita</title>`, kedua aset build HTTP 200, tanpa error baru di `laravel.log`. Diuji dengan `.env` asli (`SESSION_DRIVER=database`) setelah MariaDB dinyalakan. `php artisan test` 2 passed; `pint --test` passed.
- [x] F0-05b (tambahan) Database MySQL siap — database `kasirkita` dibuat (`utf8mb4` / `utf8mb4_unicode_ci`) dan `php artisan migrate` sukses di MySQL (9 tabel bawaan: users, cache, cache_locks, jobs, job_batches, failed_jobs, migrations, password_reset_tokens, sessions). Session tercatat di tabel `sessions` → jalur DB terbukti hidup. Risiko "migrate ke MySQL belum teruji" ditutup.
- [ ] F0-06 GitHub Pages untuk `mockup/` — `mockup/index.html` **bukan placeholder lagi**: sudah diganti hub F1-01 yang berfungsi. **Sisa butuh tindakan manusia:** Settings → Pages → Source: **GitHub Actions**, lalu pastikan workflow `mockup-pages.yml` sukses. Satu-satunya kriteria browser F1-01/F1-02 yang belum terverifikasi (perilaku di sub-path `/kasirkita/`) tertahan di sini; risiko rendah karena `grep` path absolut di `mockup/` **kosong**.
- [ ] F0-07 Pemetaan agent ↔ anggota — butuh keputusan kelompok
- [ ] F0-08 Ruang lingkup disetujui kelompok — butuh rapat kelompok
- [ ] **GATE F0** — tertahan oleh F0-01/02/06/07/08 (tindakan manusia) + syarat "Laravel jalan di minimal 2 laptop berbeda". Sign-off `qa-engineer` belum dijalankan.

## F1 Mockup statis (17–20 Okt)
- [x] F1-01 Kerangka mockup — **LULUS DENGAN CATATAN** (`qa-engineer`, 16 Okt 2026); dicentang `pm-flow` 16 Okt 2026. Pelaksana `ui-ux-designer`. Hasil (5 file): `mockup/index.html` (hub 21 layar + reset demo + panel simulasi, mengganti **total** placeholder F0-06), `mockup/assets/css/mockup.css`, `mockup/assets/js/tailwind-config.js`, `mockup/assets/js/data.js`, `mockup/assets/js/store.js`.
  **Bukti terverifikasi:** (1) Dari 171 pemeriksaan otomatis `qa-engineer`, **168 lulus**; 3 gagal = 1 kesalahan harness (lulus setelah dikoreksi) + 2 = TM-01. Harness Node dijalankan di scratchpad, **tidak ditambahkan ke repo** (tidak ada package baru). (2) Tailwind CDN `@theme` **terbukti terkompilasi**: seluruh **20 token** (15 warna + 5 warna grafik `chart-1..4` & `chart-compare`) menghasilkan hex yang benar — risiko terbesar yang dikhawatirkan QA **tidak terjadi**. (3) Font **Plus Jakarta Sans** benar-benar termuat (`document.fonts.check` bobot 600 & 800 = `true`) dan `font-variant-numeric: tabular-nums` aktif pada elemen uang. (4) Aturan `x-cloak` ada (elemen ber-`x-cloak` `display:none`) → tidak berkedip. (5) Persistensi keranjang setelah **reload nyata**: 2 baris / **Rp38.000** sebelum & sesudah. (6) `grep` path absolut di `mockup/` **kosong** → aman untuk sub-path Pages.
  **Catatan yang tidak memblokir:** TM-01 (BR-11 di `store.js`, **wajib ditutup sebelum QA F1-03**), TM-02..TM-08 → F1-10.
- [x] F1-02 Pelanggan — **LULUS DENGAN CATATAN** (`qa-engineer`, 16 Okt 2026); dicentang `pm-flow` 16 Okt 2026. Pelaksana `ui-ux-designer`. Hasil (2 file + 2 tautan pelanggan diaktifkan di hub): `mockup/pelanggan/index.html`, `mockup/pelanggan/status.html`. Cakupan **PL-01..PL-05 (P1)**; PL-06/PL-07/PL-08 **tidak** dikerjakan (sesuai brief).
  **Bukti terverifikasi (browser nyata, Chrome headless + CDP; skrip di scratchpad, bukan di repo):** (1) **4 halaman × 3 lebar (360/1024/1440) = 12 kombinasi**: `scrollWidth == clientWidth` di semua kombinasi (di 360px tepat **360/360**), **0 elemen meluap, 0 error console, 0 exception, 0 request gagal, 0 respons HTTP ≥ 400**. Halaman: hub, `pelanggan/index.html`, `pelanggan/status.html` dengan `id` valid, dan dengan `id` tidak dikenal. (2) **Target sentuh: 0 elemen interaktif** (`button`/`a`/`input`/`label`/`role=button`) bertinggi < 44px di halaman pelanggan pada 360px → bukti empiris keputusan QA (a). (3) Tab kategori **benar-benar sticky** di 360px: `top` 185px → **0px** setelah scroll 1200px, tetap terlihat. (4) Tombol keranjang melayang berfungsi: setelah 2 klik "Tambah" lewat UI, bar menampilkan "2 item di keranjang **Rp38.000**", `KK.cartTotal() = 38000`, 2 baris keranjang, tinggi bar 73px. (5) Bottom sheet: `role="dialog"` + `aria-modal="true"`, terlihat, scroll halaman **terkunci** (`overflow` html = `hidden`), **Esc menutup**. (6) **KF-03 AC2 diuji 2 tab**: tab A membuka status `pending_confirmation`, tab B memanggil `KK.confirmOrder(235)` → badge tab A berubah "Menunggu Konfirmasi" → "Belum Lunas" dengan latensi **11,7 detik** (diukur dari panggilan sampai DOM berubah), **tanpa reload** (`navigation entries` tetap 1) → di bawah ambang 15 detik. Interval polling terpasang 15.000 ms; usul turunkan ke 10.000 ms dicatat sebagai **TM-09 (F1-10)**.
  **Belum terverifikasi:** hanya perilaku di sub-path GitHub Pages `/kasirkita/` — tertahan oleh **F0-06**. 10 dari 11 kriteria visual yang semula ditandai "belum diverifikasi" oleh `qa-engineer` sekarang **terverifikasi lulus**.
- [ ] F1-03 Kasir: login, POS, pesanan masuk, pesanan aktif + bayar — **TUGAS BERIKUTNYA**. Pelaksana `ui-ux-designer`; target Sab 17 Okt; branch `mockup/F1-03-kasir` + PR (wajib mulai tugas ini, lihat Keputusan 16 Okt tentang branch+PR). **Syarat masuk (urut):** (1) **TM-01 (BR-11) ditutup lebih dulu** di `mockup/assets/js/store.js` — `KK.payOrder` wajib menolak metode `transfer`/`qris` tanpa `received_confirmed === true`, bukan hanya `:disabled` di tombol; (2) perilaku qty 1 (tombol "−" nonaktif, item tidak terhapus) **identik** dengan F1-02 — syarat keputusan QA (b); (3) hasil F1-01/F1-02 sudah di-commit ke `main`. Cakupan: `kasir/login.html`, `pos.html`, `pesanan-masuk.html`, `pesanan-aktif.html` + modal bayar 3 metode (`07` §6; KF-04..KF-08). Di luar tugas ini: riwayat/detail/struk/rekap (F1-04).
- [ ] F1-04 Kasir: riwayat, detail, struk, rekap
- [ ] F1-05 Admin: dashboard
- [ ] F1-06 Admin: master data & pengaturan
- [ ] F1-07 Admin: laporan
- [ ] F1-08 Uji responsif & console — **sebagian sudah dicicil**: 4 halaman × 3 lebar sudah lulus 0 error/0 overflow (lihat F1-02). Sisa: halaman kasir (F1-03/F1-04) & admin (F1-05..F1-07), plus uji di sub-path Pages setelah F0-06
- [ ] F1-09 Sesi uji coba kelompok — **jadwalkan selesai jauh sebelum tengah malam WIB** (mitigasi risiko re-seed, keputusan QA (e))
- [ ] F1-10 Revisi & tag `mockup-v1` — sudah membawa antrean tetap: **TM-02..TM-09** + mitigasi re-seed (pemberitahuan di hub saat re-seed; gabungkan pesanan manual dalam jendela 30 hari alih-alih menimpa)
- [ ] **GATE F1** — butir "alur bisa dicoba di GitHub Pages" **tertunda** sampai F0-06 (aktivasi Pages) selesai; verifikasi F1-01..F1-08 dilakukan lokal lebih dulu (lihat Keputusan 16 Okt)

## FL Pembangunan aplikasi Laravel (22 Okt) — mengikuti `docs new/`

Fase ini menggantikan F3–F6 pada rencana lama karena ruang lingkupnya berubah (lihat catatan di bagian atas).

- [x] FL-01 Enum, migrasi, model — `UserRole`, `UserStatus`, `MenuStatus`, `PaymentStatus`; tabel `users`, `categories`, `menus`, `transactions`, `transaction_items`, `activity_logs`; model + relasi + cast. Uang disimpan **integer rupiah** (bukan `DECIMAL(12,2)` seperti draf tabel di `docs new/`), sesuai AGENTS.md §8.4.
- [x] FL-02 Seeder data contoh — 5 akun (1 superadmin, 1 manajer, 3 kasir termasuk 1 non-aktif), 3 kategori, 22 menu, **±580 transaksi 30 hari terakhir** dengan pola ramai jam makan siang & malam serta akhir pekan lebih padat.
- [x] FL-03 Autentikasi & hak akses — login username+password, middleware `role` & `active`, rate limit 5 percobaan/menit, pengalihan sesuai peran, logout paksa untuk akun yang dinonaktifkan saat sesi berjalan.
- [x] FL-04 Design system — token warna hijau/biru sesuai rancangan `docs new/`, Plus Jakarta Sans & Chart.js **dibundel lokal** (tanpa CDN), layout + 12 komponen Blade.
- [x] FL-05 Panel kasir — POS (pencarian menu, tab kategori, keranjang, nomor meja, status bayar, kembalian), daftar transaksi hari ini dengan tab Semua/Belum Bayar/Lunas, pelunasan transaksi tertunda, halaman cetak struk.
- [x] FL-06 Panel manajer — dashboard (4 kartu KPI, grafik transaksi & omzet 8 hari, grafik menu terlaris, ringkasan aktivitas), riwayat transaksi + filter + pop-up detail, CRUD menu + unggah foto, CRUD pengguna.
- [x] FL-07 Pengujian — **92 test lolos, 218 asersi**: autentikasi (8), hak akses (16), transaksi kasir (14), CRUD menu (13), CRUD pengguna (15), dashboard (7), unit Money & SqlDialect (19).
- [x] FL-08 Dukungan Supabase + Vercel — SQL dibuat portabel MySQL/PostgreSQL (`App\Support\SqlDialect`), disk unggahan dapat dialihkan ke Supabase Storage (`App\Support\Uploads`), `vercel.json` + `api/index.php`, panduan `docs/11-deploy-supabase-vercel.md`.
- [ ] **GATE FL** — menunggu review `qa-engineer` dan UAT kelompok.

### Aturan bisnis yang dikunci oleh test

| Aturan | Diuji di |
|---|---|
| Total dihitung ulang di server; harga dari browser diabaikan | `TransactionTest::test_harga_diambil_dari_database_bukan_dari_browser` |
| Item menyimpan salinan nama & harga (perubahan harga tidak mengubah transaksi lama) | `TransactionTest::test_item_menyimpan_salinan_nama_dan_harga` |
| Uang dibayar kurang dari total ditolak | `TransactionTest::test_uang_kurang_dari_total_ditolak` |
| Menu habis tidak bisa dipesan | `TransactionTest::test_menu_habis_tidak_bisa_dipesan` |
| Transaksi Lunas tidak bisa dilunasi ulang maupun dihapus | `TransactionTest::test_transaksi_lunas_tidak_bisa_*` |
| Nomor transaksi berurutan per hari | `TransactionTest::test_nomor_transaksi_berurutan_per_hari` |
| Menu yang pernah terjual tidak bisa dihapus | `MenuCrudTest::test_menu_yang_sudah_terjual_tidak_boleh_dihapus` |
| Pengguna tidak bisa menonaktifkan/menghapus akun sendiri | `UserCrudTest::test_tidak_bisa_*_akun_sendiri` |
| Manajer tidak bisa membuat/menghapus akun superadmin | `UserCrudTest::test_manajer_tidak_boleh_*` |
| Akun non-aktif tidak bisa login | `LoginTest::test_akun_non_aktif_tidak_bisa_masuk` |
| Omzet hanya dihitung dari transaksi Lunas | `DashboardTest::test_transaksi_belum_bayar_tidak_masuk_omzet` |

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
- [ ] F5-04 Status pelanggan + polling — **catatan dari QA F1-02 (TM-11):** di mockup, `public_id` tidak dikenal dijawab kartu ramah ber-HTTP 200. Di Laravel, `GET /pesanan/{public_id}` yang tidak ada **wajib** mengembalikan **404 sebenarnya**
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
- [ ] F7-02 Black box lengkap — **bahan awal sudah ada:** `qa-engineer` menyusun tabel black box **45 kasus** dari F1-01/F1-02, **belum ditulis ke file**. Tugas F7-02 mencakup menuangkannya ke `docs/pengujian-blackbox.md` (format `10` §3). **Larangan:** tabel tidak boleh memaku angka dashboard (lihat TM-03)
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

## Perbaikan terbuka (daftar temuan yang belum ditutup)

Sumber: review `qa-engineer` atas F1-01 & F1-02 (16 Okt 2026) + verifikasi browser `pm-flow`. Tingkat memakai skala `10` §5. Baris hanya boleh dihapus setelah `qa-engineer` menyatakan temuan itu ditutup.

Catatan penomoran: ID `TM-xx` adalah nomor **pelacakan `pm-flow`** dan **tidak** sama dengan nomor "Temuan" di laporan `qa-engineer`. Temuan proses nomor 9 QA (branch+PR) tidak masuk tabel ini karena bukan cacat produk — sudah diputuskan di "Keputusan & catatan".

| ID | Temuan | Tingkat | Pemilik | Target fase | Status |
|---|---|---|---|---|---|
| TM-01 | **WAJIB SEBELUM QA F1-03.** BR-11 / KF-08 AC3 tidak ditegakkan di `mockup/assets/js/store.js` (±baris 729–795): `KK.payOrder` dengan metode `transfer`/`qris` berhasil **tanpa** konfirmasi "Dana sudah diterima". Reproduksi: `KK.payOrder(id,{method:'transfer'})` → `{ok:true}`, seharusnya ditolak. Perbaikan: wajibkan parameter `received_confirmed === true` untuk `transfer`/`qris` **di `store.js`**, bukan hanya `:disabled` di tombol | **Sedang** | ui-ux-designer | **F1-03 (syarat masuk)** | Terbuka |
| TM-02 | Komentar di `data.js` keliru: pada 60 detik pertama setelah tengah malam ada pesanan ber-`created_at` di masa depan (jam palsu 00:00:30 → pesanan 00:01). Tidak crash | Rendah | ui-ux-designer | F1-10 | Terbuka |
| TM-03 | Jumlah pesanan data contoh **tidak stabil antar tanggal**: 236 pada 16 Okt 2026, 260 pada 18 Okt 2026 (jumlah per hari dibobot akhir pekan). Deterministik dalam satu hari, tidak antar hari. **Konsekuensi: `pm-flow`, `analis-data` (F1-05) dan `penulis-dokumen` dilarang mengutip 236 sebagai angka tetap**; tabel black box tidak boleh memaku angka dashboard | Rendah | ui-ux-designer (kode) · analis-data + penulis-dokumen (kepatuhan kutipan) | F1-10 | Terbuka — larangan kutip **berlaku sekarang** |
| TM-04 | Jam pesanan "hari ini" bisa di luar jam buka (mis. 07:47 padahal buka 08:00) atau menumpuk di menit sama bila dibuka 00:00–00:20. Kosmetik | Rendah | ui-ux-designer | F1-10 | Terbuka |
| TM-05 | Opsi `isHome` di `KK.mountMockupBar` tidak dipakai hub → hub menampilkan tautan "Daftar layar mockup" yang menunjuk ke dirinya sendiri | Rendah | ui-ux-designer | F1-10 | Terbuka |
| TM-06 | Pesan gagal `addToCart` ditaruh di `submitError` yang elemennya **di dalam bottom sheet yang tertutup** → pesan "Jumlah maksimal 99 porsi per menu." tidak pernah terlihat pengguna. Butuh toast / `x-flash` di luar sheet | Rendah | ui-ux-designer | F1-10 | Terbuka |
| TM-07 | `mockup.css` memakai hex literal (`#14523C`, `#DDE3E0`, `#5B6661`), bertentangan dengan `09` §3 "token warna dari tema, bukan hex acak". Saran: pakai `var(--color-brand-700)` dsb. | Rendah | ui-ux-designer | F1-10 | Terbuka |
| TM-08 | Bottom sheet & modal **tanpa focus trap** (Esc sudah berfungsi, `role`/`aria` sudah ada). `07` §4 mensyaratkan "fokus terkunci" untuk `x-modal` | Rendah | ui-ux-designer | F1-10 | Terbuka |
| TM-09 | Usul: turunkan interval polling status pelanggan dari **15.000 ms → 10.000 ms** agar ada marjin terhadap ambang 15 detik (latensi terukur 11,7 dtk sudah lulus, tetapi tanpa marjin) | Rendah (usul) | ui-ux-designer | F1-10 | Terbuka |
| TM-10 | Kosmetik: 2 radio `order_type` berukuran 16px (lulus karena area aktuasi label 44px, lihat keputusan (a)); diminta diseragamkan ke `size-5`/`size-6` | Rendah | ui-ux-designer | F1-10 | Terbuka |
| TM-11 | **Info untuk F5-04.** KF-03 AC5 di mockup dijawab kartu ramah ber-HTTP 200. Di Laravel, `GET /pesanan/{public_id}` yang tidak ada **harus** 404 sebenarnya | Info | backend-dev | F5-04 | Terbuka |
| TM-12 | **Info.** 21 layar di hub tidak memasukkan `/admin/qr-meja` (AD-13/KF-18, **P3**). Konsisten dengan `07` §6 → tidak perlu diperbaiki | Info | — | — | Diterima (bukan cacat) |
| TM-13 | Mitigasi re-seed otomatis saat tanggal berganti (keputusan (e)): (i) tampilkan pemberitahuan di hub saat re-seed terjadi, (ii) gabungkan pesanan buatan manual yang masih dalam jendela 30 hari alih-alih menimpa | Rendah | ui-ux-designer | F1-10 | Terbuka |
| TM-14 | Satu-satunya kriteria browser F1-01/F1-02 yang belum terverifikasi: perilaku di sub-path GitHub Pages `/kasirkita/`. Risiko rendah (`grep` path absolut kosong). Tertahan oleh F0-06 | Rendah | pm-flow (setelah F0-06) | F1-08 | Terbuka — menunggu tindakan manusia |

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
| 16 Okt 2026 | **Verifikasi F1 dilakukan LOKAL dulu, bukan di GitHub Pages.** GitHub Pages belum aktif (F0-06 butuh tindakan manusia di Settings → Pages → Source: GitHub Actions) dan status deploy tidak bisa diperiksa dari lingkungan kerja (`gh` CLI tidak terpasang). Cara verifikasi resmi F1-01..F1-08: (a) `php -S 127.0.0.1:8080 -t mockup` dari root repo → `http://127.0.0.1:8080/`, atau (b) Apache XAMPP → `http://localhost/kasirkita/kasirkita/mockup/`. Konsekuensi: semua path di `mockup/` **wajib relatif** (tanpa `/` di awal) agar nanti tetap jalan di sub-path Pages `/<nama-repo>/`. Butir Gate F1 "bisa dicoba di GitHub Pages" ditandai TERTUNDA dan baru diuji setelah F0-06 selesai; bukan alasan menahan F1-01..F1-08. | pm-flow |
| 16 Okt 2026 | **F1 dimulai mendahului GATE F0.** Alasan: `mockup/` 100% statis (AGENTS.md §3) sehingga tidak bergantung pada F0-01/02/06/07/08 yang semuanya butuh tindakan manusia; menahan F1 akan memakan jatah 3,5 minggu yang tersisa. Risiko yang diterima: jika rapat ruang lingkup (F0-08) mengubah daftar fitur P1, mockup pelanggan mungkin perlu revisi — ditangani di F1-10. | pm-flow |
| 16 Okt 2026 | **F1-01 & F1-02 dicentang `[x]` sekarang** atas dasar keputusan `qa-engineer` **LULUS DENGAN CATATAN** (dua-duanya) + verifikasi browser nyata oleh sesi utama. Alasan: tidak ada temuan **Kritis/Tinggi**; satu-satunya temuan **Sedang** (TM-01/BR-11) **bukan** cakupan F1-01/F1-02 karena modal bayar baru dibuat di F1-03; 168 dari 171 pemeriksaan lulus dan 12/12 kombinasi browser bersih. Konsekuensi: TM-01 menjadi **syarat masuk F1-03**, TM-02..TM-10 & TM-13 masuk antrean F1-10. Sesuai AGENTS.md §7 butir 4, centang dilakukan setelah ada keputusan QA — **bukan** sebelum. | pm-flow |
| 16 Okt 2026 | **Tailwind CDN `@theme` TERBUKTI BERFUNGSI** (risiko terbesar F1-01 tertutup). Seluruh 20 token (15 warna + `chart-1..4` + `chart-compare`) menghasilkan hex benar. **Penjelasan penting agar tidak salah lapor:** `@tailwindcss/browser@4` membangkitkan utility **secara dinamis** lewat `MutationObserver` dan melakukan **tree-shaking per halaman** — variabel token yang tidak dipakai di satu halaman memang **tidak muncul** di `:root` halaman itu. Itu **perilaku normal, bukan bug**. Dugaan awal "3 token tidak cocok" adalah artefak cara ukur dan **sudah dikoreksi**; jangan dikutip sebagai cacat di BAB IV. | pm-flow |
| 16 Okt 2026 | **PERINGATAN KUTIPAN ANGKA: jangan pernah menulis "236 pesanan" (atau angka jumlah pesanan apa pun) sebagai angka tetap** di `docs/`, dashboard mockup, tabel black box, atau BAB III–V. Data contoh `data.js` dibobot akhir pekan sehingga jumlahnya berubah menurut tanggal pembukaan (236 pada 16 Okt 2026, 260 pada 18 Okt 2026); deterministik dalam satu hari saja. Berlaku untuk `pm-flow`, `analis-data` (F1-05), `qa-engineer` (F7-02) dan `penulis-dokumen`. Cara benar: sebut rentang/mekanisme ("±30 hari data contoh, jumlah per hari dibobot akhir pekan") atau ambil angka dari aplikasi Laravel nyata di F6 — selaras AGENTS.md §8 butir 10. Lihat TM-03. | pm-flow |
| 16 Okt 2026 | Keputusan QA **(a)**: checkbox 24px di dalam label 44px **memenuhi** syarat target sentuh — yang diukur adalah **area aktuasi**, bukan kotak visual. Diperkuat bukti empiris: **0 elemen interaktif < 44px** di halaman pelanggan pada 360px. Radio `order_type` 16px lulus dengan alasan sama; penyeragaman ke `size-5`/`size-6` **non-blocking** → TM-10. | qa-engineer |
| 16 Okt 2026 | Keputusan QA **(b)**: pada qty 1, tombol "−" **nonaktif dan tidak menghapus item** — **dapat diterima**. **Syarat: perilaku identik wajib dipakai di POS kasir F1-03** (dan nanti di F5-02), agar kasir dan pelanggan tidak punya dua kebiasaan berbeda. | qa-engineer |
| 16 Okt 2026 | Keputusan QA **(c)**: tautan "Status Pesanan" di hub yang otomatis diisi `?id=` pesanan terbaru adalah **alat bantu hub, bukan fitur baru** (tidak melanggar `01`). **Syarat: jangan diporting ke Blade** — di aplikasi nyata pelanggan membuka `public_id` miliknya sendiri. | qa-engineer |
| 16 Okt 2026 | Keputusan QA **(d)**: `<link rel="icon" href="data:,">` **diterima** karena `favicon.ico` memang 404 di server statis dan memunculkan request gagal palsu saat uji console. **Syarat: di Laravel diganti favicon nyata di `public/`, bukan URL eksternal** (AGENTS.md §8 butir 3). | qa-engineer |
| 16 Okt 2026 | Keputusan QA **(e)**: re-seed otomatis saat tanggal berganti adalah **risiko nyata tetapi kecil** → **dipertahankan dengan mitigasi**: (i) pemberitahuan di hub saat re-seed terjadi, (ii) idealnya gabungkan pesanan buatan manual yang masih dalam jendela 30 hari alih-alih menimpa — keduanya masuk **F1-10, bukan blocker** (TM-13); (iii) `pm-flow` **menjadwalkan sesi F1-09 selesai jauh sebelum tengah malam WIB** (lihat Risiko). | qa-engineer + pm-flow |
| 16 Okt 2026 | **Keputusan atas Temuan 9 QA (proses git): TIDAK membuat branch + PR retroaktif untuk F1-01/F1-02.** Alasan: (1) commit pertama `a62e470` memang sudah langsung di `main` dan **branch protection `main` belum aktif** (F0-01 belum tuntas), jadi PR retroaktif tidak menegakkan apa pun — hanya merapikan riwayat setelah fakta; (2) **substansi** yang diwajibkan `09` §1 (review sebelum merge) **tetap terjadi**: `qa-engineer` mereview dan menyatakan LULUS DENGAN CATATAN, dan jejaknya tercatat di PROGRESS.md; (3) membuat PR bertanggal palsu untuk "bukti proses" di BAB IV akan melanggar AGENTS.md §8 butir 10 (jangan mengarang bukti). **Konsekuensi yang mengikat:** (a) hasil F1-01/F1-02 di-commit langsung ke `main` dengan dua commit terpisah (`mockup(hub): ...` dan `mockup(pelanggan): ...`) sesuai format `09` §1; (b) **mulai F1-03, branch + PR + 1 review WAJIB tanpa kecuali** (`mockup/F1-03-kasir`); (c) penyimpangan ini **dicatat apa adanya** sebagai keterbatasan proses untuk BAB IV/V, tidak disembunyikan; (d) F0-01 (branch protection) naik prioritas agar aturannya ditegakkan alat, bukan niat baik. | pm-flow |
| 16 Okt 2026 | **F0-02 dicentang `[x]`.** Dasar verifikasi: commit awal `a62e470` sudah **di-push ke `origin/main`**, **83 file terlacak**, `.gitignore` terbukti benar (`laravel/vendor` & `laravel/node_modules` 0 file terlacak, `laravel/.env` tidak terlacak, `laravel/composer.lock` terlacak). **F0-01 dan F0-06 tetap `[ ]`** karena sisanya murni tindakan manusia di GitHub (undang 5 anggota, branch protection `main`, aktivasi Pages) dan tidak bisa diverifikasi dari lingkungan kerja (`gh` CLI tidak terpasang). | pm-flow |
| 16 Okt 2026 | Tabel black box **45 kasus** hasil F1-01/F1-02 sudah disusun `qa-engineer` tetapi **belum ditulis ke file**. Keputusan: **tidak** membuat `docs/pengujian-blackbox.md` sekarang (pemiliknya `qa-engineer`, jadwalnya F7-02 per `08`); bahannya dicatat sebagai masukan F7-02 agar tidak hilang. | pm-flow |
| 16 Okt 2026 | Catatan temuan (bukan keputusan): saat brief F1 disusun, root repo **sudah** `git init`, sudah punya remote `origin https://github.com/senandika-aksara/kasirkita.git`, branch `main` bersih, dan commit pertama `a62e470 chore: dokumen awal proyek KasirKita` (16 Okt 16:00 WIB) sudah ada. Artinya F0-01/F0-02 sebagian sudah berjalan, tetapi **checkbox F0 belum diubah** karena sisa butirnya (undang 5 anggota, branch protection `main`) belum bisa diverifikasi dari lingkungan kerja. Perlu dikonfirmasi pemilik repo pada sesi berikutnya sebelum F0-01/02 dicentang. | pm-flow |

## Risiko
| Risiko | Dampak | Penanganan |
|---|---|---|
| Waktu < 4 minggu | Fitur P2/P3 tidak selesai | Kerjakan paralel F1/F2 & F5/F6; potong P3 dulu |
| Laravel tidak jalan di laptop anggota | Kerja tertahan | Samakan Laragon/XAMPP & versi PHP di F0 |
| Versi PHP anggota berbeda (ada yang < 8.2 atau ≥ 8.3) | `composer install` gagal / versi Laravel tak seragam | Patok Laravel **12.x** di `composer.json` (`"laravel/framework": "^12.0"`), syarat minimum **PHP 8.2** didokumentasikan di README (F8-04); anggota dengan PHP < 8.2 wajib update XAMPP; `composer.lock` ikut di-commit agar versi identik |
| Ekstensi PHP `zip`/`intl` belum aktif di laptop anggota | Composer lambat/gagal, format angka-tanggal error | Instruksi aktivasi `php.ini` masuk README; verifikasi dengan `php -m` sebelum mulai kerja. **Di laptop pemilik proyek sudah aktif** (backup: `C:\xampp\php\php.ini.bak-kasirkita-20261016`) |
| Service MariaDB (`mysqld`) tidak berjalan | `php artisan migrate` gagal **dan setiap halaman web 500**, karena `SESSION_DRIVER=database` | Start MySQL dari XAMPP Control Panel sebelum kerja; langkah ini ditulis di README. Gejala khas di log: `SQLSTATE[HY000] [2002] ... Connection: mysql ... SQL: select * from sessions` |
| XAMPP tidak mendaftarkan MariaDB sebagai Windows service | Anggota bingung karena `Get-Service` tidak menemukan MySQL; harus lewat Control Panel tiap kali | Dokumentasikan di README: buka `C:\xampp\xampp-control.exe` lalu Start pada baris MySQL. Alternatif baris perintah: `C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone` |
| GitHub Pages belum aktif (F0-06) | Gate F1 tidak bisa ditutup penuh; kelompok sulit mencoba mockup dari HP masing-masing pada sesi F1-09 | Verifikasi lokal jadi acuan utama (Keputusan 16 Okt). F0-06 harus selesai **sebelum Sel 20 Okt** agar F1-09 bisa dijalankan dari HP anggota; rencana cadangan: sesi F1-09 dilakukan bersama di satu laptop + 1 HP via `php -S 0.0.0.0:8080` di jaringan Wi-Fi yang sama |
| Path absolut di mockup (`/assets/...`) | Mockup jalan lokal tapi rusak di GitHub Pages sub-path `/<nama-repo>/` | Semua path relatif; dicek di F1-08 dengan `grep -rn 'src="/\|href="/' mockup/` → harus kosong |
| **Re-seed data contoh terjadi di tengah sesi F1-09** (pergantian tanggal menghapus/menimpa pesanan buatan peserta) | Peserta kehilangan pesanan yang baru dibuat di tengah skenario; masukan uji jadi kacau dan harus diulang | Jadwalkan **sesi F1-09 selesai jauh sebelum tengah malam WIB** (disarankan mulai ≤ 20.00, selesai ≤ 22.00); mitigasi kode (pemberitahuan di hub + gabungkan pesanan manual dalam jendela 30 hari) masuk **F1-10 / TM-13**; cadangan: tombol "reset demo" di hub dijalankan di awal sesi agar semua peserta mulai dari seed yang sama |
| **TM-01 (BR-11) terbawa ke F5-07** jika hanya ditambal di tombol, bukan di logika | Pembayaran transfer/QRIS bisa ditandai Lunas **tanpa** konfirmasi dana diterima → uang tercatat masuk padahal belum; melanggar BR-11/KF-08 AC3 dan merusak kepercayaan angka laporan (prinsip AGENTS.md §1 butir 3) | Tutup di **`store.js`** sebagai syarat masuk F1-03 (bukan di tombol); di Laravel ditegakkan di **Form Request + `PaymentService`**, bukan di Blade; `10` §2 sudah mewajibkan test "non-tunai tanpa centang ditolak" → wajib ada di F5-13; `qa-engineer` memeriksa ulang temuan ini di gate F1 **dan** gate F5 |
| **Branch protection `main` belum aktif** sehingga `09` §1 (PR + 1 review) tidak ditegakkan alat | Kode masuk `main` tanpa review (sudah terjadi pada F1-01/F1-02); riwayat git jadi bukti proses yang lemah untuk BAB IV | F0-01 naik prioritas (tindakan manusia di GitHub Settings); mulai F1-03 branch + PR **wajib** meski belum dipaksa alat; penyimpangan F1-01/F1-02 dicatat apa adanya (Keputusan 16 Okt tentang TM-09) |
| **Angka data contoh mockup dikutip sebagai fakta** di dokumen/laporan | BAB III–V memuat angka yang tidak bisa direproduksi penguji (236 vs 260 tergantung tanggal) → dinilai mengarang data | Larangan eksplisit di Keputusan 16 Okt + TM-03; angka resmi laporan **hanya** dari aplikasi Laravel nyata (F6-10); `qa-engineer` menolak tabel black box yang memaku angka dashboard |
| Angka laporan salah | Inti aplikasi tidak dipercaya | Test angka wajib (F6-10) |
| Proposal tidak sesuai aplikasi | Dinilai tidak konsisten | Revisi BAB I.C di F2-05, sinkron di F8-03 |
