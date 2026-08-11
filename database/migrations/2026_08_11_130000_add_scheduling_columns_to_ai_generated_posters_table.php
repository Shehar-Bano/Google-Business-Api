<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_generated_posters', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('approved_at');
            $table->timestamp('published_at')->nullable()->after('scheduled_at');
            $table->string('social_post_id')->nullable()->after('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_generated_posters', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'published_at', 'social_post_id']);
        });
    }
};
