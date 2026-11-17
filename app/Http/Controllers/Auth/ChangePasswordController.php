<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('auth.change-password', ['forced' => (bool) $request->user()->must_change_password]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
        if (! $user->must_change_password) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        $target = match ($user->role) {
            'admin', 'operator' => '/operator/dashboard',
            'pewawancara' => '/pewawancara/dashboard',
            'peserta' => '/peserta/dashboard',
            default => '/',
        };

        return redirect()->intended($target)->with('status', 'Password berhasil diperbarui.');
    }
}
