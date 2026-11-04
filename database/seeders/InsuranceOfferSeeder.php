<?php

namespace Database\Seeders;

use App\Enums\CoverType;
use App\Models\InsuranceOffer;
use Illuminate\Database\Seeder;

class InsuranceOfferSeeder extends Seeder
{
    /**
     * Offres FICTIVES pour tester. Les vrais barèmes seront saisis
     * par les assureurs partenaires (back-office, étape 11).
     */
    public function run(): void
    {
        $companies = [
            ['Assureur Démo A', 1.00, 8.0],
            ['Assureur Démo B', 1.12, 10.0],
        ];

        $covers = [
            [CoverType::TIERS, 40000, 3500, 0],
            [CoverType::TIERS_PLUS, 60000, 4500, 0.8],
            [CoverType::TOUS_RISQUES, 80000, 5000, 2.5],
        ];

        foreach ($companies as [$company, $factor, $commission]) {
            foreach ($covers as [$cover, $base, $perCv, $valueRate]) {
                InsuranceOffer::updateOrCreate(
                    ['company_name' => $company, 'cover_type' => $cover->value],
                    [
                        'name' => $cover->label(),
                        'base_premium' => (int) round($base * $factor),
                        'per_cv_premium' => (int) round($perCv * $factor),
                        'value_rate' => $valueRate,
                        'commission_rate' => $commission,
                        'description' => 'Offre de démonstration (tarif fictif).',
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}