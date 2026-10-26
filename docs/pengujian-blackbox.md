# Pengujian Black Box — KasirKita

Dokumen ini mengisi **Tabel 3.6 Skenario pengujian** pada BAB III laporan kemajuan.
Kolom *Hasil aktual* dan *Status* diisi dari pelaksanaan nyata pada aplikasi, bukan
perkiraan. Bukti tangkapan layar ada di `docs/bukti-pengujian/`.

---

## 1. Keterangan pelaksanaan

| | |
|---|---|
| Tanggal pelaksanaan | 25 Oktober 2026, 20:30–20:36 WIB |
| Metode | Black box — masukan & keluaran lewat antarmuka, tanpa meninjau kode |
| Aplikasi yang diuji | KasirKita (Laravel 12.70.0) di `http://127.0.0.1:8000` |
| Lingkungan | Windows 11, XAMPP — PHP 8.2.12, MariaDB 10.4.32 |
| Peran penguji | Manajer (`ahmad.mgr`); panel kasir dapat diakses peran ini |
| Basis data | `kasirkita`, berisi data contoh 30 hari (509 transaksi sebelum pengujian) |
| Transaksi uji | **KSRK-1025-023** — Meja 7, 2× Nasi Goreng Ayam + 1× Es Teh Manis |

**Data uji yang dipakai**

| Menu | Harga satuan | Jumlah | Subtotal |
|---|---:|---:|---:|
| Air Mineral | Rp5.000 | 3 | Rp15.000 |
| Ayam Geprek | Rp30.000 | 1 | Rp30.000 |
| Bakso Urat Jumbo | Rp28.000 | 1 | Rp28.000 |
| **Total keranjang uji (U-01…U-07)** | | **5 porsi** | **Rp73.000** |
| Nasi Goreng Ayam | Rp25.000 | 2 | Rp50.000 |
| Es Teh Manis | Rp8.000 | 1 | Rp8.000 |
| **Total transaksi tersimpan (U-08…U-14)** | | **3 porsi** | **Rp58.000** |

**Angka dashboard sebelum pengujian** (20:30 WIB), dipakai sebagai pembanding U-13 dan U-14:

| Kartu | Nilai |
|---|---|
| Transaksi Hari Ini | 17 — Rp1.795.000 |
| Transaksi Oktober 2026 | 509 — Rp53.716.000 |
| Transaksi Belum Bayar | 12 — Rp1.206.000 |
| Menu Tersedia | 21 (1 habis) |

---

## 2. Tabel 3.6 Skenario pengujian (terisi)

