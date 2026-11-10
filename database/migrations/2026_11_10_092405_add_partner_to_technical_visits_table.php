<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technical_visits', function (Blueprint $table) {
            $table->foreignId('partner_id')->nullable()->after('vehicle_id')
                ->constrained('partners')->nullOnDelete();
            $table->index(['partner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('technical_visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('partner_id');
        });
    }
};