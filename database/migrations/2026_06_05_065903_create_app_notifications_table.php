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
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');

            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('type')->nullable();

            $table->json('data')->nullable();

            $table->string('role_type')->nullable();

            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id'], 'app_notifications_notifiable_idx');
            $table->index('user_id');
            $table->index('type');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
