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
        Schema::create('poster_social_publishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_generated_post_id')->constrained('ai_generated_posters')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('google')->default(false);
            $table->boolean('facebook')->default(false);
            $table->boolean('instagram')->default(false);
            $table->enum('status', ['posted', 'failed', 'partial'])->default('posted');
            $table->text('failed_reason')->nullable();
            $table->string('facebook_post_id')->nullable();
            $table->string('instagram_post_id')->nullable();
            $table->string('google_post_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poster_social_publishes');
    }
};
