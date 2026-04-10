<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->integer('likes_count')->default(0)->after('reading_time');
            $table->index('likes_count');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->integer('likes_count')->default(0)->after('recap_article_id');
            $table->index('likes_count');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('likes_count');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('likes_count');
        });
    }
};
