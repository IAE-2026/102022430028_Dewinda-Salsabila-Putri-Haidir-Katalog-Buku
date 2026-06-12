<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->unique();
            $table->string('publisher');
            $table->integer('year');
            $table->integer('stock');
            $table->integer('available_stock');
            $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};