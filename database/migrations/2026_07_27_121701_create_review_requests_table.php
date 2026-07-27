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
        Schema::create('review_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('sender_id'); // authenticated user_id as string or "app"
            $table->foreignId('sent_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('phone_number');
            $table->enum('channel', ['personal', 'app']);
            $table->enum('status', ['requested', 'sent', 'clicked', 'reviewed', 'reminder_sent', 'failed'])->default('requested');
            $table->text('redirection_url')->nullable();
            $table->string('whatsapp_message_id')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_requests');
    }
};
