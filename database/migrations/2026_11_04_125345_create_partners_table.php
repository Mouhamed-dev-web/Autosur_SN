<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('type', 20);                          // garage, depanneur, assurance
            $table->string('company_name', 120);
            $table->string('contact_phone', 20);
            $table->boolean('is_validated')->default(false);     // validé par l'admin (étape 11)
            $table->boolean('is_available')->default(false);    // dépanneur prêt à recevoir des SOS
            $table->decimal('base_lat', 10, 7)->nullable();
            $table->decimal('base_lng', 10, 7)->nullable();
            $table->unsignedSmallInteger('radius_km')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};