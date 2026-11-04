<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('status', 20)->default('requested');
            $table->unsignedInteger('fee_amount');          // FCFA, figé à la création
            $table->timestamp('fee_paid_at')->nullable();   // renseigné à l'étape 9 (paiements)
            $table->string('notes', 500)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_visits');
    }
};