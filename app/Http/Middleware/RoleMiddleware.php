<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // ❗ belum login
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        // ❗ role tidak sesuai
        if (!in_array($user->role, $roles)) {
            abort(403, 'Akses ditolak');
        }

        return $next($request);
    }
}
