<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $book = \App\Models\Book::create([
        'title'           => 'Test',
        'author'          => 'Test',
        'isbn'            => '12345678910',
        'publisher'       => 'Test',
        'year'            => 2026,
        'stock'           => 10,
        'available_stock' => 10,
    ]);

    $controller = app(\App\Http\Controllers\Api\BookController::class);
    $sso = app(\App\Services\SSOService::class);
    $jwtToken = $sso->getToken();
    echo "JWT: " . substr($jwtToken, 0, 10) . "...\n";

    $soap = app(\App\Services\SOAPAuditService::class);
    $receipt = $soap->sendAudit($book->toArray(), $jwtToken);
    echo "Receipt: " . $receipt . "\n";
    
    echo "ALL OK!";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " on line " . $e->getLine() . " of " . $e->getFile();
}
