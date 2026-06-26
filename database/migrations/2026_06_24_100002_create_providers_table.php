<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('business_name');
            $table->string('slug')->nullable()->unique();
            $table->string('logo_url')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('pix_key')->nullable();
            $table->json('bank_details')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('providers'); }
};
