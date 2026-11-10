<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->morphs('rateable');                 // visite technique ou SOS
            $table->unsignedTinyInteger('score');       // 1 à 5
            $table->string('comment', 500)->nullable();
            $table->boolean('is_hidden')->default(false); // modération (étape 11)
            $table->timestamps();

            $table->unique(['user_id', 'rateable_type', 'rateable_id']);
            $table->index(['partner_id', 'is_hidden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};