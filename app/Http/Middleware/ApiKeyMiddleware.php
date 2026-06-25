<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Cek apakah request memiliki header X-IAE-KEY yang valid.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil API key dari header X-IAE-KEY
        $apiKey = $request->header('X-IAE-KEY');

        // Bandingkan dengan yang ada di .env
        if ($apiKey !== config('app.iae_api_key')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: API Key tidak valid atau tidak ditemukan.',
                'errors'  => null,
            ], 401)->header('Content-Type', 'application/json');
        }

        return $next($request);
    }
}