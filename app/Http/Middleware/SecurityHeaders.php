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

        // HSTS — paksa HTTPS selama 1 tahun + preload (hanya aktif di production/HTTPS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
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

        // Tambahkan Vite dev server sources hanya di environment local
        // Pakai wildcard subdomain tidak bisa untuk IP, jadi allow semua port 517x
        $isLocal = app()->environment('local');
        $scriptDevSources = $isLocal ? ' http://127.0.0.1:5173 http://127.0.0.1:5174' : '';
        $connectDevSources = $isLocal ? ' http://127.0.0.1:5173 ws://127.0.0.1:5173 http://127.0.0.1:5174 ws://127.0.0.1:5174 http://localhost:8000' : '';
        $styleDevSources = $isLocal ? ' http://127.0.0.1:5173 http://127.0.0.1:5174' : '';
        $scriptEvalSource = $request->is('admin*') ? " 'unsafe-eval'" : '';

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline'{$scriptEvalSource}{$scriptDevSources}; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com{$styleDevSources}; " .
            "font-src 'self' data: https://fonts.gstatic.com; " .
            "img-src 'self' data: blob: https:; " .
            "worker-src 'self' blob:; " .
            "connect-src 'self'{$connectDevSources};"
        );

        return $response;
    }
}
