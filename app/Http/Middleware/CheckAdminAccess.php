<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role !== 'admin') {
            $email = (string) auth()->user()->email;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('filament.nibiru.auth.login')
                ->withErrors([
                    'data.email' => 'Akses Ditolak! Anda bukan Admin.',
                ])
                ->withInput([
                    'data' => [
                        'email' => $email,
                    ],
                ]);
        }

        return $next($request);
    }
}
