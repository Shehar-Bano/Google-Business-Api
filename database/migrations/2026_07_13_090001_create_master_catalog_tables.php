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
        Schema::create('business_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('business_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('business_categories')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->index();
            $table->string('status')->default('active')->index();
            $table->timestamps();

            // Unique subcategory name per category
            $table->unique(['category_id', 'name']);
        });

        Schema::create('offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcategory_id')
                ->constrained('business_subcategories')
                ->onDelete('cascade');
            $table->enum('type', ['product', 'service'])->index();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('keywords')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();

            // Index for name and composite unique constraint to prevent duplicates within subcategory
            $table->index('name');
            $table->unique(['subcategory_id', 'name', 'type']);
        });

        Schema::create('business_offerings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index(); // Assuming external business table or just big integer
            $table->foreignId('offering_id')
                ->constrained('offerings')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['business_id', 'offering_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_offerings');
        Schema::dropIfExists('offerings');
        Schema::dropIfExists('business_subcategories');
        Schema::dropIfExists('business_categories');
    }
};
