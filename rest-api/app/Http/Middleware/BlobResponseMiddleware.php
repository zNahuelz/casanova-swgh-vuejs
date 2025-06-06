<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlobResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = $response->headers->get('Content-Type');
        $existingDisposition = $response->headers->get('Content-Disposition');

        if ($contentType === 'application/pdf') {
            if (! $existingDisposition || ! str_contains($existingDisposition, 'filename=')) {
                $response->headers->set('Content-Disposition', 'attachment; filename="boleta.pdf"');
            }
        } elseif (str_starts_with($contentType, 'image/')) {
            if (! $existingDisposition) {
                $response->headers->set('Content-Disposition', 'attachment');
                $response->headers->set('Content-Type', 'application/octet-stream');
            }
        }

        return $response;
    }
}
