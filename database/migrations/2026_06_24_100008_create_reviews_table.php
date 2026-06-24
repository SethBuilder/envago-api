<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name');
            $table->string('author_avatar_url')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->enum('source', ['scraped', 'user_submitted'])->default('user_submitted');
            $table->string('source_url')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('reviews'); }
};
