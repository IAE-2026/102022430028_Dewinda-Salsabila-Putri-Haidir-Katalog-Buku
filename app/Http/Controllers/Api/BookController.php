<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookController extends Controller
{
    #[OA\Get(
        path: "/api/v1/books",
        summary: "Get all books",
        tags: ["Books"],
        security: [["ApiKeyAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Books retrieved successfully"
            )
        ]
    )]
    public function index()
    {
        $books = Book::all();

        return response()->json([
            "status" => "success",
            "message" => "Books retrieved successfully",
            "data" => $books,
            "meta" => [
                "service_name" => "Katalog-Service",
                "api_version" => "v1"
            ]
        ]);
    }

    #[OA\Get(
        path: "/api/v1/books/{id}",
        summary: "Get book by ID",
        tags: ["Books"],
        security: [["ApiKeyAuth" => []]],

        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID buku",
                in: "path",
                required: true,
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: "Book retrieved successfully"
            ),

            new OA\Response(
                response: 404,
                description: "Book not found"
            )
        ]
    )]
    public function show($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                "status" => "error",
                "message" => "Book not found",
                "errors" => null
            ], 404);
        }

        return response()->json([
            "status" => "success",
            "message" => "Book retrieved successfully",
            "data" => $book,
            "meta" => [
                "service_name" => "Katalog-Service",
                "api_version" => "v1"
            ]
        ]);
    }

    #[OA\Post(
        path: "/api/v1/books",
        summary: "Create new book",
        tags: ["Books"],
        security: [["ApiKeyAuth" => []]],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "author", "stock"],
                properties: [
                    new OA\Property(
                        property: "title",
                        type: "string",
                        example: "Example"
                    ),
                    new OA\Property(
                        property: "author",
                        type: "string",
                        example: "Example"
                    ),
                    new OA\Property(
                        property: "stock",
                        type: "integer",
                        example: 20
                    )
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 201,
                description: "Book created successfully"
            )
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'author' => 'required',
            'stock' => 'required|integer'
        ]);

        $book = Book::create($validated);

        return response()->json([
            "status" => "success",
            "message" => "Book created successfully",
            "data" => $book,
            "meta" => [
                "service_name" => "Katalog-Service",
                "api_version" => "v1"
            ]
        ], 201);
    }
}