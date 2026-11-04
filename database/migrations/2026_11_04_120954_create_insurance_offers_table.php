<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_offers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 120);   // sera relié à un partenaire à l'étape 10
            $table->string('name', 120);
            $table->string('cover_type', 20);
            $table->unsignedInteger('base_premium');            // FCFA / an
            $table->unsignedInteger('per_cv_premium')->default(0); // FCFA / an / CV
            $table->decimal('value_rate', 5, 2)->default(0);    // % de la valeur du véhicule / an
            $table->decimal('commission_rate', 4, 2);           // % apporteur d'affaires (5 à 12)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['cover_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_offers');
    }
};