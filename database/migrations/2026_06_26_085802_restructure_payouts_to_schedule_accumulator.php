<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bookings', 'payout_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreignId('payout_id')->nullable()->constrained('payouts')->nullOnDelete();
            });
        }

        Schema::table('payouts', function (Blueprint $table) {
            if (!Schema::hasColumn('payouts', 'schedule_id')) {
                $table->foreignId('schedule_id')->nullable()->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('payouts', 'booking_count')) {
                $table->unsignedInteger('booking_count')->default(0);
            }
            if (Schema::hasColumn('payouts', 'booking_id')) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('payouts', function (Blueprint $table) {
            if (Schema::hasColumn('payouts', 'schedule_id')) {
                $table->dropForeign(['schedule_id']);
                $table->dropColumn(['schedule_id', 'booking_count']);
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'payout_id')) {
                $table->dropForeign(['payout_id']);
                $table->dropColumn('payout_id');
            }
        });
    }
};
