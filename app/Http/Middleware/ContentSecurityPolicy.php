<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set(
            'Content-Security-Policy',
            "script-src 'self' 'unsafe-eval' https://app.sandbox.midtrans.com https://app.midtrans.com"
        );

        return $response;
    }
}
