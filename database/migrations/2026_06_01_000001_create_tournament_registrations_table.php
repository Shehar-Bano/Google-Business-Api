<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('users')->cascadeOnDelete();
            $table->string('payment_method_id');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('paid');
            $table->enum('registration_status', ['registered', 'cancelled'])->default('registered');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('PKR');
            $table->timestamps();

            $table->unique(['tournament_id', 'player_id']);
            $table->index(['player_id', 'registration_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_registrations');
    }
};
