<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Track which bookings have been paid out
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('payout_id')->nullable()->constrained('payouts')->nullOnDelete();
        });

        // Payouts now represent a batch payment for a schedule, not a single booking
        Schema::table('payouts', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('booking_count')->default(0);
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn(['schedule_id', 'booking_count']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['payout_id']);
            $table->dropColumn('payout_id');
        });
    }
};
