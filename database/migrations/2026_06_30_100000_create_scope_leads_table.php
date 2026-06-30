<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scope_leads', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('business_name')->nullable();
            $table->string('location')->nullable();
            $table->string('offering');
            $table->string('price_range')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('summary');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scope_leads');
    }
};
