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
        Schema::create('business_estimated_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->enum('name', [
                'google_reviews',
                'active_days',
                'reviews_replied',
                'google_ratings',
                'business_description',
                'primary_category',
                'business_category',
                'contact_phone_number',
                'business_photos',
                'post_upload_frequency',
                'country',
                'state',
                'city',
                'pincode'
            ]);
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_estimated_scores');
    }
};
