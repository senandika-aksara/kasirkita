# 08 — Fase Pengerjaan (dari 0 sampai jadi)

Pemilik dokumen: `pm-flow`. Tanggal mengacu kalender 2026; batas akhir proyek **Senin, 9 November 2026**. Hari ini rencana dibuat: Jumat, 16 Oktober 2026 → tersisa ±3,5 minggu, jadi beberapa fase berjalan **paralel**.

Setiap tugas punya ID `F<fase>-<nomor>`, pemilik (agent), dan prioritas. Setiap fase ditutup oleh **Gate**: daftar periksa yang harus dinyatakan LULUS oleh `qa-engineer` dan dicentang `pm-flow` di `PROGRESS.md`.

## Ringkasan jadwal

| Fase | Tanggal | Paralel dengan |
|---|---|---|
| F0 Persiapan & fondasi repo | Jum 16 – Sab 17 Okt | – |
| F1 Mockup statis | Sab 17 – Sel 20 Okt | F2 |
| F2 Analisis & desain sistem | Sab 17 – Rab 21 Okt | F1 |
| F3 Fondasi aplikasi | Rab 21 – Jum 23 Okt | – |
| F4 Master data | Jum 23 – Min 25 Okt | – |
| F5 Transaksi inti | Sen 26 – Sab 31 Okt | F6 (mulai Kam 29) |
| F6 Dashboard & laporan | Kam 29 Okt – Sel 3 Nov | F5 akhir |
| F7 QA, UAT & perbaikan | Sel 3 – Jum 6 Nov | F8 awal |
| F8 Finalisasi & rilis | Kam 5 – Sen 9 Nov | – |

Jika terlambat: potong P3 dulu, lalu P2 (urut dari bawah tabel prioritas `01`). P1 tidak boleh dipotong.

---

## F0 — Persiapan & fondasi repo
Pemilik: `pm-flow` (dibantu `backend-dev`)

| ID | Tugas | Pemilik |
|---|---|---|
| F0-01 | Buat repo GitHub `kasirkita`, undang 5 anggota, aktifkan branch protection `main` (wajib PR) | pm-flow |
| F0-02 | Salin paket dokumen ini (AGENTS.md, CLAUDE.md, docs/, .claude/, .github/) ke root repo | pm-flow |
| F0-03 | Dari root repo jalankan `composer create-project laravel/laravel:^12.0 laravel` (versi dipatok ke **Laravel 12.x** karena PHP terpasang 8.2.12; lihat Keputusan 16 Okt di `PROGRESS.md`). Folder `laravel/` belum ada; perintah ini yang membuatnya. Hapus `.git` di dalam `laravel/` jika ikut terbentuk, agar hanya ada satu repo | backend-dev |
| F0-04 | Atur `.env.example`: `APP_NAME=KasirKita`, `APP_TIMEZONE=Asia/Jakarta`, `APP_LOCALE=id`, `DB_CONNECTION=mysql`, `DB_DATABASE=kasirkita`; set timezone/locale di `config/app.php` jika versi Laravel tidak membaca variabel tsb. | backend-dev |
| F0-05 | Pastikan `npm install && npm run build` dan `php artisan serve` jalan; halaman welcome tampil | backend-dev |
| F0-06 | Buat folder `mockup/` dengan `index.html` sementara; aktifkan GitHub Pages (Settings → Pages → Source: GitHub Actions); pastikan workflow sukses | pm-flow |
| F0-07 | Isi tabel "Pemetaan ke anggota kelompok" di AGENTS.md | pm-flow |
| F0-08 | Rapat singkat kelompok: setujui `01-ruang-lingkup.md` (isi kolom Disetujui di log perubahan) | pm-flow |

**Gate F0:** repo bisa di-clone anggota lain; Laravel jalan di minimal 2 laptop berbeda; Pages menampilkan `mockup/index.html`; ruang lingkup disetujui.

---

## F1 — Mockup statis
Pemilik: `ui-ux-designer`. Spesifikasi: `07-ui-design-system.md` bagian 6.

