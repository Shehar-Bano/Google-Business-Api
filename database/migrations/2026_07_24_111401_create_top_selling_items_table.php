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
        // Remove top_selling_items column from businesses table
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('top_selling_items');
        });

        // Create new top_selling_items table
        Schema::create('top_selling_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('media')->nullable(); // path to image/media file
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_selling_items');

        Schema::table('businesses', function (Blueprint $table) {
            $table->json('top_selling_items')->nullable();
        });
    }
};
