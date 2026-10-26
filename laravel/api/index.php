<?php

/*
 * Titik masuk tunggal untuk Vercel (runtime vercel-php).
 *
 * Semua permintaan diarahkan ke berkas ini oleh vercel.json, lalu diteruskan
 * ke front controller Laravel yang asli di public/index.php.
 *
 * Sistem berkas di Vercel bersifat BACA-SAJA kecuali /tmp, jadi folder
 * storage/ Laravel dipindahkan ke sana sebelum framework dijalankan.
 */

$storagePath = '/tmp/storage';

foreach ([
    $storagePath.'/app/public',
    $storagePath.'/framework/cache/data',
    $storagePath.'/framework/sessions',
    $storagePath.'/framework/views',
    $storagePath.'/logs',
] as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0755, true);
    }
}

// Dibaca oleh Illuminate\Foundation\Application::storagePath().
$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;
putenv('LARAVEL_STORAGE_PATH='.$storagePath);

require __DIR__.'/../public/index.php';
