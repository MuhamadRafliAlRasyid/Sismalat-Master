<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $role = Auth::user()->role;

                switch ($role) {
                    case 'super':
                        return redirect('/super/dashboard');
                    case 'admin':
                        return redirect('/admin/dashboard');
                    case 'karyawan':
                        return redirect('/karyawan/dashboard');
                    default:
                        return redirect('/'); // fallback
                }
            }
        }

        return $next($request);
    }
}
