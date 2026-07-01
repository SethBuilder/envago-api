<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('pricing_type', ['per_person', 'per_unit'])->default('per_person')->after('price');
            $table->string('pricing_unit', 30)->nullable()->after('pricing_type');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'pricing_unit']);
        });
    }
};
