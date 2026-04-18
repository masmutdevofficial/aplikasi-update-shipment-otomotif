<?php

namespace App\Services;

use App\Models\FailedLogin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthService
{
    /**
     * Max login attempts before lockout.
     */
    protected int $maxAttempts = 5;

    /**
     * Lockout duration in seconds (15 minutes).
     */
    protected int $lockoutSeconds = 900;

    /**
     * Attempt to authenticate the user.
     *
     * @return array{success: bool, message: string, user?: User}
     */
    public function attemptLogin(string $email, string $password, string $ipAddress): array
    {
        $throttleKey = $this->throttleKey($email, $ipAddress);

        // Check if locked out
        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return [
                'success' => false,
                'message' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$minutes} menit.",
            ];
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::increment($throttleKey, $this->lockoutSeconds);
            $this->recordFailedLogin($email, $ipAddress);

            $attemptsLeft = $this->maxAttempts - RateLimiter::attempts($throttleKey);

            return [
                'success' => false,
                'message' => "Email atau password salah. Sisa percobaan: {$attemptsLeft}.",
            ];
        }

        // Check if user is active
        if (! $user->is_active) {
            return [
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ];
        }

        // Success — clear rate limiter
        RateLimiter::clear($throttleKey);

        return [
            'success' => true,
            'message' => 'Login berhasil.',
            'user' => $user,
        ];
    }

    /**
     * Get redirect path based on user level.
     */
    public function getRedirectPath(User $user): string
    {
        return match ($user->level) {
            'superadmin', 'admin' => '/admin/dashboard',
            'vendor' => '/vendor/dashboard',
            default => '/',
        };
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    /**
     * Record a failed login attempt in database.
     */
    protected function recordFailedLogin(string $email, string $ipAddress): void
    {
        $failedLogin = FailedLogin::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->first();

        if ($failedLogin) {
            $failedLogin->update([
                'attempts' => $failedLogin->attempts + 1,
                'last_attempt_at' => now(),
                'locked_until' => ($failedLogin->attempts + 1) >= $this->maxAttempts
                    ? now()->addSeconds($this->lockoutSeconds)
                    : null,
            ]);
        } else {
            FailedLogin::create([
                'email' => $email,
                'ip_address' => $ipAddress,
                'attempts' => 1,
                'last_attempt_at' => now(),
            ]);
        }
    }

    /**
     * Generate throttle key based on email and IP.
     */
    protected function throttleKey(string $email, string $ipAddress): string
    {
        return 'login|' . mb_strtolower($email) . '|' . $ipAddress;
    }
}
