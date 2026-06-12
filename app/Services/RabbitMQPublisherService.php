<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RabbitMQPublisherService
{
    private string $baseUrl = 'https://iae-sso.virtualfri.id';

    public function publish(array $book, string $receiptNumber, string $jwtToken): void
    {
        $payload = [
            'event'   => 'book.created',
            'service' => 'catalog-service',
            'nim'     => env('IAE_API_KEY', 'KEY-MHS-44'),
            'data'    => [
                'book_id'        => $book['id'],
                'title'          => $book['title'],
                'author'         => $book['author'],
                'isbn'           => $book['isbn'],
                'stock'          => $book['stock'],
                'receipt_number' => $receiptNumber,
                'timestamp'      => now()->toIso8601String(),
            ],
        ];

        try {
            $response = Http::withToken($jwtToken)
                ->post("{$this->baseUrl}/api/v1/messages/publish", [
                    'message' => $payload,
                ]);

            if ($response->failed()) {
                \Illuminate\Support\Facades\Log::error('RabbitMQ publish gagal: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Koneksi ke RabbitMQ API gagal: ' . $e->getMessage());
        }
    }
}