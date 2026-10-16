# 01 — Ruang Lingkup KasirKita

Pemilik dokumen: `analis-bisnis` + `pm-flow`. Perubahan wajib disetujui kelompok dan dicatat di bagian 6.

## 1. Posisi aplikasi

KasirKita adalah **POS sederhana berbasis web** untuk UMKM kuliner. Bukan POS lengkap seperti aplikasi komersial: tidak mengurus stok, akuntansi, cabang, atau perangkat keras khusus. Fokusnya tiga hal yang sesuai judul proposal:

1. **Pencatatan pesanan** — dari kasir maupun dari pelanggan langsung.
2. **Penandaan status pembayaran** — dikonfirmasi manual oleh kasir, dengan metode bayar tercatat.
3. **Penyajian informasi penjualan** — dashboard dan laporan yang detail untuk pemilik.

Bagian aplikasi:

| Bagian | Pengguna | Bentuk |
|---|---|---|
| Halaman pelanggan (one-page) | Pelanggan | Satu halaman publik mobile-first: profil usaha, menu, keranjang, pesan, status pesanan |
| Panel kasir | Kasir, Admin | POS, pesanan masuk, pesanan aktif, pembayaran, struk, riwayat, rekap harian |
| Panel admin | Admin | Dashboard, master data, akun, pengaturan, laporan |

## 2. Keputusan desain penting

### 2.1 Pelanggan tanpa akun
Pelanggan **tidak perlu daftar/login**. Cukup isi nama dan nomor meja (atau pilih "Bawa pulang"). Alasan: pelanggan warung tidak mau registrasi, data pribadi minimal, beban pengembangan jauh lebih kecil. Pelanggan memantau pesanan lewat tautan status unik yang muncul setelah memesan.

### 2.2 Pesanan pelanggan harus dikonfirmasi kasir
Pesanan dari halaman pelanggan masuk dengan status **Menunggu Konfirmasi**. Kasir menerima (→ Belum Lunas) atau menolak (→ Dibatalkan, wajib alasan). Ini mencegah pesanan iseng/palsu tanpa perlu verifikasi nomor HP atau OTP.

### 2.3 Pembayaran: manual, tercatat, tanpa integrasi
Metode yang dicatat: **Tunai**, **Transfer Bank**, **QRIS (statis)**.

- **Tunai** — kasir mengisi uang diterima, sistem menghitung kembalian.
- **Transfer Bank** — pelanggan transfer ke rekening usaha (info rekening tampil di halaman status/struk), kasir mengecek notifikasi mutasi di HP-nya, lalu menandai lunas. Kolom referensi opsional (mis. 4 digit akhir / nama pengirim).
- **QRIS statis** — memakai QRIS cetak yang sudah dimiliki UMKM dari bank/penyedianya. Gambar QRIS hanya **ditampilkan** (diunggah admin di Pengaturan); aplikasi tidak terhubung ke sistem QRIS apa pun. Kasir memverifikasi manual seperti transfer.

**Rekonsiliasi non-tunai** (tanpa integrasi bank): laporan khusus menampilkan semua transaksi Transfer & QRIS per hari, dan admin mencentang "Sudah cocok" setelah membandingkan dengan mutasi rekening di m-banking. Transaksi yang belum dicentang tampil sebagai peringatan di dashboard.

Impor otomatis file mutasi bank **tidak diambil** untuk v1.0: format CSV/PDF tiap bank berbeda, pencocokan otomatis rawan salah, dan file mutasi berisi data sensitif. Dicatat sebagai saran pengembangan (BAB V).

### 2.4 Menu: tersedia / habis, bukan stok
Tanpa manajemen stok. Admin/kasir cukup menandai menu **Habis** hari itu; menu habis tidak bisa dipesan pelanggan dan tidak bisa dipilih di POS. Ini menjawab kebutuhan "barang habis" tanpa modul inventori.

### 2.5 Variasi lewat catatan, bukan varian
Tidak ada varian/topping berharga beda. Setiap item pesanan punya **catatan** teks bebas (mis. "pedas", "tanpa es"). Menu dengan harga berbeda dibuat sebagai menu terpisah (mis. "Es Teh" dan "Es Teh Jumbo").

## 3. Daftar fitur dan prioritas

- **P1 = wajib** untuk v1.0 dan demo. **P2 = penting**, dikerjakan setelah P1 lulus QC. **P3 = opsional**, hanya jika waktu cukup.

### Pelanggan (tanpa login)
| ID | Fitur | Prioritas |
|---|---|---|
| PL-01 | Melihat profil usaha (nama, alamat, jam buka, status buka/tutup) | P1 |
| PL-02 | Melihat menu per kategori (nama, harga, deskripsi singkat, label Habis) | P1 |
| PL-03 | Keranjang: tambah/kurang item, catatan per item | P1 |
| PL-04 | Kirim pesanan: nama, makan di tempat (no. meja) / bawa pulang, catatan | P1 |
| PL-05 | Halaman status pesanan via tautan unik, diperbarui otomatis | P1 |
| PL-06 | Info cara bayar di halaman status (bayar di kasir, rekening, gambar QRIS statis) | P2 |
| PL-07 | Nomor meja terisi otomatis dari QR meja (`/?meja=5`) | P3 |
| PL-08 | Foto menu | P3 |

