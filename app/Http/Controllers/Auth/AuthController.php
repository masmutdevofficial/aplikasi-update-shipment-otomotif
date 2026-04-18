<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Show login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $result = $this->authService->attemptLogin(
            $request->validated('email'),
            $request->validated('password'),
            $request->ip()
        );

        if (! $result['success']) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', $result['message']);
        }

        $user = $result['user'];

        Auth::login($user);
        $request->session()->regenerate();

        $redirectPath = $this->authService->getRedirectPath($user);

        return redirect()->intended($redirectPath)
            ->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
