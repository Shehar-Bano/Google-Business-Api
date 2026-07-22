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
        Schema::create('preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            
            // Text preferences
            $table->string('business_tagline')->nullable();
            $table->text('business_description')->nullable();
            $table->text('different_than_competition')->nullable();
            $table->text('why_visit_us')->nullable();
            $table->text('low_standards_of_industry')->nullable();
            $table->text('solutions_for_low_standards')->nullable();
            $table->text('malpractices_in_industry')->nullable();
            $table->text('solutions_for_malpractices')->nullable();
            $table->text('common_mistakes_by_customers')->nullable();
            $table->text('guidelines_to_customer')->nullable();
            $table->string('nearest_landmark')->nullable();
            
            // Image lists (stored as JSON arrays)
            $table->json('interior_photos')->nullable();
            $table->json('team_photos')->nullable();
            
            // Targeting & Creative options
            $table->string('target_gender')->nullable();
            $table->string('target_age_group')->nullable();
            $table->string('region')->nullable();
            $table->string('model_ethnicity')->nullable();
            $table->string('audience')->nullable();
            $table->string('cta')->nullable();
            
            // Flags
            $table->boolean('stop_creative_auto_approval')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};
