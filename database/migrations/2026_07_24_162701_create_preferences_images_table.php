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
        // Create preferences_images table
        Schema::create('preferences_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preference_id')->constrained('preferences')->onDelete('cascade');
            $table->string('type'); // interior_photos or team_photos
            $table->string('label')->nullable();
            $table->string('image'); // image file path
            $table->timestamps();
        });

        // Drop interior_photos and team_photos from preferences if they exist
        Schema::table('preferences', function (Blueprint $table) {
            if (Schema::hasColumn('preferences', 'interior_photos')) {
                $table->dropColumn('interior_photos');
            }
            if (Schema::hasColumn('preferences', 'team_photos')) {
                $table->dropColumn('team_photos');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferences_images');

        Schema::table('preferences', function (Blueprint $table) {
            $table->json('interior_photos')->nullable();
            $table->json('team_photos')->nullable();
        });
    }
};
