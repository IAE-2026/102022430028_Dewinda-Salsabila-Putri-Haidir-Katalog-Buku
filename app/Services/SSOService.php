<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SSOService
{
    private string $baseUrl = 'https://iae-sso.virtualfri.id';

    public function getToken(): string
    {
        // M2M token via api_key (server SSO butuh format form-data)
        $response = Http::asForm()->acceptJson()->post("{$this->baseUrl}/api/v1/auth/token", [
            'api_key' => env('IAE_API_KEY', 'KEY-MHS-44'),
        ]);

        if ($response->failed()) {
            throw new \Exception('SSO login gagal: ' . $response->body());
        }

        $token = $response->json('token') ?? $response->json('access_token');
        
        if (!$token) {
            throw new \Exception('Token tidak ditemukan dalam response SSO: ' . $response->body());
        }
        
        return $token;
    }

    public function getUserToken(): string
    {
        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
            'email'    => 'warga38@ktp.iae.id',
            'password' => 'KtpDigital2026!',
        ]);

        if ($response->failed()) {
            throw new \Exception('SSO user login gagal: ' . $response->body());
        }

        return $response->json('access_token');
    }
}