<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('cover_type', 20);
            $table->unsignedTinyInteger('fiscal_power');       // CV
            $table->unsignedInteger('vehicle_value')->nullable(); // FCFA
            $table->unsignedTinyInteger('duration_months');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_quotes');
    }
};