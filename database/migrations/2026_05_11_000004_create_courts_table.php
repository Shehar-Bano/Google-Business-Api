<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->default('glass'); // glass, wooden, synthetic, other
            $table->decimal('price_per_hour', 8, 2)->default(0);
            $table->unsignedTinyInteger('capacity')->default(2);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
