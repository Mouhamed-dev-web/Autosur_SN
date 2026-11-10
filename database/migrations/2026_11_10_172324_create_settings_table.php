<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('value');
            $table->timestamps();
        });

        // Valeurs par défaut (présentes aussi en production)
        $now = now();
        DB::table('settings')->insert([
            ['name' => 'technical_visit_fee', 'label' => 'Frais de service visite technique (FCFA, entre 1500 et 3000)', 'value' => '2000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'technical_visit_validity_months', 'label' => 'Durée de validité d\'une visite technique (mois)', 'value' => '12', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'channel_mail_enabled', 'label' => 'Envoi des alertes par e-mail (1 = activé, 0 = désactivé)', 'value' => '1', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'channel_sms_enabled', 'label' => 'Envoi des alertes par SMS (1 = activé, 0 = désactivé)', 'value' => '1', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};