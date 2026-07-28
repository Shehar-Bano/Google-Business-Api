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
        Schema::create('business_keyword_ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('search_query'); // e.g. "restaurant"
            $table->string('keyword'); // the idea e.g. "best burgers"
            $table->bigInteger('avg_monthly_searches')->nullable();
            $table->string('competition')->nullable();
            $table->double('low_range_bid')->nullable();
            $table->double('high_range_bid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_keyword_ideas');
    }
};
