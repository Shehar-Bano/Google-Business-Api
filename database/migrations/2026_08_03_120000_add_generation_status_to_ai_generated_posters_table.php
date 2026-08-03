<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_generated_posters', function (Blueprint $table) {
            $table->string('generation_status')->default('queued')->after('status');
            $table->text('generation_error')->nullable()->after('generation_status');
            $table->index('generation_status');
        });
    }

    public function down(): void
    {
        Schema::table('ai_generated_posters', function (Blueprint $table) {
            $table->dropIndex(['generation_status']);
            $table->dropColumn(['generation_status', 'generation_error']);
        });
    }
};
