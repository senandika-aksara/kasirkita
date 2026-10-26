<?php

/*
 * Titik masuk tunggal untuk Vercel (runtime vercel-php).
 *
 * Semua permintaan diarahkan ke berkas ini oleh vercel.json, lalu diteruskan
 * ke front controller Laravel yang asli di public/index.php.
 *
 * Sistem berkas di Vercel bersifat BACA-SAJA kecuali /tmp, jadi dua hal
 * dipindahkan ke sana sebelum framework dijalankan:
 *
 * 1. Folder storage/ Laravel (cache view, sesi, log).
 * 2. Cache penemuan paket (bootstrap/cache/packages.php & services.php).
 *    Runtime vercel-php menjalankan `composer install` TANPA scripts, sehingga
 *    `artisan package:discover` tidak pernah berjalan saat build dan kedua
 *    berkas itu tidak ikut terbundel. Laravel lalu mencoba membuatnya sendiri
 *    di bootstrap/cache yang read-only, gagal, dan seluruh service provider
 *    batal terdaftar — gejalanya "Target class [view] does not exist".
 */

$storagePath = '/tmp/storage';

foreach ([
    $storagePath.'/app/public',
    $storagePath.'/bootstrap',
    $storagePath.'/framework/cache/data',
    $storagePath.'/framework/sessions',
    $storagePath.'/framework/views',
    $storagePath.'/logs',
] as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0755, true);
    }
}

/*
 * Dibaca oleh Illuminate\Foundation\Application:
 * - LARAVEL_STORAGE_PATH -> storagePath()
 * - APP_PACKAGES_CACHE   -> getCachedPackagesPath()
 * - APP_SERVICES_CACHE   -> getCachedServicesPath()
 *
 * Nilai yang diawali "/" dipakai apa adanya sebagai jalur absolut.
 */
foreach ([
    'LARAVEL_STORAGE_PATH' => $storagePath,
    'APP_PACKAGES_CACHE' => $storagePath.'/bootstrap/packages.php',
    'APP_SERVICES_CACHE' => $storagePath.'/bootstrap/services.php',
] as $key => $value) {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv($key.'='.$value);
}

require __DIR__.'/../public/index.php';
