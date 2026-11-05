<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sos_request_declines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sos_request_id')->constrained('sos_requests')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sos_request_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sos_request_declines');
    }
};