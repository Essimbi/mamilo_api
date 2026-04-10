<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->string('location');
            $table->dateTime('event_date');
            $table->string('type');
            $table->foreignUuid('cover_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignUuid('recap_article_id')->nullable()->constrained('articles')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
