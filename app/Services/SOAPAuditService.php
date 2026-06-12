<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SOAPAuditService
{
    private string $baseUrl = 'https://iae-sso.virtualfri.id';

    public function sendAudit(array $book, string $jwtToken): string
    {
        // Buat SOAP Envelope XML
        $xml = $this->buildEnvelope($book);

        // Kirim ke endpoint SOAP dosen
        $response = Http::withToken($jwtToken)
            ->withHeaders(['Content-Type' => 'text/xml'])
            ->withBody($xml, 'text/xml')
            ->post("{$this->baseUrl}/soap/v1/audit");

        if ($response->failed()) {
            throw new \Exception('SOAP audit gagal: ' . $response->body());
        }

        // Ambil ReceiptNumber dari response XML
        $receiptNumber = $this->parseReceiptNumber($response->body());

        return $receiptNumber;
    }

    private function buildEnvelope(array $book): string
    {
        $logContent = json_encode([
            'book_id'   => $book['id'],
            'title'     => $book['title'],
            'author'    => $book['author'],
            'isbn'      => $book['isbn'],
            'stock'     => $book['stock'],
            'timestamp' => now()->toIso8601String(),
            'nim'       => env('IAE_API_KEY', 'KEY-MHS-44'),
        ]);

        $teamId = env('IAE_TEAM_ID', 'TEAM-05');

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
    <soap:Body>
        <iae:AuditRequest>
            <iae:TeamID>{$teamId}</iae:TeamID>
            <iae:ActivityName>BookCreated</iae:ActivityName>
            <iae:LogContent><![CDATA[{$logContent}]]></iae:LogContent>
        </iae:AuditRequest>
    </soap:Body>
</soap:Envelope>
XML;
    }

    private function parseReceiptNumber(string $xmlResponse): string
    {
        // Parse XML response untuk ambil ReceiptNumber
        $xml = simplexml_load_string($xmlResponse);
        $xml->registerXPathNamespace('iae', 'http://iae.central/audit');

        $receipt = $xml->xpath('//iae:ReceiptNumber');

        if (empty($receipt)) {
            throw new \Exception('ReceiptNumber tidak ditemukan di response SOAP');
        }

        return (string) $receipt[0];
    }
}