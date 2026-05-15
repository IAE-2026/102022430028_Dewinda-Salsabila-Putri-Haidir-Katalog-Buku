<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Catalog Service API",
    version: "1.0.0",
    description: "Service Katalog Buku - Tugas 2 IAE"
)]

#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "X-IAE-KEY"
)]

abstract class Controller
{
    //
}