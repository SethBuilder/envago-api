<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_data', function (Blueprint $table) {
            $table->id();
            $table->string('destination');
            $table->enum('source', ['youtube', 'apify_serp', 'apify_instagram'])->default('youtube');
            $table->json('raw_data');
            $table->boolean('synthesized')->default(false);
            $table->timestamps();

            $table->index(['destination', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_data');
    }
};
