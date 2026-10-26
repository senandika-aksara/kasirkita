<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();

        if ($request->viaSuperPassword) {
            // Dicatat berbeda agar jejak pemakaian password master tetap terlihat
            // di riwayat aktivitas dan tidak tersamar sebagai login biasa.
            ActivityLog::record($user, 'Login', 'Masuk sebagai '.$user->role->label().' memakai password master', 'Perlu diperhatikan');
        } else {
            ActivityLog::record($user, 'Login', 'Masuk ke sistem sebagai '.$user->role->label());
        }

        return redirect()->intended($this->homeFor($request));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah keluar dari sistem.');
    }

    /** Kasir langsung ke daftar transaksi, manajer & superadmin ke dashboard. */
    private function homeFor(Request $request): string
    {
        return $request->user()->isManagerial()
            ? route('manager.dashboard')
            : route('cashier.transactions.index');
    }
}
