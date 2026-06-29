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
        Schema::table('lara_conversations', function (Blueprint $table) {
            $table->string('uuid', 100)->nullable()->index()->after('session_id');
            $table->string('whatsapp', 30)->nullable()->index()->after('uuid');
            $table->string('email', 255)->nullable()->index()->after('whatsapp');
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('lara_conversations', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropColumn(['uuid', 'whatsapp', 'email', 'provider_id']);
        });
    }
};
