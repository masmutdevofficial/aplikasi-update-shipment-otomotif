<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * Show change password form.
     */
    public function showChangeForm(): View
    {
        return view('auth.change-password');
    }

    /**
     * Handle change password request.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    /**
     * Show forgot password form.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle sending password reset link.
     */
    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::ResetLinkSent
            ? back()->with('success', 'Link reset password telah dikirim ke email Anda.')
            : back()->withInput()->with('error', 'Email tidak ditemukan dalam sistem.');
    }

    /**
     * Show reset password form.
     */
    public function showResetForm(string $token): View
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'updated_by' => $user->id,
                ]);

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login.')
            : back()->withErrors(['email' => 'Gagal mereset password. Token mungkin sudah kadaluarsa.']);
    }
}
