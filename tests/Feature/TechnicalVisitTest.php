<?php

namespace Tests\Feature;

use App\Enums\VisitStatus;
use App\Models\TechnicalVisit;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\TechnicalVisitService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalVisitTest extends TestCase
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
            'year' => 2018, 'insurance_status' => 'active',
        ]);
    }

    /** Lundi de la semaine prochaine à 10h : toujours un créneau valide. */
    private function validSlot(): string
    {
        return now()->addWeek()->startOfWeek()->setTime(10, 0)->format('Y-m-d\TH:i');
    }

    public function test_un_client_peut_demander_un_rendez_vous(): void
    {
        $user = $this->client();
        $vehicle = $this->vehicleFor($user);

        $this->actingAs($user)
            ->post(route('client.visits.store'), [
                'vehicle_id' => $vehicle->id,
                'scheduled_at' => $this->validSlot(),
            ])
            ->assertRedirect(route('client.visits.index'));

        $visit = $user->technicalVisits()->first();
        $this->assertSame(VisitStatus::REQUESTED, $visit->status);
        $this->assertGreaterThanOrEqual(1500, $visit->fee_amount);
        $this->assertLessThanOrEqual(3000, $visit->fee_amount);
    }

    public function test_les_frais_restent_dans_la_fourchette(): void
    {
        $setting = \App\Models\Setting::where('name', 'technical_visit_fee')->firstOrFail();

        $setting->update(['value' => '10000']);
        $this->assertSame(3000, app(TechnicalVisitService::class)->fee());

        $setting->update(['value' => '100']);
        $this->assertSame(1500, app(TechnicalVisitService::class)->fee());
    }

    public function test_impossible_pour_le_vehicule_d_un_autre(): void
    {
        $a = $this->client();
        $b = $this->client();
        $vehicle = $this->vehicleFor($b);

        $this->actingAs($a)
            ->post(route('client.visits.store'), [
                'vehicle_id' => $vehicle->id,
                'scheduled_at' => $this->validSlot(),
            ])
            ->assertSessionHasErrors('vehicle_id');
    }

    public function test_une_seule_visite_active_par_vehicule(): void
    {
        $user = $this->client();
        $vehicle = $this->vehicleFor($user);
        $payload = ['vehicle_id' => $vehicle->id, 'scheduled_at' => $this->validSlot()];

        $this->actingAs($user)->post(route('client.visits.store'), $payload)->assertSessionHasNoErrors();
        $this->actingAs($user)->post(route('client.visits.store'), $payload)->assertSessionHasErrors('vehicle_id');

        $this->assertSame(1, $user->technicalVisits()->count());
    }

    public function test_dimanche_et_heures_de_nuit_refuses(): void
    {
        $user = $this->client();
        $vehicle = $this->vehicleFor($user);

        $sunday = now()->next('Sunday')->setTime(10, 0)->format('Y-m-d\TH:i');
        $night = now()->addWeek()->startOfWeek()->setTime(22, 0)->format('Y-m-d\TH:i');
        $past = now()->subDay()->setTime(10, 0)->format('Y-m-d\TH:i');

        foreach ([$sunday, $night, $past] as $slot) {
            $this->actingAs($user)
                ->post(route('client.visits.store'), ['vehicle_id' => $vehicle->id, 'scheduled_at' => $slot])
                ->assertSessionHasErrors('scheduled_at');
        }
    }

    public function test_annulation_par_le_proprietaire_seulement(): void
    {
        $a = $this->client();
        $b = $this->client();
        $service = app(TechnicalVisitService::class);
        $visit = $service->create($b, $this->vehicleFor($b), now()->addDays(3));

        $this->actingAs($a)->post(route('client.visits.cancel', $visit))->assertForbidden();
        $this->assertSame(VisitStatus::REQUESTED, $visit->fresh()->status);

        $this->actingAs($b)->post(route('client.visits.cancel', $visit))->assertRedirect();
        $this->assertSame(VisitStatus::CANCELLED, $visit->fresh()->status);
    }

    public function test_visite_effectuee_met_a_jour_le_vehicule(): void
    {
        $user = $this->client();
        $vehicle = $this->vehicleFor($user);
        $service = app(TechnicalVisitService::class);

        $visit = $service->create($user, $vehicle, now()->addDays(3));
        $service->confirm($visit);
        $service->complete($visit);

        $vehicle->refresh();
        $this->assertSame(now()->toDateString(), $vehicle->technical_visit_last_at->toDateString());
        $this->assertSame(
            now()->addMonths(config('autosur.technical_visit.validity_months'))->toDateString(),
            $vehicle->technical_visit_expires_at->toDateString(),
        );
        $this->assertSame(VisitStatus::COMPLETED, $visit->fresh()->status);
    }

    public function test_on_ne_peut_pas_terminer_une_visite_non_confirmee(): void
    {
        $user = $this->client();
        $service = app(TechnicalVisitService::class);
        $visit = $service->create($user, $this->vehicleFor($user), now()->addDays(3));

        $this->expectException(\DomainException::class);
        $service->complete($visit);
    }

    public function test_la_liste_ne_montre_que_ses_visites(): void
    {
        $a = $this->client();
        $b = $this->client();
        $service = app(TechnicalVisitService::class);
        $service->create($a, $this->vehicleFor($a, 'DK-1111-AA'), now()->addDays(3));
        $service->create($b, $this->vehicleFor($b, 'DK-2222-BB'), now()->addDays(3));

        $this->actingAs($a)->get(route('client.visits.index'))
            ->assertOk()
            ->assertSee('DK-1111-AA')
            ->assertDontSee('DK-2222-BB');

        $this->assertSame(2, TechnicalVisit::count());
    }
}