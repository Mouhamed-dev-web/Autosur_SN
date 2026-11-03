<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plate', 20)->unique();          // immatriculation
            $table->string('brand', 60);
            $table->string('model', 60);
            $table->unsignedSmallInteger('year');
            $table->string('color', 40)->nullable();
            $table->string('vin', 17)->nullable()->unique(); // numéro de châssis
            $table->string('insurance_status', 20)->default('unknown');
            $table->date('insurance_expires_at')->nullable();
            $table->date('technical_visit_last_at')->nullable();
            $table->date('technical_visit_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};