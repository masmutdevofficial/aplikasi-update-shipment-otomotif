<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\VendorAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVendorStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status === User::STATUS_PENDING) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'error',
                'Akun Anda masih berstatus Pending dan belum dapat login. Silakan hubungi administrator.'
            );
        }

        if ($user && ! $user->canLogin()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        if ($user && $user->isVendor() && VendorAccess::isMaintenance()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', VendorAccess::maintenanceMessage());
        }

        return $next($request);
    }
}