| ID | Fungsi | Skenario dan masukan | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|---|
| U-01 | Pencatatan pesanan | Mengisi satu item dengan jumlah valid — klik menu **Air Mineral** (Rp5.000) | Item tampil pada daftar pesanan | Daftar Pesanan berisi 1 baris: "Air Mineral — Rp5.000 / porsi — qty 1 — Rp5.000" | **Sesuai** |
| U-02 | Pencatatan pesanan | Mengisi beberapa item berbeda — tambah **Ayam Geprek** dan **Bakso Urat Jumbo** | Seluruh item tampil pada daftar pesanan | Daftar Pesanan berisi 3 baris: Air Mineral, Ayam Geprek, Bakso Urat Jumbo | **Sesuai** |
| U-03 | Validasi input | Menyimpan pesanan tanpa item | Sistem menolak dan menampilkan pesan | UI: tombol "Simpan & Proses" **nonaktif** saat keranjang kosong. Server (kiriman langsung tanpa `items`): **HTTP 422** — *"Pesanan masih kosong. Pilih minimal satu menu."* | **Sesuai** |
| U-04 | Validasi input | Jumlah item bernilai nol atau negatif | Sistem menolak masukan | UI: tombol "−" **nonaktif** saat qty = 1, jumlah tidak bisa turun ke 0. Server: `qty=0` → **422** *"Jumlah setiap menu minimal 1."*; `qty=-5` → **422** pesan sama | **Sesuai** |
| U-05 | Validasi input | Jumlah berisi karakter non-angka | Sistem menolak masukan | Server: `qty=abc` → **422** *"Jumlah harus berupa angka bulat."*; `qty` kosong → **422** *"Jumlah wajib diisi."* UI tidak menyediakan kolom ketik untuk jumlah (hanya tombol −/+), sehingga masukan non-angka tidak mungkin dari antarmuka | **Sesuai** |
| U-06 | Hitung total | Satu item, harga dan jumlah diketahui — Air Mineral Rp5.000 × 3 | Total sama dengan harga dikali jumlah | Subtotal baris **Rp15.000**, Total Transaksi **Rp15.000**. Hitungan manual: 5.000 × 3 = 15.000 | **Sesuai** |
| U-07 | Hitung total | Beberapa item — Rp15.000 + Rp30.000 + Rp28.000 | Total sama dengan jumlah seluruh subtotal | Total Transaksi **Rp73.000**, keterangan "5 porsi dalam 3 baris menu". Hitungan manual: 15.000 + 30.000 + 28.000 = 73.000 | **Sesuai** |
| U-08 | Simpan data | Menyimpan pesanan valid — Meja 7, 2× Nasi Goreng Ayam + 1× Es Teh Manis, status **Belum Bayar** | Transaksi dan rincian item tersimpan dengan status belum lunas | Tersimpan sebagai **KSRK-1025-023**, Meja 07, Rp58.000, status **BELUM BAYAR**. Pesan sistem: *"Transaksi KSRK-1025-023 tersimpan dengan status BELUM BAYAR."* Jumlah transaksi hari ini 22 → 23 | **Sesuai** (lihat Catatan 1) |
| U-09 | Simpan data | Memeriksa data tersimpan — buka halaman Detail Transaksi | Total dan rincian sesuai pesanan | Rincian Menu: Nasi Goreng Ayam (MNU-001) 2 × Rp25.000 = Rp50.000; Es Teh Manis (MNU-011) 1 × Rp8.000 = Rp8.000; **Total Pesanan Rp58.000**. Waktu Lunas masih "—" | **Sesuai** |
| U-10 | Status lunas | Menandai transaksi belum lunas sebagai lunas — uang dibayar Rp100.000 | Status berubah menjadi lunas | Status berubah menjadi **LUNAS**. Pesan: *"Transaksi KSRK-1025-023 ditandai LUNAS. Kembalian: Rp42.000."* Hitungan manual kembalian: 100.000 − 58.000 = 42.000. Tab Belum Bayar 6 → 5, Lunas 17 → 18 | **Sesuai** |
| U-11 | Status lunas | Memeriksa transaksi setelah dilunasi | Rincian item dan total tidak berubah | Rincian tetap identik (2 × Rp25.000 = Rp50.000; 1 × Rp8.000 = Rp8.000), **Total Pesanan tetap Rp58.000**. Yang bertambah hanya Waktu Lunas (25 Okt 2026 20:35), Uang dibayar Rp100.000, dan Kembalian Rp42.000 | **Sesuai** |
| U-12 | Dashboard | Membuka dashboard setelah beberapa transaksi tersimpan | Ringkasan sesuai hitungan manual | Keempat kartu dibandingkan dengan perhitungan ulang langsung di basis data (query `SELECT COUNT/SUM` terpisah). Semua **cocok persis** — rinciannya pada bagian 3 | **Sesuai** |
| U-13 | Dashboard | Menambah transaksi lalu membuka kembali dashboard | Angka ringkasan bertambah sesuai transaksi baru | Setelah KSRK-1025-023 (Rp58.000, belum bayar) tersimpan: Transaksi Belum Bayar **12 → 13** dan **Rp1.206.000 → Rp1.264.000** (selisih tepat Rp58.000). Kartu "Transaksi Hari Ini" tetap 17 / Rp1.795.000 — benar, karena kartu itu hanya menghitung transaksi lunas | **Sesuai** |
| U-14 | Dashboard | Mengubah transaksi menjadi lunas | Pembagian lunas dan belum lunas diperbarui | Setelah KSRK-1025-023 dilunasi: Transaksi Belum Bayar **13 → 12** (Rp1.264.000 → Rp1.206.000) dan Transaksi Hari Ini **17 → 18** (Rp1.795.000 → Rp1.853.000). Perpindahan Rp58.000 dari kelompok belum lunas ke kelompok lunas terlihat utuh | **Sesuai** |

**Rekapitulasi: 14 skenario diuji — 14 Sesuai, 0 Tidak Sesuai.**

---

## 3. Rincian pembuktian U-12

Angka pada dashboard dibandingkan dengan perhitungan ulang yang dilakukan langsung
di basis data, bukan dengan angka dari aplikasi itu sendiri.

| Kartu dashboard | Ditampilkan aplikasi | Hitung ulang di basis data | Cocok |
|---|---|---|---|
| Transaksi Hari Ini | 18 — Rp1.853.000 | 18 — Rp1.853.000 | Ya |
| Transaksi Oktober 2026 | 510 — Rp53.774.000 | 510 — Rp53.774.000 | Ya |
| Transaksi Belum Bayar | 12 — Rp1.206.000 | 12 — Rp1.206.000 | Ya |
| Menu Tersedia | 21 (1 habis) | 21 (1 habis) | Ya |

Baris terakhir tabel grafik harian ("25 Okt") juga menampilkan **18 transaksi —
Rp1.853.000**, sama dengan kartu "Transaksi Hari Ini". Kesamaan ini penting karena
sebelum perbaikan tanggal 25 Oktober 2026, kartu ringkasan dan grafik memakai dasar
tanggal yang berbeda sehingga bisa menampilkan dua angka berbeda di satu layar.

