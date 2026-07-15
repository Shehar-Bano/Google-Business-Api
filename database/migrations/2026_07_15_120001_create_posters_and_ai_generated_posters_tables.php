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
        Schema::create('posters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->string('status')->default('Active'); // Active or Inactive
            $table->timestamps();
        });

        Schema::create('ai_generated_posters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('business_id')
                ->nullable()
                ->constrained('businesses')
                ->onDelete('cascade');
            $table->foreignId('poster_id')
                ->nullable()
                ->constrained('posters')
                ->onDelete('cascade');
            $table->longText('prompt');
            $table->string('generated_title')->nullable();
            $table->longText('generated_caption')->nullable();
            $table->string('generated_image')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generated_posters');
        Schema::dropIfExists('posters');
    }
};
