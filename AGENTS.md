# AGENTS.md — KasirKita

> File utama proyek. **Semua agent dan semua anggota tim wajib membaca file ini dulu** sebelum mengerjakan apa pun.
> Detail tiap topik ada di folder `docs/`. Peran tiap agent ada di `.claude/agents/`.

---

## 1. Identitas proyek

| | |
|---|---|
| Nama aplikasi | **KasirKita** |
| Judul capstone | Sistem Informasi Kasir untuk Pengelolaan Data Transaksi Penjualan pada UMKM Kuliner |
| Mata kuliah | STSI4440 — Capstone Project, Program Studi Sistem Informasi, Universitas Terbuka |
| Tim | Kelompok B: Muhamad Taufik Badiu, Al Munzir, Endang Hermawan, Muhammad Rafly, Azka Mulia Saputra |
| Target pengguna | UMKM kuliner (warung makan, kedai kopi, rumah makan, usaha jajanan) |
| Jenis aplikasi | **POS (Point of Sale) sederhana berbasis web** + halaman pemesanan mandiri untuk pelanggan |
| Periode proyek | 14 September – 9 November 2026 |

### Satu kalimat produk
KasirKita membantu UMKM kuliner **mencatat pesanan, mengonfirmasi pembayaran manual, dan melihat laporan penjualan yang rapi** — tanpa integrasi pihak ketiga, tanpa biaya langganan, dan cukup dijalankan dari browser.

### Prinsip (urut prioritas)
1. **Sederhana tapi berguna.** Setiap fitur harus menjawab masalah nyata UMKM di latar belakang proposal (pencatatan manual, data hilang, rekap lama). Fitur yang tidak menjawab itu = tidak dibuat.
2. **Tanpa pihak ketiga.** Tidak ada payment gateway, API bank, layanan SMS/WA, atau layanan cloud berbayar. Pembayaran dikonfirmasi manual oleh kasir.
3. **Data benar dulu, tampilan kemudian.** Uang dihitung di server, disimpan sebagai integer rupiah, riwayat transaksi tidak boleh berubah.
4. **Selesai tepat waktu.** Prioritas P1 harus jadi; P2 dikerjakan jika P1 lulus QC; P3 hanya jika ada waktu sisa.
5. **Dokumen dan aplikasi selalu sinkron.** Setiap keputusan yang mengubah ruang lingkup dicatat di `docs/01-ruang-lingkup.md` dan diteruskan ke proposal.

---

## 2. Tiga pengguna (aktor)

| Aktor | Login? | Perangkat utama | Yang dilakukan |
|---|---|---|---|
| **Pelanggan** | Tidak (tamu) | HP | Lihat menu, pesan mandiri, pantau status pesanan, bayar di kasir |
| **Kasir** | Ya | Tablet / laptop | Input pesanan langsung, konfirmasi pesanan pelanggan, terima pembayaran manual, cetak struk, rekap harian |
| **Admin (Pemilik)** | Ya | Laptop / HP | Dashboard, kelola menu & kategori, kelola akun kasir, pengaturan usaha, laporan lengkap, rekonsiliasi non-tunai |

Admin juga bisa memakai semua halaman kasir.

---

## 3. Struktur repository

```
kasirkita/                        ← root repo GitHub
├── AGENTS.md                     ← file ini (utama)
├── CLAUDE.md                     ← hanya mengimpor AGENTS.md (untuk Claude Code)
├── README.md                     ← pengantar repo + cara menjalankan
├── .claude/agents/               ← 8 agent = 8 "anggota tim virtual"
├── .github/workflows/
│   └── mockup-pages.yml          ← publish folder mockup/ ke GitHub Pages
├── docs/                         ← sumber kebenaran desain & proses
│   ├── 01-ruang-lingkup.md
│   ├── 02-proses-bisnis.md
│   ├── 03-kebutuhan.md
│   ├── 04-desain-data.md
│   ├── 05-halaman-dan-route.md
│   ├── 06-dashboard-dan-laporan.md
│   ├── 07-ui-design-system.md
│   ├── 08-fase-pengerjaan.md
│   ├── 09-standar-kode-dan-git.md
│   ├── 10-qa-dan-pengujian.md
│   ├── PROGRESS.md               ← papan progres (diupdate tiap fase)
│   └── laporan/                  ← bahan BAB III–V (dibuat agent penulis-dokumen)
├── mockup/                       ← FASE 1: mockup statis HTML (dibuka di GitHub Pages)
└── laravel/                      ← FASE 0 & 3+: aplikasi Laravel
```

Aturan folder:
- `mockup/` **100% statis** (HTML + CSS + JS, data dummy). Tidak boleh bergantung pada Laravel.
- `laravel/` adalah aplikasi sebenarnya. Semua perintah `php artisan`, `composer`, `npm` dijalankan **di dalam folder `laravel/`**.
- `docs/` dibaca semua agent. Yang boleh mengubah: lihat kolom "Pemilik dokumen" di bagian 6.

