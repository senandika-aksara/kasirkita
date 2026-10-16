# 06 — Dashboard dan Laporan

Pemilik dokumen: `analis-data`. Setiap angka di sini punya definisi tunggal; dashboard, laporan, dan rekap kasir **wajib memakai definisi yang sama** (implementasi di satu tempat: `App\Services\Reports`).

## 1. Definisi dasar (dipakai semua angka)

| Istilah | Definisi |
|---|---|
| Transaksi lunas | `orders.status = 'paid'` |
| Waktu acuan omzet | `payments.paid_at` (Asia/Jakarta) — BR-15 |
| Omzet | `SUM(payments.amount_due)` transaksi lunas dalam periode |
| Jumlah transaksi | `COUNT` transaksi lunas dalam periode |
| Rata-rata transaksi (AOV) | Omzet ÷ jumlah transaksi (dibulatkan ke rupiah) |
| Item terjual | `SUM(order_items.qty)` dari transaksi lunas |
| Pembatalan | Pesanan `cancelled` dengan `cancelled_at` dalam periode; nilai = `SUM(total)` |
| Tingkat pembatalan | Pembatalan ÷ (lunas + pembatalan) dalam periode |
| Periode pembanding | Periode dengan panjang sama tepat sebelumnya (mis. 7 hari → 7 hari sebelumnya) |
| Perubahan % | (nilai sekarang − pembanding) ÷ pembanding × 100; jika pembanding 0 → tampil "—" |
| Uang tunai seharusnya | `SUM(amount_due)` pembayaran `cash` lunas (yang diterima dikurangi kembalian = amount_due) |

Periode preset: **Hari ini**, **Kemarin**, **7 hari terakhir** (termasuk hari ini), **30 hari terakhir**, **Bulan ini**, **Bulan lalu**, **Rentang kustom** (laporan saja, maks. 366 hari).

## 2. Dashboard Admin (`/admin`)

Pilihan periode di atas: Hari ini · 7 hari · 30 hari · Bulan ini (default: Hari ini).

### Baris 1 — Perlu tindakan (selalu real-time, tidak terpengaruh periode)
- Pesanan menunggu konfirmasi (jumlah) → tautan ke Pesanan Masuk.
- Pesanan belum lunas (jumlah + nominal) → tautan ke Pesanan Aktif.
- Pembayaran non-tunai belum dicocokkan > 1 hari (jumlah + nominal) → tautan ke Laporan Pembayaran.
- Menu berstatus Habis (jumlah) → tautan ke Kelola Menu.
Kartu yang bernilai 0 tampil netral ("Semua beres").

### Baris 2 — KPI periode (dengan perubahan % vs periode pembanding)
1. Omzet
2. Jumlah transaksi lunas
3. Rata-rata per transaksi
4. Item terjual
5. Pembatalan (jumlah + nominal + tingkat %)

### Baris 3 — Grafik
| Grafik | Bentuk | Data |
|---|---|---|
| Tren omzet | Batang (periode Hari ini: per jam; lainnya: per hari, hari kosong = 0) + garis tipis periode pembanding | Omzet |
| Jam ramai | Batang per jam (hanya jam buka) | Rata-rata jumlah transaksi per jam dalam periode |
| Metode pembayaran | Batang horizontal (Tunai, Transfer, QRIS) dengan nominal & % | Omzet per metode |
| Penjualan per kategori | Batang horizontal | Omzet per kategori |
| Sumber pesanan | Dua angka + persentase (Kasir vs Pelanggan mandiri) | Jumlah transaksi |

### Baris 4 — Tabel
- **Top 10 menu terlaris**: peringkat, menu, kategori, qty, omzet, % kontribusi.
- **Menu kurang laku**: 5 menu aktif dengan qty terendah (termasuk 0) dalam periode — bahan evaluasi menu untuk pemilik.
- **10 transaksi terbaru**: kode, waktu, pelanggan/meja, total, metode, status.

### Insight otomatis (teks sederhana, P2)
Maks. 3 kalimat yang dibuat dari aturan tetap, mis.:
- "Omzet 7 hari terakhir naik 12% dibanding 7 hari sebelumnya."
- "Jam paling ramai: 12.00–13.00."
- "Es Teh Manis menyumbang 18% item terjual."
Tanpa AI; hanya template + angka dari query.

