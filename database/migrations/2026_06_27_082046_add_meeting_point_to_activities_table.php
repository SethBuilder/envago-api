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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('meeting_point')->nullable()->after('state');
            $table->decimal('meeting_point_lat', 10, 7)->nullable()->after('meeting_point');
            $table->decimal('meeting_point_lng', 10, 7)->nullable()->after('meeting_point_lat');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['meeting_point', 'meeting_point_lat', 'meeting_point_lng']);
        });
    }
};
