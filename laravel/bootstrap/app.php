<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Di Vercel, TLS diakhiri oleh proxy platform lalu diteruskan ke PHP
         * sebagai http biasa. Tanpa ini Laravel menganggap koneksinya http dan
         * menghasilkan URL aset http:// di halaman https:// — diblokir browser
         * sebagai mixed content, sehingga CSS/JS tidak termuat.
         *
         * Mempercayai semua proxy aman di sini karena satu-satunya jalan masuk
         * ke fungsi adalah proxy Vercel, yang menimpa header X-Forwarded-*.
         * Di lokal tidak berpengaruh: XAMPP tidak mengirim header tersebut.
         */
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureUserIsActive::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * Vercel memotong baris log yang panjang dan hanya menyisakan ekornya,
         * sehingga pesan galat yang sebenarnya tidak pernah terbaca — yang tampak
         * hanya bingkai stack trace paling luar. Baris ringkas berpenanda ini
         * selalu muat utuh, jadi galat produksi bisa dikenali dari Runtime Logs
         * dengan mencari "[KK]".
         */
        $exceptions->report(function (\Throwable $e): bool {
            error_log(sprintf(
                "[KK] %s: %s @ %s:%d",
                $e::class,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
            ));

            // SEMENTARA: tahan pelaporan bawaan agar entri log hanya
            // berisi baris pendek di atas dan tidak dipangkas Vercel.
            return false;
        });
    })->create();
