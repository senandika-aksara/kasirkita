<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun yang dinonaktifkan saat sedang login langsung dikeluarkan dari sistem.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
<<<<<<< HEAD
                ->withErrors(['username' => 'Akun Anda telah dinonaktifkan. Hubungi admin atau pemilik usaha.']);
=======
                ->withErrors(['username' => 'Akun Anda telah dinonaktifkan. Hubungi manajer.']);
>>>>>>> 967352ece77c129992614c2829f2f0e00550498f
        }

        return $next($request);
    }
}
