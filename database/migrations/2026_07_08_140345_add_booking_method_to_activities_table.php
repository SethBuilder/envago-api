<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('booking_method', ['envago', 'affiliate', 'lead'])->default('envago')->after('is_active');
            $table->string('link_url')->nullable()->after('booking_method');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['booking_method', 'link_url']);
        });
    }
};
