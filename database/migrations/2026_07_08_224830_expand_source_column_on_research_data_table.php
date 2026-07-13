<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_data', function (Blueprint $table) {
            $table->string('source', 50)->default('youtube')->change();
        });
    }

    public function down(): void
    {
        Schema::table('research_data', function (Blueprint $table) {
            $table->string('source', 50)->default('youtube')->change();
        });
    }
};
