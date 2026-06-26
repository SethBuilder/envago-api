<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->unsignedInteger('booking_count')->default(0);
            $table->string('currency', 3)->default('BRL');
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('confirmation_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('payout_id')->references('id')->on('payouts')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['payout_id']);
        });
        Schema::dropIfExists('payouts');
    }
};