| ID | Tugas | Prioritas |
|---|---|---|
| F1-01 | Kerangka mockup: `index.html` hub, `tailwind-config.js`, `data.js`, `store.js`, bar "MOCKUP" | P1 |
| F1-02 | Halaman pelanggan: one-page menu + keranjang + form + status | P1 |
| F1-03 | Kasir: login, POS, pesanan masuk, pesanan aktif + modal bayar 3 metode | P1 |
| F1-04 | Kasir: riwayat, detail, struk, rekap | P1 |
| F1-05 | Admin: dashboard lengkap (semua bagian `06` §2) dengan Chart.js | P1 |
| F1-06 | Admin: kategori, menu, pengguna, pengaturan | P1 |
| F1-07 | Admin: 6 halaman laporan + tampilan cetak | P1 |
| F1-08 | Uji responsif 360/1024/1440 & bebas error console | P1 |
| F1-09 | **Sesi uji coba kelompok** (Sel 20 Okt): tiap anggota menjalankan skenario di `index.html`, catat masukan di `docs/PROGRESS.md` bagian "Masukan mockup" | P1 |
| F1-10 | Revisi mockup dari masukan; kunci versi `mockup-v1` (git tag) | P1 |

**Gate F1:** alur pesanan→bayar→laporan bisa dicoba di GitHub Pages; semua masukan berlabel "wajib" sudah ditangani; kelompok setuju desain dikunci. Setelah gate ini, perubahan tampilan besar harus lewat `pm-flow`.

---

## F2 — Analisis & desain sistem (paralel F1)
Pemilik: `analis-bisnis`; dokumen laporan: `penulis-dokumen`.

| ID | Tugas |
|---|---|
| F2-01 | Finalisasi `02-proses-bisnis.md` (alur, status, BR) berdasarkan masukan mockup |
| F2-02 | Finalisasi `03-kebutuhan.md` (KF, KNF, AC) |
| F2-03 | Finalisasi `04-desain-data.md` (ERD + kamus data) dan review bersama `backend-dev` |
| F2-04 | Draf BAB III di `docs/laporan/bab3.md` (metode Waterfall, analisis sistem berjalan vs usulan, use case, activity, ERD, rancangan antarmuka dari screenshot mockup, rencana pengujian, jadwal) |
| F2-05 | Draf revisi BAB I.C sesuai `01-ruang-lingkup.md` §5 |

**Gate F2:** ERD & kamus data disetujui `backend-dev`; setiap fitur P1 punya AC; BAB III draf lengkap (boleh masih ada `[GAMBAR]`).

---

## F3 — Fondasi aplikasi
Pemilik: `backend-dev` + `frontend-dev`

| ID | Tugas | Pemilik |
|---|---|---|
| F3-01 | Enum (OrderStatus, PaymentMethod, OrderType, OrderSource, UserRole) | backend |
| F3-02 | Migrasi semua tabel sesuai `04` + index | backend |
| F3-03 | Model, relasi, cast, scope (`available()`, `active()`, `paid()`, `inPeriod()`) | backend |
| F3-04 | Factory semua model + `DatabaseSeeder` minimal + `DemoSeeder` 30 hari | backend |
| F3-05 | Login/logout manual, middleware `role` & `active`, rate limiter, redirect per peran | backend |
| F3-06 | Tailwind tema (token `07`), font, Alpine & Chart.js via npm | frontend |
| F3-07 | Layout: `layouts/public`, `layouts/cashier`, `layouts/admin`, `layouts/print`; komponen dasar (`x-button`, `x-input`, `x-card`, `x-money`, `x-status-badge`, `x-flash`, `x-modal`) | frontend |
| F3-08 | Halaman login dari mockup | frontend |
| F3-09 | Test: login, akses peran, akun nonaktif | qa |

**Gate F3:** `migrate:fresh --seed` sukses; login 3 akun demo; kasir tidak bisa buka `/admin`; test F3 hijau.

---

## F4 — Master data
| ID | Tugas | Prioritas |
|---|---|---|
| F4-01 | CRUD kategori (+ urutan, larangan hapus jika ada menu) | P1 |
| F4-02 | CRUD menu (+ tersedia/habis, arsip, larangan hapus jika pernah terjual, cari & filter) | P1 |
| F4-03 | Pengaturan usaha (`store_settings`) + helper `current()` | P2 (minimal nama & jam buka: P1) |
| F4-04 | Kelola akun kasir | P2 |
| F4-05 | Upload gambar QRIS statis & foto menu (`storage:link`) | P2/P3 |
| F4-06 | Test master data | P1 |

**Gate F4:** semua AC KF-13, KF-14 (dan KF-16, KF-17 jika P2 diambil) lulus.

---

