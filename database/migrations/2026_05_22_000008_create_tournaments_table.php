<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('format');
            $table->string('tournament_image')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('registration_deadline');
            $table->decimal('entry_fees', 10, 2)->default(0);
            $table->decimal('prize_pool', 10, 2)->default(0);
            $table->unsignedInteger('allowed_player')->default(0);
            $table->unsignedInteger('registered_players_count')->default(0);
            $table->enum('status', ['open', 'full', 'closed', 'completed', 'cancelled'])->default('open');
            $table->text('rules')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