---

## 4. Stack teknologi

| Lapisan | Pilihan | Alasan |
|---|---|---|
| Backend | Laravel versi stabil terbaru + PHP sesuai syaratnya | Diminta kelompok, dokumentasi lengkap, cocok untuk CRUD + laporan |
| Database | MySQL / MariaDB (Laragon atau XAMPP); SQLite untuk test | Umum dipakai, gratis |
| Tampilan | Blade + Tailwind CSS (bawaan Vite Laravel) + Alpine.js | Ringan, tanpa SPA, markup mockup bisa langsung dipindah ke Blade |
| Grafik | Chart.js (dipasang lewat npm, dibundel lokal) | Gratis, tidak memanggil layanan luar |
| Auth | Login manual (`Auth::attempt`) dengan username + password | Hanya 2 peran login, tidak perlu starter kit |
| Cetak struk & laporan | Halaman khusus cetak + `window.print()` (bisa simpan PDF dari browser) | Tanpa library PDF / printer driver |
| Export data | CSV bawaan Laravel (`response()->streamDownload`) | Bisa dibuka di Excel, tanpa package |
| Test | Pest atau PHPUnit (ikuti yang terpasang) | Bawaan Laravel |
| Mockup | HTML + Tailwind (CDN) + Alpine.js (CDN) + Chart.js (CDN) | Kelas Tailwind & atribut Alpine sama dengan versi Laravel → tinggal salin |

Konfigurasi wajib: `APP_TIMEZONE=Asia/Jakarta` (atau `'timezone' => 'Asia/Jakarta'` di `config/app.php`), `APP_LOCALE=id`.

---

## 5. Fase pengerjaan (ringkas)

Detail lengkap, tanggal, Definition of Done, dan gate ada di `docs/08-fase-pengerjaan.md`.

| Fase | Nama | Hasil utama | Agent utama |
|---|---|---|---|
| 0 | Persiapan & fondasi repo | Repo, struktur folder, Laravel terinstal & jalan, scope dikunci | `pm-flow` |
| 1 | **Mockup statis** | `mockup/` klik-able untuk 3 aktor, online di GitHub Pages, diuji kelompok | `ui-ux-designer` |
| 2 | Analisis & desain sistem | Proses bisnis, kebutuhan, ERD final, bahan BAB III | `analis-bisnis` |
| 3 | Fondasi aplikasi | Migrasi, model, seeder, login, peran, layout | `backend-dev`, `frontend-dev` |
| 4 | Master data | Kategori, menu, akun kasir, pengaturan usaha | `backend-dev`, `frontend-dev` |
| 5 | Transaksi inti | POS kasir, pesanan pelanggan, konfirmasi, pembayaran, struk | `backend-dev`, `frontend-dev` |
| 6 | Dashboard & laporan | Dashboard detail, 6 laporan, export CSV, cetak | `analis-data` |
| 7 | QA & UAT | Feature test, black box, UAT ke UMKM/kelompok, perbaikan bug | `qa-engineer` |
| 8 | Finalisasi | Dokumen BAB III–V, README, data demo, rilis v1.0, materi presentasi | `penulis-dokumen`, `pm-flow` |

**Gate:** sebuah fase dianggap selesai hanya jika `qa-engineer` menyatakan **LULUS** dan `pm-flow` mencentang fase itu di `docs/PROGRESS.md`.

---

## 6. Tim agent (anggota tim virtual)

Setiap agent punya file sendiri di `.claude/agents/<nama>.md` berisi identitas, tanggung jawab, input, output, dan aturan kerjanya.

| Agent | Peran (seperti orang di tim) | Pemilik dokumen |
|---|---|---|
| `pm-flow` | Project manager: mengatur fase, membagi tugas, menjaga scope & jadwal, memutuskan gate | `08-fase-pengerjaan.md`, `PROGRESS.md`, `01-ruang-lingkup.md` (bersama analis) |
| `analis-bisnis` | Business/system analyst: proses bisnis, aturan bisnis, kebutuhan, use case, ERD | `01`, `02`, `03`, `04` |
| `ui-ux-designer` | Desainer: design system, mockup statis, alur layar | `07-ui-design-system.md`, folder `mockup/` |
| `backend-dev` | Developer backend Laravel: database, model, controller, aturan transaksi, keamanan | `05-halaman-dan-route.md` (bersama frontend), kode `laravel/app`, `database`, `routes` |
| `frontend-dev` | Developer frontend: Blade, Tailwind, Alpine, mengubah mockup jadi halaman nyata | kode `laravel/resources` |
| `analis-data` | Data/report engineer: query dashboard & laporan, export, cetak | `06-dashboard-dan-laporan.md`, kode laporan |
| `qa-engineer` | Quality control: test otomatis, black box, review kode, UAT, keputusan LULUS/GAGAL | `10-qa-dan-pengujian.md` |
| `penulis-dokumen` | Technical writer: menyusun BAB III–V dan README dari kondisi aplikasi nyata | `docs/laporan/`, `README.md` |

