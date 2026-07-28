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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('status')->default('active'); // active, inactive
            $table->json('features')->nullable(); // json array of features e.g. ["Feature 1", "Feature 2"]
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('billing_period')->default('monthly'); // monthly, yearly
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
