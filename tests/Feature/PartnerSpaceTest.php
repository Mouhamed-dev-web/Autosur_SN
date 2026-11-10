<?php

namespace Tests\Feature;

use App\Contracts\SmsGatewayInterface;
use App\Enums\SubscriptionStatus;
use App\Enums\VisitStatus;
use App\Models\InsuranceOffer;
use App\Models\InsuranceSubscription;
use App\Models\Partner;
use App\Models\Rating;
use App\Models\TechnicalVisit;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\InsuranceService;
use App\Services\SosService;
use App\Services\TechnicalVisitService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSpaceTest extends TestCase
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

    private function partner(string $type, array $override = []): Partner
    {
        $user = User::factory()->create();
        $user->assignRole($type);

        return Partner::create(array_merge([
            'user_id' => $user->id,
            'type' => $type,
            'company_name' => ucfirst($type).' Test',
            'contact_phone' => '+221770000010',
            'is_validated' => true,
            'is_available' => $type === 'depanneur',
        ], $override));
    }

    private function vehicleFor(User $user, string $plate): Vehicle
    {
        return $user->vehicles()->create([
            'plate' => $plate, 'brand' => 'Toyota', 'model' => 'Corolla',
            'year' => 2018, 'insurance_status' => 'unknown',
        ]);
    }

    private function visitFor(User $user, string $plate, ?Partner $garage = null): TechnicalVisit
    {
        return app(TechnicalVisitService::class)
            ->create($user, $this->vehicleFor($user, $plate), now()->addDays(3), null, $garage);
    }

    private function completedVisit(User $user, Partner $garage, string $plate = 'DK-7000-AA'): TechnicalVisit
    {
        $service = app(TechnicalVisitService::class);
        $visit = $this->visitFor($user, $plate, $garage);
        $service->confirmBy($visit, $garage);
        $service->completeBy($visit, $garage);

        return $visit->fresh();
    }

    private function fakeSms(): object
    {
        $sms = new class implements SmsGatewayInterface
        {
            public array $sent = [];

            public function send(string $phone, string $message): bool
            {
                $this->sent[] = [$phone, $message];

                return true;
            }
        };
        $this->app->instance(SmsGatewayInterface::class, $sms);

        return $sms;
    }

    private function registration(array $override = []): array
    {
        return array_merge([
            'type' => 'garage',
            'company_name' => 'Garage Fall',
            'contact_phone' => '77 555 66 78',
            'name' => 'Moussa Fall',
            'phone' => '77 555 66 77',
            'password' => 'motdepasse1',
            'password_confirmation' => 'motdepasse1',
            'terms' => '1',
        ], $override);
    }

    // ---------- Inscription ----------

    public function test_inscription_partenaire_cree_un_compte_non_valide(): void
    {
        $this->fakeSms();

        $this->post(route('partners.register.store'), $this->registration())
            ->assertRedirect(route('otp.show'));

        $user = User::where('phone', '+221775556677')->firstOrFail();
        $this->assertTrue($user->hasRole('garage'));
        $this->assertFalse($user->hasRole('client'));
        $this->assertFalse($user->partner->is_validated);
        $this->assertFalse($user->partner->is_available);
        $this->assertSame('+221775556678', $user->partner->contact_phone);
    }

    public function test_un_type_non_autorise_est_refuse(): void
    {
        $this->fakeSms();

        foreach (['admin', 'client', 'inconnu'] as $type) {
            $this->post(route('partners.register.store'), $this->registration(['type' => $type]))
                ->assertSessionHasErrors('type');
        }

        $this->assertSame(0, User::count());
    }

    public function test_apres_l_otp_le_partenaire_arrive_dans_son_espace(): void
    {
        $sms = $this->fakeSms();
        $this->post(route('partners.register.store'), $this->registration());

        preg_match('/(\d{6})/', $sms->sent[0][1], $m);
        $this->post('/verification', ['code' => $m[1]])->assertRedirect(route('partner.dashboard'));

        $user = User::where('phone', '+221775556677')->firstOrFail();
        $this->actingAs($user)->get(route('partner.dashboard'))
            ->assertRedirect(route('partner.visits.index'));
    }

    // ---------- Garage ----------

    public function test_un_client_ne_choisit_qu_un_garage_valide(): void
    {
        $user = $this->client();
        $vehicle = $this->vehicleFor($user, 'DK-1000-AA');
        $slot = now()->addWeek()->startOfWeek()->setTime(10, 0)->format('Y-m-d\TH:i');

        $pending = $this->partner('garage', ['is_validated' => false]);
        $this->actingAs($user)->post(route('client.visits.store'), [
            'vehicle_id' => $vehicle->id, 'scheduled_at' => $slot, 'partner_id' => $pending->id,
        ])->assertSessionHasErrors('partner_id');

        $garage = $this->partner('garage');
        $this->actingAs($user)->post(route('client.visits.store'), [
            'vehicle_id' => $vehicle->id, 'scheduled_at' => $slot, 'partner_id' => $garage->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame($garage->id, $user->technicalVisits()->first()->partner_id);
    }

    public function test_un_garage_non_valide_ne_peut_pas_confirmer(): void
    {
        $garage = $this->partner('garage', ['is_validated' => false]);
        $visit = $this->visitFor($this->client(), 'DK-2000-AA');

        $this->actingAs($garage->user)->post(route('partner.visits.confirm', $visit))
            ->assertSessionHasErrors('partner');
        $this->assertSame(VisitStatus::REQUESTED, $visit->fresh()->status);
    }

    public function test_le_premier_garage_qui_confirme_gagne(): void
    {
        $first = $this->partner('garage');
        $second = $this->partner('garage');
        $visit = $this->visitFor($this->client(), 'DK-3000-AA');

        $this->actingAs($first->user)->post(route('partner.visits.confirm', $visit))
            ->assertSessionHasNoErrors();
        $this->actingAs($second->user)->post(route('partner.visits.confirm', $visit))
            ->assertForbidden();

        $this->assertSame($first->id, $visit->fresh()->partner_id);
        $this->assertSame(VisitStatus::CONFIRMED, $visit->fresh()->status);
    }

    public function test_une_demande_attribuee_est_invisible_pour_les_autres_garages(): void
    {
        $chosen = $this->partner('garage');
        $other = $this->partner('garage');
        $this->visitFor($this->client(), 'DK-4000-AA', $chosen);

        $this->actingAs($chosen->user)->get(route('partner.visits.index'))->assertSee('DK-4000-AA');
        $this->actingAs($other->user)->get(route('partner.visits.index'))->assertDontSee('DK-4000-AA');
    }

    public function test_deroulement_complet_cote_garage(): void
    {
        $user = $this->client();
        $garage = $this->partner('garage');
        $visit = $this->visitFor($user, 'DK-5000-AA', $garage);

        $this->actingAs($garage->user)->post(route('partner.visits.confirm', $visit))->assertSessionHasNoErrors();
        $this->actingAs($garage->user)->post(route('partner.visits.complete', $visit))->assertSessionHasNoErrors();

        $visit->refresh();
        $this->assertSame(VisitStatus::COMPLETED, $visit->status);
        $this->assertSame(
            now()->addMonths(config('autosur.technical_visit.validity_months'))->toDateString(),
            $visit->vehicle->technical_visit_expires_at->toDateString(),
        );
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_on_ne_termine_pas_la_visite_d_un_autre_garage(): void
    {
        $owner = $this->partner('garage');
        $other = $this->partner('garage');
        $visit = $this->visitFor($this->client(), 'DK-6000-AA', $owner);
        app(TechnicalVisitService::class)->confirmBy($visit, $owner);

        $this->actingAs($other->user)->post(route('partner.visits.complete', $visit))->assertForbidden();
        $this->actingAs($other->user)->post(route('partner.visits.reject', $visit))->assertForbidden();
        $this->assertSame(VisitStatus::CONFIRMED, $visit->fresh()->status);
    }

    public function test_le_refus_du_garage_annule_et_previent_le_client(): void
    {
        $garage = $this->partner('garage');
        $visit = $this->visitFor($this->client(), 'DK-6500-AA', $garage);

        $this->actingAs($garage->user)->post(route('partner.visits.reject', $visit))->assertSessionHasNoErrors();

        $this->assertSame(VisitStatus::CANCELLED, $visit->fresh()->status);
        $this->assertDatabaseCount('notifications', 1);
    }

    // ---------- Accès par rôle ----------

    public function test_les_espaces_sont_cloisonnes_par_role(): void
    {
        $garage = $this->partner('garage');
        $insurer = $this->partner('assurance');

        $this->actingAs($garage->user)->get(route('partner.insurance.index'))->assertForbidden();
        $this->actingAs($insurer->user)->get(route('partner.visits.index'))->assertForbidden();
        $this->actingAs($garage->user)->get(route('partner.sos.index'))->assertForbidden();
        $this->actingAs($this->client())->get(route('partner.visits.index'))->assertForbidden();
        $this->actingAs($garage->user)->get(route('client.vehicles.index'))->assertForbidden();
    }

    // ---------- Assureur ----------

    private function subscriptionFor(Partner $insurer, string $plate): InsuranceSubscription
    {
        $user = $this->client();
        $quote = $user->insuranceQuotes()->create([
            'vehicle_id' => $this->vehicleFor($user, $plate)->id,
            'cover_type' => 'tiers',
            'fiscal_power' => 6,
            'duration_months' => 12,
        ]);
        $offer = InsuranceOffer::create([
            'partner_id' => $insurer->id,
            'company_name' => $insurer->company_name,
            'name' => 'Tiers',
            'cover_type' => 'tiers',
            'base_premium' => 50000,
            'per_cv_premium' => 4000,
            'value_rate' => 0,
            'commission_rate' => 10,
            'is_active' => true,
        ]);

        return app(InsuranceService::class)->subscribe($quote, $offer);
    }

    public function test_l_assureur_ne_voit_que_ses_prospects(): void
    {
        $a = $this->partner('assurance', ['company_name' => 'Assureur A']);
        $b = $this->partner('assurance', ['company_name' => 'Assureur B']);
        $this->subscriptionFor($a, 'DK-8100-AA');
        $this->subscriptionFor($b, 'DK-8200-BB');

        $this->actingAs($a->user)->get(route('partner.insurance.index'))
            ->assertOk()
            ->assertSee('DK-8100-AA')
            ->assertDontSee('DK-8200-BB');
    }

    public function test_l_assureur_active_et_le_vehicule_est_mis_a_jour(): void
    {
        $insurer = $this->partner('assurance');
        $sub = $this->subscriptionFor($insurer, 'DK-8300-AA');

        $this->actingAs($insurer->user)
            ->post(route('partner.insurance.activate', $sub), ['start_date' => now()->toDateString()])
            ->assertSessionHasNoErrors();

        $sub->refresh();
        $this->assertSame(SubscriptionStatus::ACTIVE, $sub->status);
        $this->assertSame('active', $sub->quote->vehicle->fresh()->insurance_status->value);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_un_autre_assureur_ne_peut_ni_activer_ni_refuser(): void
    {
        $owner = $this->partner('assurance');
        $other = $this->partner('assurance');
        $sub = $this->subscriptionFor($owner, 'DK-8400-AA');

        $this->actingAs($other->user)->post(route('partner.insurance.activate', $sub))->assertForbidden();
        $this->actingAs($other->user)->post(route('partner.insurance.decline', $sub))->assertForbidden();
        $this->assertSame(SubscriptionStatus::REQUESTED, $sub->fresh()->status);
    }

    public function test_l_assureur_refuse_et_previent_le_client(): void
    {
        $insurer = $this->partner('assurance');
        $sub = $this->subscriptionFor($insurer, 'DK-8500-AA');

        $this->actingAs($insurer->user)->post(route('partner.insurance.decline', $sub))->assertSessionHasNoErrors();

        $this->assertSame(SubscriptionStatus::CANCELLED, $sub->fresh()->status);
        $this->assertDatabaseCount('notifications', 1);
    }

    // ---------- Notations ----------

    public function test_le_client_note_apres_la_visite_une_seule_fois(): void
    {
        $user = $this->client();
        $garage = $this->partner('garage');
        $visit = $this->completedVisit($user, $garage);

        $this->actingAs($user)
            ->post(route('client.visits.rate', $visit), ['score' => 5, 'comment' => 'Très bon accueil'])
            ->assertSessionHasNoErrors();

        $this->assertSame(5.0, $garage->averageRating());

        $this->actingAs($user)
            ->post(route('client.visits.rate', $visit), ['score' => 1])
            ->assertSessionHasErrors('rating');
        $this->assertSame(1, Rating::count());
    }

    public function test_pas_de_note_avant_la_fin_ni_pour_l_intervention_d_un_autre(): void
    {
        $user = $this->client();
        $other = $this->client();
        $garage = $this->partner('garage');

        $pending = $this->visitFor($user, 'DK-9100-AA', $garage);
        $this->actingAs($user)->post(route('client.visits.rate', $pending), ['score' => 5])
            ->assertSessionHasErrors('rating');

        $done = $this->completedVisit($user, $garage, 'DK-9200-AA');
        $this->actingAs($other)->post(route('client.visits.rate', $done), ['score' => 1])
            ->assertForbidden();
        $this->assertSame(0, Rating::count());
    }

    public function test_note_hors_limites_refusee(): void
    {
        $user = $this->client();
        $visit = $this->completedVisit($user, $this->partner('garage'));

        foreach ([0, 6] as $score) {
            $this->actingAs($user)->post(route('client.visits.rate', $visit), ['score' => $score])
                ->assertSessionHasErrors('score');
        }
    }

    public function test_les_avis_masques_ne_comptent_pas_dans_la_moyenne(): void
    {
        $garage = $this->partner('garage');
        $a = $this->client();
        $b = $this->client();
        $v1 = $this->completedVisit($a, $garage, 'DK-9300-AA');
        $v2 = $this->completedVisit($b, $garage, 'DK-9400-AA');

        foreach ([[$a, $v1, 5, false], [$b, $v2, 1, true]] as [$user, $visit, $score, $hidden]) {
            Rating::create([
                'user_id' => $user->id, 'partner_id' => $garage->id,
                'rateable_type' => $visit->getMorphClass(), 'rateable_id' => $visit->id,
                'score' => $score, 'is_hidden' => $hidden,
            ]);
        }

        $this->assertSame(5.0, $garage->averageRating());
    }

    public function test_le_client_note_son_depanneur(): void
    {
        $user = $this->client();
        $towing = $this->partner('depanneur');

        $sos = app(SosService::class)->create($user, [
            'vehicle_id' => $this->vehicleFor($user, 'DK-9500-AA')->id,
            'problem' => 'battery', 'lat' => 14.70, 'lng' => -17.45,
        ]);
        $service = app(SosService::class);
        $service->accept($sos, $towing);
        $service->arrive($sos->fresh(), $towing);
        $service->complete($sos->fresh(), $towing);

        $this->actingAs($user)->post(route('client.sos.rate', $sos), ['score' => 4])
            ->assertSessionHasNoErrors();
        $this->assertSame(4.0, $towing->averageRating());
    }
}