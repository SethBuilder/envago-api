<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['affiliate', 'lead']);
            $table->string('destination')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();

            $table->index(['activity_id', 'type']);
            $table->index(['destination', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_clicks');
    }
};