## F5 — Transaksi inti
| ID | Tugas | Prioritas |
|---|---|---|
| F5-01 | `OrderService`: buat pesanan (kasir & pelanggan), hitung total, snapshot, kode harian, transaksi DB | P1 |
| F5-02 | Halaman POS (Alpine: keranjang, kategori, catatan, tipe) + simpan / simpan & bayar | P1 |
| F5-03 | Halaman pelanggan one-page + kirim pesanan + aturan jam buka & saklar online + rate limit | P1 |
| F5-04 | Halaman status pelanggan + endpoint JSON + polling 15 dtk | P1 |
| F5-05 | Pesanan masuk: terima/tolak + badge polling | P1 |
| F5-06 | Pesanan aktif + batal (alasan) | P1 |
| F5-07 | `PaymentService` + modal bayar 3 metode, kembalian, lock anti-dobel | P1 |
| F5-08 | Riwayat hari ini + detail transaksi | P1 |
| F5-09 | Struk cetak 58/80mm | P2 |
| F5-10 | Rekap hari ini | P2 |
| F5-11 | Toggle tersedia/habis dari POS | P2 |
| F5-12 | Info bayar (rekening & QRIS statis) di halaman status | P2 |
| F5-13 | Test transaksi (lihat `10` §2) | P1 |

**Gate F5:** skenario end-to-end (pesan pelanggan → terima → bayar tunai/QRIS → status berubah → riwayat) lulus di browser dan test; tidak ada cara membayar 2× atau mengubah pesanan lunas.

---

## F6 — Dashboard & laporan
Pemilik: `analis-data` (+ `frontend-dev` untuk tampilan)

| ID | Tugas | Prioritas |
|---|---|---|
| F6-01 | Value object `Period` + preset + pembanding | P1 |
| F6-02 | `DashboardService` + halaman dashboard lengkap | P1 |
| F6-03 | L1 Laporan Penjualan + cetak | P1 |
| F6-04 | L2 Laporan Transaksi | P1 |
| F6-05 | L3 Laporan Menu Terjual | P1 |
| F6-06 | L4 Pembayaran & Rekonsiliasi | P2 |
| F6-07 | L5 Kinerja Kasir, L6 Pembatalan | P2 |
| F6-08 | Export CSV semua laporan + cetak semua laporan | P2 |
| F6-09 | Insight otomatis dashboard | P2 |
| F6-10 | Test angka dashboard & laporan dengan data hitung-manual | P1 |

**Gate F6:** angka dashboard = laporan = rekap kasir untuk periode yang sama; test angka hijau; tampilan sesuai mockup.

---

## F7 — QA, UAT & perbaikan
Pemilik: `qa-engineer`

| ID | Tugas |
|---|---|
| F7-01 | Jalankan seluruh test otomatis; target semua hijau |
| F7-02 | Lengkapi tabel black box `docs/pengujian-blackbox.md` (semua KF, kasus normal & salah) |
| F7-03 | Review keamanan (checklist `10` §4) |
| F7-04 | UAT: minimal 1 pelaku UMKM kuliner nyata (jika memungkinkan) + 5 anggota/rekan memerankan kasir & pelanggan; kuesioner `docs/kuesioner-uat.md` |
| F7-05 | Catat bug di GitHub Issues (template `10` §5), perbaiki Kritis & Tinggi |
| F7-06 | Uji instalasi bersih mengikuti README di laptop yang belum pernah menjalankan proyek |

**Gate F7:** tidak ada bug Kritis/Tinggi terbuka; black box lengkap; hasil UAT terekap.

---

## F8 — Finalisasi & rilis
Pemilik: `penulis-dokumen` + `pm-flow`

| ID | Tugas |
|---|---|
| F8-01 | BAB IV (`docs/laporan/bab4.md`): implementasi per halaman + screenshot, hasil black box, hasil UAT, pembahasan terhadap tujuan BAB I.B |
| F8-02 | BAB V (`docs/laporan/bab5.md`): kesimpulan per tujuan + rekomendasi (impor mutasi bank, stok sederhana, PWA offline, dll.) |
| F8-03 | Sinkronkan BAB I–III dengan aplikasi final; daftar pustaka; berita acara |
| F8-04 | README final, screenshot, akun demo, cara instal |
| F8-05 | Rilis: tag `v1.0.0`, release notes |
| F8-06 | Materi presentasi + skenario demo 5–7 menit + video demo (opsional) |

**Gate F8 (= proyek selesai):** semua butir "Definisi Selesai" di AGENTS.md §9 terpenuhi.
