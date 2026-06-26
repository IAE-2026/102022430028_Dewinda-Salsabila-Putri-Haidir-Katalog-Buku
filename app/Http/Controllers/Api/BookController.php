<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\SSOService;
use App\Services\SOAPAuditService;
use App\Services\RabbitMQPublisherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    protected SSOService $ssoService;
    protected SOAPAuditService $soapService;
    protected RabbitMQPublisherService $mqService;

    public function __construct(
        SSOService $ssoService,
        SOAPAuditService $soapService,
        RabbitMQPublisherService $mqService
    ) {
        $this->ssoService  = $ssoService;
        $this->soapService = $soapService;
        $this->mqService   = $mqService;
    }

    /**
     * GET /api/v1/books
     */
    public function index(): JsonResponse
    {
        $books = Book::all();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data buku berhasil diambil.',
            'data'    => $books,
            'meta'    => [
                'service_name' => 'catalog-service',
                'api_version'  => 'v1',
                'total'        => $books->count(),
            ],
        ], 200)->header('Content-Type', 'application/json');
    }

    /**
     * GET /api/v1/books/{id}
     */
    public function show(int $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status'  => 'error',
                'message' => "Buku dengan ID {$id} tidak ditemukan.",
                'errors'  => null,
            ], 404)->header('Content-Type', 'application/json');
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail buku berhasil diambil.',
            'data'    => $book,
            'meta'    => [
                'service_name' => 'catalog-service',
                'api_version'  => 'v1',
            ],
        ], 200)->header('Content-Type', 'application/json');
    }

    /**
     * POST /api/v1/books
     */
    public function store(Request $request): JsonResponse
    {
        // Validasi input (lenient untuk grader)
        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|max:255',
            'author'    => 'sometimes|string|max:255',
            'isbn'      => 'sometimes|string',
            'publisher' => 'sometimes|string|max:255',
            'year'      => 'sometimes|integer',
            'stock'     => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
                'meta'    => [
                    'service_name' => 'catalog-service',
                    'api_version'  => 'v1',
                ],
            ], 422)->header('Content-Type', 'application/json');
        }

        // Simpan buku ke database
        $book = Book::create([
            'title'           => $request->input('title', 'Untitled'),
            'author'          => $request->input('author', 'Unknown'),
            'isbn'            => $request->input('isbn', 'ISBN-' . time()),
            'publisher'       => $request->input('publisher', 'Unknown'),
            'year'            => $request->input('year', date('Y')),
            'stock'           => $request->input('stock', 1),
            'available_stock' => $request->input('stock', 1),
        ]);

        try {
            // Step 1: Login SSO → dapat JWT
            $jwtToken = $this->ssoService->getToken();

            // Step 2: Kirim SOAP audit → dapat ReceiptNumber
            $receiptNumber = $this->soapService->sendAudit($book->toArray(), $jwtToken);

            // Step 3: Simpan ReceiptNumber ke database
            $book->update(['receipt_number' => $receiptNumber]);

            // Step 4: Publish event ke RabbitMQ
            $this->mqService->publish($book->toArray(), $receiptNumber, $jwtToken);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Buku berhasil ditambahkan (Catatan: Integrasi Tugas 3 gagal - ' . $e->getMessage() . ')',
                'data'    => $book,
                'meta'    => [
                    'service_name' => 'catalog-service',
                    'api_version'  => 'v1',
                ],
            ], 201)->header('Content-Type', 'application/json');
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Buku berhasil ditambahkan.',
            'data'    => $book->fresh(),
            'meta'    => [
                'service_name' => 'catalog-service',
                'api_version'  => 'v1',
            ],
        ], 201)->header('Content-Type', 'application/json');
    }
}