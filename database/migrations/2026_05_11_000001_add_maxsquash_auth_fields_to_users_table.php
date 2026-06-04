<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('role')->nullable()->after('password');
            $table->string('status')->default('otp_pending')->after('role');
            $table->boolean('otp_verified')->default(false)->after('status');

            $table->string('api_access_token')->nullable()->after('remember_token');
            $table->string('api_refresh_token')->nullable()->after('api_access_token');

            $table->string('club_name')->nullable()->after('otp_verified');
            $table->string('owner_manager_name')->nullable()->after('club_name');
            $table->string('address')->nullable()->after('owner_manager_name');
            $table->string('city')->nullable()->after('address');
            $table->unsignedInteger('number_of_courts')->nullable()->after('city');
            $table->string('working_hours')->nullable()->after('number_of_courts');
            $table->string('club_logo')->nullable()->after('working_hours');

            $table->string('profile_image')->nullable()->after('club_logo');
            $table->date('dob')->nullable()->after('profile_image');
            $table->string('gender')->nullable()->after('dob');
            $table->string('playing_level')->nullable()->after('gender');
            $table->string('primary_hand')->nullable()->after('playing_level');
            $table->text('bio')->nullable()->after('primary_hand');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'status',
                'otp_verified',
                'api_access_token',
                'api_refresh_token',
                'club_name',
                'owner_manager_name',
                'address',
                'city',
                'number_of_courts',
                'working_hours',
                'club_logo',
                'profile_image',
                'dob',
                'gender',
                'playing_level',
                'primary_hand',
                'bio',
            ]);
        });
    }
};
