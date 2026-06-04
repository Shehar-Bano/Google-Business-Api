<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['available', 'booked', 'blocked'])->default('available');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['court_id', 'booking_date', 'start_time']);
            $table->index(['club_id', 'booking_date', 'status']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('slot_id')->index();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('booking_status', ['pending', 'confirmed', 'cancelled', 'completed', 'failed'])->default('confirmed');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('paid');
            $table->string('payment_method');
            $table->string('payment_transaction_id')->nullable();
            $table->decimal('court_price', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('PKR');
            $table->timestamps();

            $table->unique(['slot_id']);
            $table->index(['club_id', 'court_id', 'booking_date', 'booking_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('court_time_slots');
    }
};
