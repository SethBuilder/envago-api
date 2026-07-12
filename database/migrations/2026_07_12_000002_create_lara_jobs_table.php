<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lara_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_id')->index();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending')->index();
            $table->text('user_message');
            $table->longText('result_message')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lara_jobs');
    }
};