---

## 4. Catatan atas hasil pengujian

**Catatan 1 — status awal transaksi (U-08).**
Rancangan pada BAB III menyatakan "status awal setiap transaksi adalah belum lunas".
Pada aplikasi, kasir **memilih** status saat menyimpan ("Belum Bayar" atau "Sudah
Bayar"), dengan pilihan bawaan "Sudah Bayar" karena mayoritas pembayaran di warung
terjadi saat itu juga. U-08 dijalankan dengan memilih "Belum Bayar" dan hasilnya
sesuai. Penyimpangan ini **sudah tercatat** pada Tabel 3.5 sebagai *"Penandaan lunas
— Tidak Sesuai (Disesuaikan)"* dan bukan temuan baru. Bila kelompok ingin rancangan
dan aplikasi benar-benar seragam, ada dua pilihan: (a) ubah kalimat rancangan menjadi
"status awal ditentukan kasir", atau (b) ubah aplikasi agar selalu menyimpan sebagai
belum lunas. Pilihan (a) lebih sesuai kebutuhan nyata UMKM.

**Catatan 2 — cara menguji U-04 dan U-05.**
Antarmuka tidak menyediakan kolom ketik untuk jumlah item; jumlah hanya diubah lewat
tombol "−" dan "+" yang dibatasi 1–99. Karena itu masukan nol, negatif, dan non-angka
tidak dapat dibuat dari layar. Agar skenario tetap teruji sungguhan, masukan tersebut
dikirim langsung ke alamat penyimpanan transaksi. Keduanya ditolak server dengan
HTTP 422 dan pesan berbahasa Indonesia. Pengujian ini tetap black box: yang diperiksa
hanya masukan dan keluaran.

**Catatan 3 — ruang lingkup.**
Ruang lingkup pada BAB I menyatakan *"Sistem tidak menyediakan fitur filter pada
laporan atau rekapitulasi penjualan"*, namun halaman Daftar Transaksi milik manajer
memiliki filter status, periode, kasir, dan pencarian. Ketidaksesuaian ini **di luar
Tabel 3.6** sehingga tidak memengaruhi status U-01…U-14, tetapi perlu diselaraskan:
hapus filternya, atau perbarui ruang lingkup di proposal.

---

## 5. Pengujian otomatis sebagai pelengkap

Selain black box di atas, aplikasi memiliki rangkaian pengujian otomatis
(Pest/PHPUnit) yang dijalankan pada tanggal yang sama:

```
php artisan test
Tests:  92 passed (220 assertions)
```

Cakupannya mencakup autentikasi dan hak akses per peran, validasi master data,
perhitungan total di sisi server (termasuk penolakan harga palsu dari browser),
aturan transisi status pembayaran, serta kebenaran angka dashboard. Pengujian
otomatis ini **bukan pengganti** Tabel 3.6, melainkan jaring pengaman agar fungsi
yang sudah lulus tidak rusak diam-diam saat kode diubah.

---

## 6. Bukti pengujian

| Berkas | Membuktikan |
|---|---|
| `bukti-pengujian/U-01-02-06-07-keranjang-dan-total.jpg` | Daftar pesanan 3 baris dan Total Transaksi Rp73.000 |
| `bukti-pengujian/U-08-tersimpan-belum-bayar.jpg` | KSRK-1025-023 tersimpan, status BELUM BAYAR |
| `bukti-pengujian/U-09-rincian-tersimpan.jpg` | Rincian menu dan total Rp58.000 pada data tersimpan |
| `bukti-pengujian/U-10-ditandai-lunas-kembalian.jpg` | Status LUNAS dan kembalian Rp42.000 |
| `bukti-pengujian/U-11-rincian-setelah-lunas.jpg` | Rincian dan total tidak berubah setelah pelunasan |

---

## 7. Yang belum diuji

Agar dokumen ini jujur, berikut hal yang **belum** dilaksanakan dan tidak boleh
dinyatakan hasilnya di BAB IV sebelum benar-benar dikerjakan:

- **Evaluasi kemudahan penggunaan** (Tabel 3.7 baris terakhir) — memerlukan umpan
  balik pengguna nyata lewat demonstrasi atau diskusi dengan pelaku UMKM kuliner.
- **User Acceptance Test (UAT)** — kuesioner dan tugas skenario ke responden,
  dijadwalkan pada fase 7.
- **Pengujian di basis data PostgreSQL (Supabase)** — seluruh angka di atas diperoleh
  dari MariaDB lokal. Aplikasi sudah disiapkan untuk keduanya, tetapi pengujian di
  lingkungan daring belum dijalankan.
