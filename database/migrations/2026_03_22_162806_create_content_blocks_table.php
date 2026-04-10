<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('article_id')->constrained('articles')->cascadeOnDelete();
            $table->enum('type', ['paragraph', 'heading', 'image', 'gallery', 'quote']);
            $table->integer('position');
            $table->json('content');
            $table->timestamps();

            $table->unique(['article_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
