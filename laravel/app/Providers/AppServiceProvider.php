<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tampilan halaman bawaan Laravel memakai palet Tailwind standar dan melebar
        // di layar HP; semua daftar memakai tampilan milik KasirKita.
        Paginator::defaultView('vendor.pagination.kasirkita');
        Paginator::defaultSimpleView('vendor.pagination.kasirkita');
    }
}
