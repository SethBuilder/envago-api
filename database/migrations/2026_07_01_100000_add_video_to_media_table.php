<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add poster_url column for video thumbnail
        Schema::table('media', function (Blueprint $table) {
            $table->text('poster_url')->nullable()->after('url');
        });

        // Extend the type enum to include 'video' (MySQL only — SQLite stores enums as TEXT)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE media MODIFY COLUMN type ENUM('cover', 'gallery', 'video') NOT NULL DEFAULT 'gallery'");
        }
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('poster_url');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE media MODIFY COLUMN type ENUM('cover', 'gallery') NOT NULL DEFAULT 'gallery'");
        }
    }
};
