<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->timestamp('lara_onboarded_at')->nullable()->after('first_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('lara_onboarded_at');
        });
    }
};
