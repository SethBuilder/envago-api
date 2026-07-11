<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->boolean('claimed')->default(true)->after('status');
        });

        // Existing providers are all claimed (signed up themselves)
        DB::table('providers')->update(['claimed' => true]);
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('claimed');
        });
    }
};
