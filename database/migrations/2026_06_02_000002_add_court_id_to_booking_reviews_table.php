<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_reviews', function (Blueprint $table) {
            $table->foreignId('court_id')
                ->nullable()
                ->after('club_id')
                ->constrained('courts')
                ->nullOnDelete();
        });

        $reviewRows = DB::table('booking_reviews')
            ->join('bookings', 'booking_reviews.booking_id', '=', 'bookings.id')
            ->select('booking_reviews.id', 'bookings.court_id')
            ->get();

        foreach ($reviewRows as $row) {
            DB::table('booking_reviews')
                ->where('id', $row->id)
                ->update(['court_id' => $row->court_id]);
        }
    }

    public function down(): void
    {
        Schema::table('booking_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('court_id');
        });
    }
};
