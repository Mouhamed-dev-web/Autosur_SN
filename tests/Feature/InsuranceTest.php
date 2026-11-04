<?php

namespace Tests\Feature;

use App\Enums\CoverType;
use App\Enums\SubscriptionStatus;
use App\Models\InsuranceOffer;
use App\Models\InsuranceQuote;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\InsuranceService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InsuranceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function client(): User
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        return $user;
    }

    private function vehicleFor(User $user, string $plate = 'DK-1234-AB'): Vehicle
    {
        return $user->vehicles()->create([
            'plate' => $plate, 'brand' => 'Toyota', 'model' => 'Corolla',
            'year' => 2018, 'insurance_status' => 'unknown',
        ]);
    }

    private function offer(array $override = []): InsuranceOffer
    {
        return InsuranceOffer::create(array_merge([
            'company_name' => 'Assureur Test',
            'name' => 'Tiers',
            'cover_type' => 'tiers',
            'base_premium' => 50000,
            'per_cv_premium' => 4000,
            'value_rate' => 0,
            'commission_rate' => 10,
            'is_active' => true,
        ], $override));
    }

    private function quote(User $user, array $override = []): InsuranceQuote
    {
        return $user->insuranceQuotes()->create(array_merge([
            'vehicle_id' => $this->vehicleFor($user)->id,
            'cover_type' => 'tiers',
            'fiscal_power' => 6,
            'duration_months' => 12,
        ], $override));
    }

    public function test_calcul_de_la_prime(): void
    {
        $service = app(InsuranceService::class);
        $offer = $this->offer();
        $user = $this->client();

        $this->assertSame(74000, $service->premium($offer, $this->quote($user)));
        $this->assertSame(37000, $service->premium($offer, $this->quote($user, [
            'vehicle_id' => $this->vehicleFor($user, 'DK-2222-BB')->id,
            'duration_months' => 6,
        ])));
    }

    public function test_commission_bornee_entre_5_et_12_pourcent(): void
    {
        $service = app(InsuranceService::class);

        $this->assertSame(12.0, $service->commissionRate($this->offer(['commission_rate' => 20])));
        $this->assertSame(5.0, $service->commissionRate($this->offer(['commission_rate' => 1])));
        $this->assertSame(10000, $service->commission(100000, 10.0));
    }

    public function test_un_client_demande_un_devis_et_voit_les_offres_triees(): void
    {
        $user = $this->client();
        $vehicle = $this->vehicleFor($user);
        $this->offer(['company_name' => 'Chere', 'base_premium' => 90000]);
        $this->offer(['company_name' => 'Pas chere', 'base_premium' => 30000]);

        $this->actingAs($user)->post(route('client.insurance.store'), [
            'vehicle_id' => $vehicle->id,
            'cover_type' => 'tiers',
            'fiscal_power' => 6,
            'duration_months' => 12,
        ])->assertRedirect();

        $quote = $user->insuranceQuotes()->first();
        $this->assertNotNull($quote);

        $offers = app(InsuranceService::class)->offersFor($quote);
        $this->assertSame('Pas chere', $offers->first()['offer']->company_name);
    }

    public function test_valeur_obligatoire_pour_tous_risques(): void
    {
        $user = $this->client();
        $vehicle = $this->vehicleFor($user);

        $this->actingAs($user)->post(route('client.insurance.store'), [
            'vehicle_id' => $vehicle->id,
            'cover_type' => CoverType::TOUS_RISQUES->value,
            'fiscal_power' => 6,
            'duration_months' => 12,
        ])->assertSessionHasErrors('vehicle_value');
    }

    public function test_impossible_de_demander_un_devis_pour_le_vehicule_d_un_autre(): void
    {
        $a = $this->client();
        $b = $this->client();
        $vehicle = $this->vehicleFor($b);

        $this->actingAs($a)->post(route('client.insurance.store'), [
            'vehicle_id' => $vehicle->id,
            'cover_type' => 'tiers',
            'fiscal_power' => 6,
            'duration_months' => 12,
        ])->assertSessionHasErrors('vehicle_id');
    }

    public function test_souscription_avec_consentement_et_commission_figee(): void
    {
        $user = $this->client();
        $offer = $this->offer();
        $quote = $this->quote($user);

        $this->actingAs($user)
            ->post(route('client.insurance.subscribe', [$quote, $offer]))
            ->assertSessionHasErrors('consent');

        $this->actingAs($user)
            ->post(route('client.insurance.subscribe', [$quote, $offer]), ['consent' => '1'])
            ->assertSessionHasNoErrors();

        $sub = $quote->subscription()->first();
        $this->assertSame(SubscriptionStatus::REQUESTED, $sub->status);
        $this->assertSame(74000, $sub->premium_amount);
        $this->assertSame(7400, $sub->commission_amount);
        $this->assertNotNull($sub->consented_at);

        // pas de seconde souscription sur le même devis
        $this->actingAs($user)
            ->post(route('client.insurance.subscribe', [$quote, $offer]), ['consent' => '1'])
            ->assertSessionHasErrors('subscribe');
    }

    public function test_un_autre_client_ne_peut_ni_voir_ni_souscrire(): void
    {
        $a = $this->client();
        $b = $this->client();
        $offer = $this->offer();
        $quote = $this->quote($b);

        $this->actingAs($a)->get(route('client.insurance.show', $quote))->assertForbidden();
        $this->actingAs($a)
            ->post(route('client.insurance.subscribe', [$quote, $offer]), ['consent' => '1'])
            ->assertForbidden();
        $this->assertNull($quote->subscription()->first());
    }

    public function test_l_activation_met_a_jour_le_vehicule(): void
    {
        $user = $this->client();
        $quote = $this->quote($user);
        $service = app(InsuranceService::class);

        $sub = $service->subscribe($quote, $this->offer());
        $service->activate($sub);

        $vehicle = $quote->vehicle->fresh();
        $this->assertSame('active', $vehicle->insurance_status->value);
        $this->assertSame(
            now()->addMonths(12)->toDateString(),
            $vehicle->insurance_expires_at->toDateString(),
        );
        $this->assertSame(SubscriptionStatus::ACTIVE, $sub->fresh()->status);
    }
}