# 05 — Halaman, Route, dan Hak Akses

Pemilik dokumen: `backend-dev` + `frontend-dev`. Nama route dipakai di Blade (`route('...')`), jangan menulis URL manual.

## 1. Peta halaman (sitemap)

```
PUBLIK (Pelanggan, tanpa login)
└── /                         One-page: profil usaha + menu + keranjang + form pesan
    └── /pesanan/{public_id}  Status pesanan

AUTH
└── /login

PANEL KASIR (cashier, admin)          layout: sidebar kiri, optimal tablet landscape
├── /kasir                    POS
├── /kasir/pesanan-masuk      Pesanan Menunggu Konfirmasi
├── /kasir/pesanan-aktif      Pesanan Belum Lunas (+ modal bayar)
├── /kasir/riwayat            Transaksi hari ini
├── /kasir/pesanan/{order}    Detail transaksi
├── /kasir/pesanan/{order}/struk   Halaman cetak struk
└── /kasir/rekap              Rekap hari ini

PANEL ADMIN (admin)                   layout: sidebar kiri, optimal laptop
├── /admin                    Dashboard
├── /admin/kategori           Kelola kategori
├── /admin/menu               Kelola menu
├── /admin/pengguna           Kelola akun kasir
├── /admin/pengaturan         Pengaturan usaha
├── /admin/laporan/penjualan
├── /admin/laporan/transaksi
├── /admin/laporan/menu
├── /admin/laporan/pembayaran (+ rekonsiliasi)
├── /admin/laporan/kasir
├── /admin/laporan/pembatalan
└── /admin/qr-meja            (P3)
```

## 2. Daftar route

| Method | URI | Nama | Controller@aksi | Akses |
|---|---|---|---|---|
| GET | / | `home` | `Public\StorefrontController@index` | publik |
| POST | /pesan | `public.orders.store` | `Public\OrderController@store` (throttle:public-order) | publik |
| GET | /pesanan/{order:public_id} | `public.orders.show` | `Public\OrderController@show` | publik |
| GET | /pesanan/{order:public_id}/status | `public.orders.status` | `Public\OrderController@status` (JSON) | publik |
| GET | /login | `login` | `Auth\LoginController@create` | tamu |
| POST | /login | – | `Auth\LoginController@store` (throttle:login) | tamu |
| POST | /logout | `logout` | `Auth\LoginController@destroy` | login |
| GET | /kasir | `cashier.pos` | `Cashier\PosController@index` | role:admin,cashier |
| POST | /kasir/pesanan | `cashier.orders.store` | `Cashier\OrderController@store` | role:admin,cashier |
| GET | /kasir/pesanan-masuk | `cashier.orders.incoming` | `Cashier\OrderController@incoming` | role:admin,cashier |
| GET | /kasir/pesanan-masuk/jumlah | `cashier.orders.incoming-count` | JSON `{count}` untuk badge | role:admin,cashier |
| POST | /kasir/pesanan/{order}/terima | `cashier.orders.confirm` | `Cashier\OrderController@confirm` | role:admin,cashier |
| POST | /kasir/pesanan/{order}/tolak | `cashier.orders.reject` | `Cashier\OrderController@reject` | role:admin,cashier |
| GET | /kasir/pesanan-aktif | `cashier.orders.active` | `Cashier\OrderController@active` | role:admin,cashier |
| POST | /kasir/pesanan/{order}/bayar | `cashier.orders.pay` | `Cashier\PaymentController@store` | role:admin,cashier |
| POST | /kasir/pesanan/{order}/batal | `cashier.orders.cancel` | `Cashier\OrderController@cancel` | role:admin,cashier |
| GET | /kasir/riwayat | `cashier.orders.history` | `Cashier\OrderController@history` | role:admin,cashier |
| GET | /kasir/pesanan/{order} | `cashier.orders.show` | `Cashier\OrderController@show` | role:admin,cashier |
| GET | /kasir/pesanan/{order}/struk | `cashier.orders.receipt` | `Cashier\ReceiptController@show` | role:admin,cashier |
| GET | /kasir/rekap | `cashier.recap` | `Cashier\RecapController@index` | role:admin,cashier |
| PATCH | /kasir/menu/{product}/ketersediaan | `cashier.products.availability` | `Cashier\ProductAvailabilityController@update` | role:admin,cashier |
| GET | /admin | `admin.dashboard` | `Admin\DashboardController@index` (?periode=) | role:admin |
| resource | /admin/kategori | `admin.categories.*` | `Admin\CategoryController` (tanpa show) | role:admin |
| resource | /admin/menu | `admin.products.*` | `Admin\ProductController` (tanpa show) | role:admin |
| PATCH | /admin/menu/{product}/arsip | `admin.products.archive` | toggle arsip | role:admin |
| resource | /admin/pengguna | `admin.users.*` | `Admin\UserController` (index, create, store, edit, update) | role:admin |
| PATCH | /admin/pengguna/{user}/status | `admin.users.toggle` | aktif/nonaktif | role:admin |
| GET/PUT | /admin/pengaturan | `admin.settings.edit/update` | `Admin\SettingController` | role:admin |
| GET | /admin/laporan/{jenis} | `admin.reports.show` | `Admin\ReportController@show` | role:admin |
| GET | /admin/laporan/{jenis}/export | `admin.reports.export` | CSV | role:admin |
| GET | /admin/laporan/{jenis}/cetak | `admin.reports.print` | halaman cetak | role:admin |
| POST | /admin/laporan/pembayaran/cocokkan | `admin.reports.reconcile` | tandai cocok (array id) | role:admin |
| GET | /admin/qr-meja | `admin.table-qr` | P3 | role:admin |

`{jenis}` ∈ `penjualan, transaksi, menu, pembayaran, kasir, pembatalan` (whitelist; selain itu 404).

## 3. Middleware & keamanan

- `EnsureUserHasRole` dengan alias `role`, parameter daftar peran: `role:admin,cashier`.
- `EnsureUserIsActive`: logout paksa jika akun dinonaktifkan saat sedang login.
- Rate limiter di `AppServiceProvider`: `login` (5/menit per username+IP), `public-order` (5 per 10 menit per IP — BR-17).
- Setelah login redirect sesuai peran (KF-04 AC2).
- Semua aksi ubah data memakai POST/PATCH/PUT + CSRF; tidak ada aksi ubah data lewat GET.
- Gunakan Policy atau pengecekan status di Service untuk transisi status (BR-09), bukan hanya menyembunyikan tombol.

## 4. Struktur kode yang disarankan

```
laravel/app/
├── Enums/            OrderStatus, PaymentMethod, OrderType, OrderSource, UserRole
├── Models/           User, StoreSetting, Category, Product, Order, OrderItem, Payment
├── Services/
│   ├── OrderService.php         buat pesanan (kasir & pelanggan), kode, total
│   ├── OrderStatusService.php   terima, tolak, batal
│   ├── PaymentService.php       bayar + kembalian + lock
│   └── Reports/                 satu class per laporan + DashboardService
├── Http/
│   ├── Controllers/{Public,Auth,Cashier,Admin}/
│   ├── Requests/                satu Form Request per form
│   └── Middleware/
└── Support/Money.php            format Rupiah
```

Controller tipis: validasi (Form Request) → panggil Service → redirect dengan flash message.
