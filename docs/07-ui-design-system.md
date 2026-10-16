# 07 — UI, Design System, dan Spesifikasi Mockup

Pemilik dokumen: `ui-ux-designer`. Berlaku untuk `mockup/` **dan** `laravel/resources/views`.

## 1. Karakter tampilan
Bersih, hangat, mudah dibaca di warung yang terang/ramai. Tombol besar, angka uang tegas, status jelas. Bukan gaya korporat yang kaku.

## 2. Token warna

| Token | Hex | Pakai untuk |
|---|---|---|
| `brand-700` | `#14523C` | Teks aktif, hover tombol utama |
| `brand-600` | `#1D6B50` | Tombol utama, aksen, logo |
| `brand-100` | `#E3F0EA` | Latar item aktif, badge Lunas |
| `bg` | `#F3F5F4` | Latar halaman |
| `surface` | `#FFFFFF` | Kartu, panel |
| `line` | `#DDE3E0` | Garis/border |
| `ink` | `#17201C` | Teks utama |
| `ink-2` | `#3A4540` | Teks sekunder |
| `muted` | `#5B6661` | Keterangan (kontras ≥ 4.5:1 di putih) |
| `warn-700` / `warn-100` | `#8A5100` / `#FCEFD9` | Belum Lunas, Menunggu |
| `danger-700` / `danger-100` | `#A3342B` / `#F8E3E0` | Dibatalkan, hapus |
| `info-700` / `info-100` | `#1F4E8C` / `#E3ECF8` | Menunggu Konfirmasi, info |

Badge status (selalu teks + warna):
- Menunggu Konfirmasi → info · Belum Lunas → warn · Lunas → brand · Dibatalkan → danger.

Grafik (urutan seri): `#1D6B50`, `#E08A1E`, `#3A6FB0`, `#8B5CF6`; periode pembanding abu `#A8B3AE`.

Di Laravel, daftarkan token sebagai warna tema Tailwind (`@theme` di `resources/css/app.css` untuk Tailwind v4) agar bisa dipakai `bg-brand-600`, dst. Di mockup, definisikan token yang sama lewat konfigurasi Tailwind CDN.

## 3. Tipografi
- Font: **Plus Jakarta Sans** (400/500/600/700/800). Mockup: Google Fonts. Laravel: paket npm `@fontsource/plus-jakarta-sans` (dibundel lokal, tanpa CDN) agar aplikasi tetap rapi saat dijalankan offline di warung.
- Skala: 12 (keterangan) · 14 (tabel, form) · 15 (badan) · 18 (judul kartu) · 24 (judul halaman) · 28–32 (angka KPI).
- Angka uang: `font-variant-numeric: tabular-nums` agar sejajar di tabel.

## 4. Komponen (nama sama di mockup & Blade)

| Komponen | Blade | Catatan |
|---|---|---|
| Tombol | `x-button` (variant: primary, secondary, danger, ghost; size: md, lg) | Tinggi min. 44px |
| Input / select / textarea | `x-input`, `x-select`, `x-textarea` | Selalu dengan `<label>`, pesan error di bawah |
| Kartu | `x-card` | Radius 14px, border `line` |
| Kartu KPI | `x-stat` | Label, nilai, sub-teks, perubahan % (▲/▼ + warna + teks) |
| Badge status | `x-status-badge :status` | Dari Enum `label()` & `color()` |
| Uang | `x-money :value` | `Rp15.000` |
| Modal | `x-modal` | Alpine, fokus terkunci, Esc menutup |
| Toast / flash | `x-flash` | Dari session flash |
| Tabel | `x-table` | Header abu, baris border, scroll horizontal di HP |
| Empty state | `x-empty` | Ikon garis + kalimat + aksi |
| Filter periode | `x-period-filter` | Preset + tanggal kustom |

Ikon: SVG garis (mis. Heroicons outline/Lucide disalin sebagai SVG inline atau paket npm), **tanpa emoji**.

## 5. Layout per bagian

