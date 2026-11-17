<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password tidak valid.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();
        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Akun tidak aktif.'])->onlyInput('email');
        }

        if ($user->must_change_password) {
            return redirect()->route('password.change.form');
        }

        return redirect()->intended($this->redirectPathFor($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    protected function redirectPathFor($user): string
    {
        return match ($user->role) {
            'admin', 'operator' => '/operator/dashboard',
            'pewawancara' => '/pewawancara/dashboard',
            'peserta' => '/peserta/dashboard',
            default => '/',
        };
    }
}
