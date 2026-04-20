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
        Schema::create('fraud_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable(); // Store form data
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('reason'); // 'rate_limit', 'duplicate', 'honeypot', 'suspicious'
            $table->boolean('blocked')->default(true);
            $table->timestamps();
            
            $table->index(['ip_address', 'created_at']);
            $table->index(['email', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fraud_attempts');
    }
};
