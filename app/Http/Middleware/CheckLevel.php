<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$levels
     */
    public function handle(Request $request, Closure $next, string ...$levels): Response
    {
        if (! $request->user() || ! in_array($request->user()->level, $levels)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
