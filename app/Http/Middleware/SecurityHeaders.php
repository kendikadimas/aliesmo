<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing — berlaku untuk semua
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // HSTS — paksa HTTPS selama 1 tahun (hanya aktif di production/HTTPS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Enable XSS protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Strict referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Remove server signature
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Khusus API routes — CSP ketat, tidak perlu render HTML
        if ($request->is('api/*')) {
            $response->headers->set('Content-Security-Policy', "default-src 'none'");
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
            return $response;
        }

        // Web routes (termasuk /admin Filament) — CSP yang kompatibel
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
            "font-src 'self' data: https://fonts.gstatic.com; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self' http://localhost:8000 ws://localhost:5173;"
        );

        return $response;
    }
}