### Pemetaan ke anggota kelompok (isi oleh kelompok)

| Agent | Penanggung jawab manusia |
|---|---|
| pm-flow | [nama] |
| analis-bisnis | [nama] |
| ui-ux-designer | [nama] |
| backend-dev | [nama] |
| frontend-dev | [nama] |
| analis-data | [nama] |
| qa-engineer | [nama] |
| penulis-dokumen | [nama] |

Satu orang boleh memegang lebih dari satu agent. Disarankan QA dipegang orang yang **bukan** penulis kode fitur yang diuji.

---

## 7. Cara bekerja dengan agent

### Alur standar setiap tugas
1. **`pm-flow`** membaca `PROGRESS.md`, menentukan tugas berikutnya, menulis brief tugas (apa, siapa, file mana, kriteria selesai).
2. Agent pelaksana (`backend-dev` / `frontend-dev` / dll.) mengerjakan **satu tugas kecil**, lalu menulis laporan serah terima (format di bawah).
3. **`qa-engineer`** menguji & mereview → LULUS atau GAGAL + daftar perbaikan.
4. Jika LULUS: commit/PR di-merge, `pm-flow` mencentang `PROGRESS.md`.
5. Jika ada keputusan baru yang mengubah scope/proses: `analis-bisnis` memperbarui `docs/`, `penulis-dokumen` mencatat dampaknya ke proposal.

### Format laporan serah terima (wajib di akhir setiap tugas)
```
## Serah terima — <judul tugas>
- Fase / ID tugas: F5-03
- Dikerjakan oleh: <agent>
- File diubah: <daftar>
- Cara mengecek: <langkah singkat / perintah>
- Hasil test: <ringkas>
- Catatan / risiko: <jika ada>
- Butuh keputusan: <jika ada, ditujukan ke siapa>
```

### Contoh perintah di Claude Code (dari root repo)
- "Pakai agent pm-flow, cek PROGRESS.md dan beri tahu tugas berikutnya untuk fase 1."
- "Pakai agent ui-ux-designer, kerjakan tugas F1-02 sesuai docs/07."
- "Pakai agent backend-dev, kerjakan F5-03 (simpan pesanan dari kasir)."
- "Pakai agent qa-engineer, review dan uji hasil F5-03."

Catatan: agent tidak bisa memanggil agent lain. Sesi utama (kamu yang mengetik perintah) yang menjalankan agent satu per satu sesuai arahan `pm-flow`.

---

## 8. Aturan yang berlaku untuk SEMUA agent

1. Baca `AGENTS.md` + dokumen `docs/` yang relevan **sebelum** menulis kode atau dokumen.
2. **Jangan membuat fitur di luar `docs/01-ruang-lingkup.md`.** Ide baru → tulis di bagian "Usulan" pada dokumen itu, keputusan oleh `pm-flow` + kelompok.
3. **Tanpa integrasi pihak ketiga** (payment gateway, API bank, WA/SMS gateway, Firebase, layanan AI, dsb.). CDN hanya untuk mockup.
4. Uang selalu **integer rupiah**. Total dihitung ulang di server dari harga di database.
5. Transaksi yang sudah **Lunas tidak boleh diubah atau dihapus**. Data transaksi tidak pernah di-hard-delete.
6. Bahasa: UI dan dokumen **Bahasa Indonesia**; nama class, tabel, kolom, route, variabel **Bahasa Inggris**.
7. Tugas kecil, commit kecil. Satu tugas = satu branch = satu PR (lihat `docs/09`).
8. Jangan menyimpan password, `.env`, atau data pribadi asli ke repo.
9. Jika ragu, **tanya** (tulis di "Butuh keputusan") — jangan menebak hal yang mengubah perilaku bisnis.
10. Jangan mengarang data hasil uji, angka, atau referensi di dokumen laporan.

---

## 9. Definisi "Selesai" untuk aplikasi v1.0

- Semua fitur **P1** di `docs/03-kebutuhan.md` berjalan dan lulus test.
- Tidak ada bug berstatus **Kritis** atau **Tinggi** yang terbuka.
- `php artisan migrate:fresh --seed` menghasilkan aplikasi siap demo (data contoh 30 hari).
- Mockup online di GitHub Pages; README berisi cara instalasi lokal yang sudah dicoba oleh anggota lain.
- Tabel black box & hasil UAT tersedia untuk BAB IV.
- Proposal BAB I.C sudah diperbarui sesuai `docs/01-ruang-lingkup.md`.
