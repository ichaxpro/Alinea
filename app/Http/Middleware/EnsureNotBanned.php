<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->is_banned) {
            // Allow read-only operations (GET, HEAD, OPTIONS)
            if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
                return $next($request);
            }

            // Allow logout
            if ($request->is('logout')) {
                return $next($request);
            }
            
            // Allow admin routes if they somehow get banned (or maybe they shouldn't be here)
            if ($request->is('admin*') || $request->is('livewire*') || $request->is('filament*')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Akun Anda telah diblokir. Anda hanya dapat melihat konten.'], 403);
            }

            return back()->with('error', 'Akun Anda telah diblokir. Anda hanya dapat melihat konten.');
        }

        return $next($request);
    }
}
