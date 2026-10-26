<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** Halaman depan hanya mengarahkan pengguna ke panel yang sesuai perannya. */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        return $user->isManagerial()
            ? redirect()->route('manager.dashboard')
            : redirect()->route('cashier.transactions.index');
    }
}
