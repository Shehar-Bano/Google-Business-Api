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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('provider');
            $table->string('provider_user_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('profile_picture')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->string('status')->default('connected');
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
            $table->index('provider_user_id');
        });

        Schema::create('social_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('social_account_id')
                ->constrained('social_accounts')
                ->onDelete('cascade');
            $table->string('page_id')->index();
            $table->string('page_name');
            $table->text('page_access_token');
            $table->string('category')->nullable();
            $table->timestamps();

            $table->unique(['social_account_id', 'page_id']);
        });

        Schema::create('instagram_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('social_account_id')
                ->constrained('social_accounts')
                ->onDelete('cascade');
            $table->string('page_id')->index(); // linked page_id from social_pages
            $table->string('instagram_business_id')->index();
            $table->string('username');
            $table->string('profile_picture')->nullable();
            $table->timestamps();

            $table->unique(['social_account_id', 'instagram_business_id'], 'ig_account_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_accounts');
        Schema::dropIfExists('social_pages');
        Schema::dropIfExists('social_accounts');
    }
};