### Kasir
| ID | Fitur | Prioritas |
|---|---|---|
| KS-01 | Login / logout | P1 |
| KS-02 | POS: pilih menu per kategori, jumlah, catatan, tipe pesanan, nama/meja, simpan | P1 |
| KS-03 | Pesanan masuk dari pelanggan: terima / tolak (alasan), notifikasi badge | P1 |
| KS-04 | Pesanan aktif (belum lunas): lihat, tandai lunas, batalkan (alasan) | P1 |
| KS-05 | Pembayaran manual 3 metode; tunai dengan kembalian | P1 |
| KS-06 | Riwayat transaksi hari ini + detail transaksi | P1 |
| KS-07 | Cetak struk (halaman cetak browser) | P2 |
| KS-08 | Rekap hari ini milik kasir: jumlah transaksi, omzet per metode, uang tunai yang seharusnya ada | P2 |
| KS-09 | Tandai menu Habis/Tersedia langsung dari POS | P2 |

### Admin (Pemilik)
| ID | Fitur | Prioritas |
|---|---|---|
| AD-01 | Dashboard detail (lihat `06-dashboard-dan-laporan.md`) | P1 |
| AD-02 | Kelola kategori | P1 |
| AD-03 | Kelola menu: tambah, ubah, tersedia/habis, arsip | P1 |
| AD-04 | Laporan Penjualan (ringkasan harian) dengan filter tanggal | P1 |
| AD-05 | Laporan Transaksi (detail) dengan filter | P1 |
| AD-06 | Laporan Menu Terjual | P1 |
| AD-07 | Laporan Metode Pembayaran & Rekonsiliasi non-tunai | P2 |
| AD-08 | Laporan Kinerja Kasir | P2 |
| AD-09 | Laporan Pembatalan | P2 |
| AD-10 | Export CSV & cetak semua laporan | P2 |
| AD-11 | Kelola akun kasir (tambah, nonaktifkan, reset password) | P2 |
| AD-12 | Pengaturan usaha: nama, alamat, telepon, jam buka, info rekening, gambar QRIS statis, catatan kaki struk, buka/tutup pesanan online | P2 |
| AD-13 | Generator QR per meja (halaman cetak) | P3 |

## 4. Di luar ruang lingkup (TIDAK dibuat)

- Payment gateway, QRIS dinamis, API/open banking, e-wallet API, atau kerja sama pihak ketiga apa pun.
- Impor otomatis mutasi bank.
- Manajemen stok, bahan baku, resep, pembelian, alert stok.
- Varian/modifier berharga (ukuran, topping); diganti catatan item.
- Modul dapur / kitchen display / status masak.
- Akun & login pelanggan, poin, member, voucher, diskon, promo.
- Pajak, service charge.
- Pengantaran/ongkir, reservasi meja.
- Multi-cabang, multi-usaha.
- Akuntansi (jurnal, laba-rugi, HPP), penutupan buku/closing kas yang mengunci data.
- Refund / pembatalan transaksi yang sudah lunas.
- Notifikasi WhatsApp, SMS, email, push.
- Integrasi printer thermal (struk dicetak lewat dialog cetak browser).
- Aplikasi mobile native.

## 5. Dampak ke proposal (wajib direvisi)

Ruang lingkup di proposal BAB I.C saat ini **bertentangan** dengan rencana ini di beberapa butir. Revisi yang perlu dilakukan `penulis-dokumen` + kelompok:

| Butir proposal saat ini | Ubah menjadi |
|---|---|
| "Sistem menyediakan fitur bagi kasir untuk menerima dan mencatat pesanan pelanggan" | Pesanan dapat dicatat oleh kasir **dan** dikirim langsung oleh pelanggan melalui halaman pemesanan, lalu dikonfirmasi kasir |
| TIDAK MASUK: "pilihan maupun pencatatan metode pembayaran, termasuk pembayaran digital atau QRIS" | Sistem **mencatat** metode pembayaran (tunai, transfer, QRIS statis) yang dikonfirmasi manual; **tidak** ada integrasi pembayaran digital |
| TIDAK MASUK: "fitur filter pada laporan" | Laporan memiliki filter periode tanggal dan filter sederhana (status, metode, kasir, kategori) |
| TIDAK MASUK: "Informasi penjualan terbatas pada dashboard, tanpa modul laporan tambahan" | Sistem menyediakan dashboard **dan** modul laporan (penjualan, transaksi, menu terjual, metode pembayaran, kinerja kasir, pembatalan) |
| (belum ada) | Tambahkan aktor: Admin/Pemilik, Kasir, Pelanggan |
| Tetap | Tidak ada closing kas, modul dapur, varian produk, stok/inventori |

Juga perbaiki inkonsistensi di Berita Acara: butir "Fitur Manajemen Stok Barang + Alert stok menipis" dan "Pengelolaan stok barang yang lebih efisien" bertentangan dengan ruang lingkup (stok tidak dibuat). Sesuaikan di berita acara pertemuan berikutnya.

## 6. Log perubahan ruang lingkup

| Tanggal | Perubahan | Alasan | Disetujui |
|---|---|---|---|
| 16 Okt 2026 | Tambah aktor Pelanggan (tanpa login) + pesanan mandiri | Permintaan kelompok, aplikasi lebih bermanfaat | [menunggu kelompok] |
| 16 Okt 2026 | Catat metode pembayaran manual + rekonsiliasi non-tunai | Permintaan kelompok; tanpa integrasi pihak ketiga | [menunggu kelompok] |
| 16 Okt 2026 | Modul laporan detail dengan filter | Permintaan kelompok | [menunggu kelompok] |

## 7. Usulan (belum disetujui)

Tulis ide baru di sini dengan format: `- [tanggal] [pengusul] Ide — alasan — perkiraan usaha (S/M/L)`.
