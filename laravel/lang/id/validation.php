<?php

/*
|--------------------------------------------------------------------------
| Pesan Validasi Bahasa Indonesia
|--------------------------------------------------------------------------
| Hanya memuat aturan yang benar-benar dipakai KasirKita.
*/

return [
    'accepted' => ':attribute harus disetujui.',
    'after' => ':attribute harus tanggal setelah :date.',
    'after_or_equal' => ':attribute harus tanggal setelah atau sama dengan :date.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute harus berupa daftar.',
    'before' => ':attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':attribute harus berisi antara :min dan :max item.',
        'file' => 'Ukuran :attribute harus antara :min dan :max kilobita.',
        'numeric' => ':attribute harus bernilai antara :min dan :max.',
        'string' => ':attribute harus antara :min dan :max karakter.',
    ],
    'boolean' => ':attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password salah.',
    'date' => ':attribute bukan tanggal yang sah.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus terdiri dari :digits angka.',
    'digits_between' => ':attribute harus terdiri dari :min sampai :max angka.',
    'dimensions' => 'Ukuran gambar :attribute tidak sesuai.',
    'distinct' => ':attribute memiliki nilai yang duplikat.',
    'email' => ':attribute harus berupa alamat surel yang sah.',
    'exists' => ':attribute yang dipilih tidak sah.',
    'file' => ':attribute harus berupa berkas.',
    'filled' => ':attribute wajib diisi.',
    'gt' => [
        'numeric' => ':attribute harus lebih besar dari :value.',
        'string' => ':attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'numeric' => ':attribute harus lebih besar atau sama dengan :value.',
        'string' => ':attribute harus minimal :value karakter.',
    ],
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak sah.',
    'integer' => ':attribute harus berupa angka bulat.',
    'lt' => [
        'numeric' => ':attribute harus lebih kecil dari :value.',
    ],
    'lte' => [
        'numeric' => ':attribute harus lebih kecil atau sama dengan :value.',
    ],
    'max' => [
        'array' => ':attribute maksimal berisi :max item.',
        'file' => 'Ukuran :attribute maksimal :max kilobita.',
        'numeric' => ':attribute maksimal bernilai :max.',
        'string' => ':attribute maksimal :max karakter.',
    ],
    'mimes' => ':attribute harus berupa berkas berjenis: :values.',
    'mimetypes' => ':attribute harus berupa berkas berjenis: :values.',
    'min' => [
        'array' => ':attribute minimal berisi :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobita.',
        'numeric' => ':attribute minimal bernilai :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'not_in' => ':attribute yang dipilih tidak sah.',
    'numeric' => ':attribute harus berupa angka.',
    'present' => ':attribute wajib ada.',
    'regex' => 'Format :attribute tidak sah.',
    'required' => ':attribute wajib diisi.',
    'required_if' => ':attribute wajib diisi bila :other bernilai :value.',
    'required_with' => ':attribute wajib diisi bila terdapat :values.',
    'required_without' => ':attribute wajib diisi bila tidak terdapat :values.',
    'same' => ':attribute dan :other harus sama.',
    'size' => [
        'array' => ':attribute harus berisi :size item.',
        'file' => 'Ukuran :attribute harus :size kilobita.',
        'numeric' => ':attribute harus bernilai :size.',
        'string' => ':attribute harus :size karakter.',
    ],
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => ':attribute gagal diunggah.',
    'url' => 'Format :attribute tidak sah.',

    'custom' => [
        'password' => [
            'min' => 'Password minimal :min karakter.',
        ],
    ],

    'attributes' => [
        'name' => 'Nama lengkap',
        'username' => 'Username',
        'password' => 'Password',
        'password_confirmation' => 'Konfirmasi password',
        'role' => 'Level akses',
        'status' => 'Status',
        'photo' => 'Foto',
        'category_id' => 'Kategori',
        'price' => 'Harga',
        'image' => 'Foto menu',
        'table_number' => 'Nomor meja',
        'pay_amount' => 'Jumlah uang',
        'payment_status' => 'Status bayar',
        'items' => 'Daftar pesanan',
        'items.*.menu_id' => 'Menu',
        'items.*.qty' => 'Jumlah',
    ],
];
