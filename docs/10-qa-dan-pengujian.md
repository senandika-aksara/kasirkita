# 10 — QA dan Pengujian

Pemilik dokumen: `qa-engineer`. QA adalah **gerbang**: tidak ada fase/PR yang selesai tanpa keputusan LULUS dari QA.

## 1. Lapisan pengujian

| Lapisan | Alat | Kapan | Bukti untuk laporan |
|---|---|---|---|
| Feature/unit test otomatis | Pest/PHPUnit + `RefreshDatabase` (SQLite in-memory) | Setiap PR | Ringkasan `php artisan test` |
| Black box (manual, terstruktur) | Tabel `docs/pengujian-blackbox.md` | Akhir tiap fase, lengkap di F7 | Tabel BAB IV |
| Review kode & keamanan | Checklist §4 | Setiap PR | Catatan di PR |
| Uji mockup | Skenario di `mockup/index.html` | F1 | Masukan di PROGRESS.md |
| UAT | Kuesioner Likert + tugas skenario | F7 | Tabel & grafik hasil UAT di BAB IV |

## 2. Test otomatis minimum

**Auth & akses**: login benar/salah; redirect per peran; kasir 403 di `/admin`; akun nonaktif ditolak; rate limit login.

**Master data**: validasi kategori & menu; hapus kategori berisi menu ditolak; hapus menu yang pernah terjual ditolak (arsip berhasil); menu Habis/Arsip tidak muncul sebagai bisa-dipesan.

**Pesanan**:
- Total dihitung server walau request mengirim harga/total palsu.
- Snapshot nama & harga tersimpan; ubah harga menu tidak mengubah pesanan lama.
- Kode `KK-YYMMDD-NNN` berurutan dan reset per hari.
- Menu Habis/Arsip ditolak; keranjang kosong ditolak; qty 0/100 ditolak.
- Pesanan pelanggan saat tutup/saklar mati ditolak; rate limit 5/10 menit.
- `public_id` berupa ULID; status JSON hanya mengembalikan data yang aman (tanpa ID internal user).

**Transisi status**: semua transisi sah berhasil; semua transisi tidak sah ditolak (mis. bayar pesanan `pending_confirmation`, batal pesanan `paid`); alasan wajib.

**Pembayaran**: uang pas (kosong); uang lebih → kembalian benar; uang kurang ditolak; non-tunai tanpa centang ditolak; bayar dua kali ditolak.

**Laporan & dashboard** (wajib, karena angka adalah inti aplikasi):
- Seed kecil yang dirancang manual (mis. 6 transaksi di 3 hari, 2 metode, 1 batal, 1 pesanan lewat tengah malam WIB) dengan **hasil yang ditulis tangan di komentar test**.
- Omzet hanya dari `paid` berdasarkan `paid_at`; hari kosong bernilai 0; perubahan % dengan pembanding 0 → null.
- Angka dashboard = L1 = rekap kasir untuk periode sama.
- CSV: header benar, jumlah baris = jumlah data, angka tanpa pemisah ribuan.

## 3. Black box — format tabel

Simpan di `docs/pengujian-blackbox.md`:

| No | ID Kebutuhan | Fitur | Skenario | Data uji | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|---|---|---|
| 1 | KF-04 | Login | Login kasir dengan data benar | kasir1 / (password demo) | Masuk ke halaman POS | | |

Aturan: setiap AC di `03-kebutuhan.md` minimal 1 baris; tiap form minimal 1 kasus input salah; "Hasil aktual" ditulis apa adanya; Status `Berhasil` / `Gagal` (gagal → buat issue).

## 4. Checklist review PR (dipakai `qa-engineer`)

Fungsi
- [ ] Semua AC tugas terpenuhi (sebutkan satu per satu)
- [ ] Sesuai `01-ruang-lingkup.md` (tidak ada fitur di luar scope)
- [ ] Sesuai aturan bisnis `02` (BR terkait disebut)

Data & keamanan
- [ ] Validasi di Form Request; tidak mempercayai harga/total/status dari request
- [ ] Otorisasi peran & transisi status di server, bukan hanya tombol disembunyikan
- [ ] Operasi multi-tabel di dalam `DB::transaction`
- [ ] Tidak ada `{!! !!}` untuk data dari pengguna (XSS)
- [ ] Tidak ada query mentah dengan input pengguna tanpa binding
- [ ] Tidak ada hard delete data transaksi
- [ ] Tidak ada rahasia/kredensial di kode

Kualitas
- [ ] `php artisan test` hijau; test baru untuk perilaku baru
- [ ] Pint bersih
- [ ] Tidak ada N+1 di halaman daftar
- [ ] Tampilan sesuai mockup; cek 360px & 1024px; tidak ada error console
- [ ] Label/pesan Bahasa Indonesia, format Rupiah & tanggal benar

Keputusan ditulis: **LULUS** / **GAGAL — perlu perbaikan: (daftar)** / **LULUS DENGAN CATATAN: (daftar non-blocking)**.

## 5. Template laporan bug (GitHub Issue)

```
Judul: [F5] Kembalian minus saat uang diterima kosong
Tingkat: Kritis / Tinggi / Sedang / Rendah
Langkah:
1. Login kasir1
2. ...
Hasil sekarang:
Hasil seharusnya: (rujuk KF/BR)
Lingkungan: browser, ukuran layar, commit
Bukti: screenshot
```
Tingkat: **Kritis** = data uang salah/hilang, tidak bisa transaksi, celah akses; **Tinggi** = fitur P1 tidak jalan; **Sedang** = P2 bermasalah / tampilan rusak; **Rendah** = kosmetik.

## 6. UAT

- Peserta: idealnya pemilik/kasir UMKM kuliner nyata + anggota/rekan sebagai pelanggan.
- Tugas skenario (diukur berhasil/tidak & waktu): (1) kasir mencatat pesanan 3 item dan menerima pembayaran tunai; (2) pelanggan memesan dari HP, kasir menerima, pelanggan bayar QRIS; (3) pemilik mencari menu terlaris minggu ini; (4) pemilik melihat omzet bulan ini per metode bayar; (5) pemilik menandai transaksi non-tunai sudah cocok.
- Kuesioner `docs/kuesioner-uat.md`: 10 pernyataan Likert 1–5 tentang kemudahan & kegunaan (selaras dengan *effort expectancy* & *performance expectancy* pada penelitian UTAUT yang dikutip di BAB II), plus 2 pertanyaan terbuka.
- Rekap: rata-rata per pernyataan, persentase setuju, kutipan masukan.
