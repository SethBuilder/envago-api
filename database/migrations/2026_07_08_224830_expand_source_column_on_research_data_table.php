<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE research_data MODIFY source VARCHAR(50) NOT NULL DEFAULT 'youtube'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE research_data MODIFY source ENUM('youtube','apify_serp','apify_instagram') NOT NULL DEFAULT 'youtube'");
    }
};
