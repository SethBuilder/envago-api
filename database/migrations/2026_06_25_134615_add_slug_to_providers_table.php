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
        Schema::table('providers', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('business_name');
        });

        // Backfill slugs for existing providers
        foreach (\App\Models\Provider::all() as $provider) {
            $provider->update(['slug' => \Illuminate\Support\Str::slug($provider->business_name)]);
        }
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
