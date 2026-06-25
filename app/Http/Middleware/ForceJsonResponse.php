<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Memastikan semua request API menggunakan Accept: application/json
     * dan semua response memiliki Content-Type: application/json.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force Accept header agar Laravel selalu return JSON
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Force Content-Type header pada response
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
