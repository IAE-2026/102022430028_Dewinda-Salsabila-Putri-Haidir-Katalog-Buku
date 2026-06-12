<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class VerifyJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak disertakan',
            ], 401);
        }

        try {
            // Ambil JWKS dari cache atau request baru ke SSO
            $jwks = Cache::remember('jwks', 3600, function () {
                $response = Http::get('https://iae-sso.virtualfri.id/api/v1/auth/jwks');
                if ($response->failed()) {
                    throw new \Exception('Gagal mengambil JWKS');
                }
                return $response->json();
            });

            // Parse JWKS menjadi format yang dimengerti library php-jwt
            $keys = JWK::parseKeySet($jwks);

            // Verifikasi token
            $decoded = JWT::decode($token, $keys);

            // Token valid. Mapping user & role ke lokal
            $email = $decoded->email ?? ($decoded->sub ?? null);
            $roleName = $decoded->role ?? 'warga'; // Default role jika tidak ada
            $name = $decoded->name ?? explode('@', $email)[0] ?? 'User';

            if ($email) {
                // Find or create user
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => bcrypt(\Illuminate\Support\Str::random(16)) // Password dummy
                    ]
                );

                // Find or create role
                $role = Role::firstOrCreate(['name' => $roleName]);

                // Sync role
                $user->roles()->syncWithoutDetaching([$role->id]);

                // Set user to auth
                Auth::login($user);
            }

            // Simpan decoded token di request untuk penggunaan lanjutan
            $request->attributes->add(['jwt_payload' => $decoded]);
            $request->attributes->add(['jwt_token' => $token]); // Simpan token mentah untuk re-use

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token sudah kadaluarsa',
            ], 401);
        } catch (\Exception $e) {
            Log::error('JWT Verification Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid: ' . $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}
