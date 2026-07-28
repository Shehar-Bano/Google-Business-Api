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
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // admin who performed action
            $table->string('action'); // e.g. login, logout, user_status_update, role_create, permission_assign, etc.
            $table->string('target_type')->nullable(); // e.g. User, Role, Permission, Business
            $table->string('target_id')->nullable(); // target record ID
            $table->text('description')->nullable(); // descriptive info
            $table->json('payload')->nullable(); // raw input payload if useful
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
