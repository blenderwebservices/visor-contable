<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach security headers (Pilar 8 de Auditoría).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Protección contra Clickjacking (permitiendo frames del mismo origen para el visor de Excel/HTML)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Evitar detección automática de tipo MIME (MIME-sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Política de Referencias estricta
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restricción de APIs del dispositivo
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=(), payment=()');

        // Forzar HTTPS (HSTS) en entornos seguros
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