| Bagian | Prioritas perangkat | Layout |
|---|---|---|
| Pelanggan `/` | HP (360–430px) | Header usaha → tab kategori sticky → daftar menu → tombol keranjang melayang (jumlah + total) → bottom sheet keranjang & form |
| Kasir | Tablet landscape (1024–1280px) & laptop | Header tipis + sidebar ikon+label; POS: grid menu kiri, panel pesanan kanan |
| Admin | Laptop (1280–1440px) | Header + sidebar; konten lebar maks. 1280px; di HP sidebar jadi menu atas |

## 6. Spesifikasi mockup statis (Fase 1)

Tujuan: kelompok bisa **mencoba alur lengkap 3 aktor** dari browser/HP sebelum kode Laravel dibuat, dan markup-nya **dipakai ulang** saat membuat Blade.

### Teknologi
- HTML + Tailwind CSS (CDN, `@tailwindcss/browser@4`) + Alpine.js (CDN) + Chart.js (CDN) + Google Fonts.
- Tanpa build step, tanpa framework lain. Semua path **relatif** (agar jalan di GitHub Pages sub-path `/<nama-repo>/`).
- Data dummy di `mockup/assets/js/data.js`; state alur (keranjang, pesanan, status) disimpan di `localStorage` agar perubahan di halaman pelanggan terlihat di halaman kasir **pada browser yang sama** (bisa buka 2 tab). Tombol "Reset data demo" di halaman indeks.

### Struktur folder
```
mockup/
├── index.html                ← hub: daftar semua layar per aktor + tombol reset demo + skenario uji
├── assets/
│   ├── css/mockup.css        ← sedikit CSS tambahan (print, scrollbar)
│   └── js/
│       ├── tailwind-config.js ← token warna & font
│       ├── data.js            ← usaha, kategori, menu, kasir, 30 hari transaksi dummy
│       └── store.js           ← helper localStorage: pesanan, status, format rupiah
├── pelanggan/
│   ├── index.html            ← one-page menu + keranjang + form
│   └── status.html           ← status pesanan (?id=...)
├── kasir/
│   ├── login.html
│   ├── pos.html
│   ├── pesanan-masuk.html
│   ├── pesanan-aktif.html    ← + modal pembayaran (3 metode)
│   ├── riwayat.html
│   ├── detail.html
│   ├── struk.html            ← tampilan cetak 58/80mm
│   └── rekap.html
└── admin/
    ├── dashboard.html        ← semua kartu, grafik, tabel dari 06
    ├── kategori.html
    ├── menu.html
    ├── pengguna.html
    ├── pengaturan.html
    ├── laporan-penjualan.html
    ├── laporan-transaksi.html
    ├── laporan-menu.html
    ├── laporan-pembayaran.html
    ├── laporan-kasir.html
    └── laporan-pembatalan.html
```

### Ketentuan mockup
- Setiap halaman punya bar kecil di atas: "MOCKUP — data contoh" + tautan kembali ke `index.html`.
- Alur yang **harus berfungsi** (klik-able): pesan dari pelanggan → muncul di Pesanan Masuk → terima → Pesanan Aktif → bayar (tunai dengan kembalian / QRIS / transfer) → status pelanggan berubah → muncul di Riwayat & Rekap → struk.
- Dashboard & laporan boleh memakai data dummy statis dari `data.js` (filter periode cukup mengubah tampilan contoh).
- Teks nyata Bahasa Indonesia; tidak ada lorem ipsum.
- Lulus cek: tanpa error di console, tampil benar di 360px / 1024px / 1440px.

### Publikasi
Workflow `.github/workflows/mockup-pages.yml` mem-publish folder `mockup/` ke GitHub Pages setiap ada perubahan di `main`. URL: `https://<username>.github.io/<nama-repo>/`.
GitHub Pages pada repo **private** butuh akun berbayar atau GitHub Student Developer Pack; jika tidak ada, repo dibuat **public** (aman, tidak ada data rahasia di repo).