## 3. Rekap Hari Ini (kasir, `/kasir/rekap`)
- Untuk kasir: hanya transaksi yang ia konfirmasi; untuk admin: pilihan semua kasir / per kasir.
- Isi: jumlah transaksi lunas, omzet per metode, total omzet, **uang tunai seharusnya**, jumlah & nilai pembatalan, pesanan belum lunas yang masih terbuka (peringatan).
- Tombol Cetak.

## 4. Modul laporan (`/admin/laporan/...`)

Fitur umum semua laporan:
- Filter periode (preset + kustom) dan filter khusus per laporan.
- Ringkasan angka di atas tabel, tabel detail, baris total di bawah.
- **Cetak** (halaman cetak A4, judul laporan, nama usaha, periode, dicetak oleh & waktu cetak) — P1 untuk Laporan Penjualan, P2 lainnya.
- **Export CSV** (P2): pemisah `;`, UTF-8 dengan BOM agar Excel Indonesia membaca benar, angka tanpa format titik.
- Filter tersimpan di query string (bisa dibagikan/di-bookmark).

### L1 — Laporan Penjualan (P1)
- Filter: periode.
- Ringkasan: omzet, transaksi, AOV, item terjual, pembatalan.
- Grafik: omzet per hari.
- Tabel per hari: Tanggal · Transaksi · Item · Tunai · Transfer · QRIS · Total Omzet · Pembatalan (jml).
- Total di baris akhir.

### L2 — Laporan Transaksi (P1)
- Filter: periode (berdasarkan waktu pesan), status, metode, kasir, sumber, cari kode/nama.
- Tabel: Kode · Waktu pesan · Waktu bayar · Pelanggan/Meja · Tipe · Sumber · Kasir · Metode · Total · Status.
- Klik baris → detail transaksi. Pagination 25.

### L3 — Laporan Menu Terjual (P1)
- Filter: periode, kategori.
- Tabel per menu: Menu · Kategori · Qty · Omzet · % kontribusi omzet · Harga rata-rata (dari snapshot).
- Ringkasan per kategori di atas (qty & omzet). Urutan default qty terbanyak; bisa urut omzet.
- Menu dihitung dari `order_items.product_name` + `product_id` (menu yang diarsip/diganti nama tetap muncul).

### L4 — Laporan Metode Pembayaran & Rekonsiliasi (P2)
- Filter: periode, metode, status cocok (semua / belum / sudah).
- Ringkasan per metode: jumlah transaksi, nominal, % omzet.
- Tabel per hari × metode.
- Tabel non-tunai: Kode · Waktu bayar · Metode · Nominal · Referensi · Kasir · Status cocok (checkbox). Tombol **Tandai cocok** (massal). Mencatat `reconciled_at/by`. Bisa dibatalkan (hapus tanda) oleh admin.

### L5 — Laporan Kinerja Kasir (P2)
- Filter: periode.
- Tabel per kasir: Kasir · Transaksi dikonfirmasi · Omzet · AOV · Pesanan pelanggan diterima · Ditolak/dibatalkan · Tunai yang diterima.

### L6 — Laporan Pembatalan (P2)
- Filter: periode, tahap (ditolak dari Menunggu Konfirmasi / dibatalkan dari Belum Lunas), kasir.
- Ringkasan: jumlah, nilai, tingkat pembatalan, alasan terbanyak.
- Tabel: Kode · Waktu pesan · Waktu batal · Pelanggan · Nilai · Tahap · Alasan · Oleh.

## 5. Aturan teknis

- Satu class per laporan di `App\Services\Reports\` dengan input `Period` (value object: start, end) + filter, output array/DTO. Controller hanya memanggil.
- Agregasi di database (`SUM`, `COUNT`, `GROUP BY DATE(paid_at)` / `HOUR(paid_at)`), bukan loop di PHP atas ribuan baris.
- Isi tanggal/jam kosong dengan 0 di PHP setelah query (supaya grafik tidak bolong).
- Waktu disimpan UTC atau WIB secara konsisten — tetapkan `APP_TIMEZONE=Asia/Jakarta` sejak Fase 0 agar `DATE(paid_at)` sesuai hari lokal.
- Grafik: Chart.js, data dikirim ke Blade sebagai JSON (`@json`). Warna sesuai `07-ui-design-system.md`; jangan pakai warna sebagai satu-satunya pembeda (beri label/nilai).
- Setiap laporan wajib punya **test angka** (lihat `10-qa-dan-pengujian.md`): data seed kecil dengan hasil yang dihitung manual.
