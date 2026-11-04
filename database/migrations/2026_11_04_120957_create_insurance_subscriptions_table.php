<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_quote_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_offer_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('requested');
            $table->unsignedInteger('premium_amount');          // FCFA, figé
            $table->decimal('commission_rate', 4, 2);           // figé
            $table->unsignedInteger('commission_amount');       // FCFA, figé
            $table->timestamp('consented_at');                  // partage des coordonnées accepté
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_subscriptions');
    }
};