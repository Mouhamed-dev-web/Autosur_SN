<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('payable');                      // ce qui est payé (visite technique, etc.)
            $table->string('provider', 20);                 // wave, orange_money
            $table->string('gateway', 20);                  // simulation, plus tard wave, orange_money
            $table->string('reference')->unique();          // notre référence
            $table->string('provider_ref')->nullable();     // référence côté opérateur
            $table->unsignedInteger('amount');              // FCFA, figé côté serveur
            $table->string('currency', 3)->default('XOF');
            $table->string('status', 20)->default('pending');
            $table->string('event_id')->nullable();         // dernier événement webhook traité
            $table->json('payload')->nullable();            // réponse brute, pour l'audit
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};