<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Test asJson ===\n";
$response = \Illuminate\Support\Facades\Http::asJson()->post('https://iae-sso.virtualfri.id/api/v1/auth/token', [
    'api_key' => 'KEY-MHS-38',
]);
echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n\n";

echo "=== Test withBody raw JSON ===\n";
$response2 = \Illuminate\Support\Facades\Http::withHeaders([
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
])->withBody(json_encode(['api_key' => 'KEY-MHS-38']), 'application/json')->post('https://iae-sso.virtualfri.id/api/v1/auth/token');
echo "Status: " . $response2->status() . "\n";
echo "Body: " . $response2->body() . "\n\n";

echo "=== Test asForm ===\n";
$response3 = \Illuminate\Support\Facades\Http::asForm()->post('https://iae-sso.virtualfri.id/api/v1/auth/token', [
    'api_key' => 'KEY-MHS-38',
]);
echo "Status: " . $response3->status() . "\n";
echo "Body: " . $response3->body() . "\n";
