<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'year',
        'stock',
        'available_stock',
        'receipt_number'
    ];

    protected $casts = [
        'stock'           => 'integer',
    ];
}