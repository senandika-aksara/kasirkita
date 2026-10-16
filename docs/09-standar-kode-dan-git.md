# 09 — Standar Kode dan Alur Git

Pemilik dokumen: `pm-flow` + `qa-engineer`.

## 1. Git & GitHub

- Branch utama: `main` (selalu bisa dijalankan). Dilindungi: wajib PR, minimal 1 review.
- Branch kerja: `<jenis>/<id-tugas>-<ringkas>`, contoh `feat/F5-07-pembayaran`, `fix/F7-12-kembalian-minus`, `docs/F2-04-bab3`, `mockup/F1-03-kasir`.
- Commit kecil, pesan Bahasa Indonesia, format: `<jenis>(<area>): <ringkasan>`
  - jenis: `feat`, `fix`, `refactor`, `test`, `docs`, `style`, `chore`, `mockup`
  - contoh: `feat(kasir): simpan pesanan dari POS dengan kode harian`
- PR: judul = ID tugas + ringkasan; isi = laporan serah terima (format AGENTS.md §7) + screenshot jika ada tampilan.
- Merge hanya setelah `qa-engineer` menulis **LULUS** di PR.
- Tag: `mockup-v1`, `v1.0.0`.
- Jangan commit: `.env`, `vendor/`, `node_modules/`, file upload di `storage/`, dump database berisi data asli.

## 2. PHP / Laravel

- Ikuti gaya bawaan Laravel; rapikan dengan **Laravel Pint** (`./vendor/bin/pint`) sebelum commit.
- Controller tipis → Form Request untuk validasi → Service untuk logika bisnis → Model untuk relasi/scope.
- Pesan validasi Bahasa Indonesia: buat `lang/id/validation.php` (salin dari `lang/en` lalu terjemahkan pesan yang dipakai) dan `attributes` agar nama field ramah.
- Uang: tipe `int`, helper `Money::format(int $rupiah): string`. Dilarang `float`.
- Query: eager load (`with()`) untuk daftar; hindari N+1 (aktifkan `Model::preventLazyLoading()` di lingkungan lokal).
- Tanggal: Carbon dengan timezone aplikasi; tampilkan dengan helper format Indonesia.
- Tidak ada logika bisnis di Blade selain menampilkan.
- Tidak menambah package Composer/npm tanpa persetujuan `pm-flow` (tulis alasan di PR).

## 3. Blade / Frontend

- Komponen Blade anonim di `resources/views/components/`.
- Alpine.js untuk interaksi lokal (keranjang, modal, hitung kembalian, polling). Data awal dari server via `@json`.
- Kelas Tailwind mengikuti mockup; token warna dari tema, bukan hex acak.
- Setiap form: label, error per field (`@error`), tombol submit nonaktif saat mengirim (cegah dobel klik).
- Aksesibilitas: lihat `07` dan KNF-07.

## 4. Penamaan

| Hal | Bahasa | Contoh |
|---|---|---|
| Tabel, kolom, model, class, method, route name | Inggris | `orders`, `PaymentService::confirm()`, `cashier.orders.pay` |
| URL yang dilihat pengguna | Indonesia | `/kasir/pesanan-aktif` |
| Label UI, pesan, dokumen | Indonesia | "Tandai Lunas" |
| Komentar kode | Indonesia, singkat, hanya bila perlu | |

## 5. Struktur commit per fitur (urutan yang disarankan)
1. Migrasi/model (jika ada) → 2. Service + test unit/feature → 3. Controller + route + request → 4. View → 5. Test akhir & screenshot → PR.
