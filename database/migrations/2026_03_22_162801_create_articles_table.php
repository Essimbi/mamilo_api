<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->foreignUuid('cover_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->enum('status', ['draft', 'review', 'scheduled', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('author_id')->constrained('users')->cascadeOnDelete();
            $table->integer('reading_time')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
