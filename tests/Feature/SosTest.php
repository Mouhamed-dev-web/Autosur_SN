<?php

namespace Tests\Feature;

use App\Enums\SosStatus;
use App\Models\Partner;
use App\Models\SosRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\SosService;
use App\Support\Geo;
use Database\Seeders\RoleSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SosTest extends TestCase
{
    use RefreshDatabase;

    // Dakar (centre) : base des dépanneurs de test
    private const DAKAR = ['lat' => 14.6937, 'lng' => -17.4441];

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

    private function vehicleFor(User $user): Vehicle
    {
        return $user->vehicles()->create([
            'plate' => 'DK-'.random_int(1000, 9999).'-AB', 'brand' => 'Toyota', 'model' => 'Corolla',
            'year' => 2018, 'insurance_status' => 'active',
        ]);
    }

    private function depanneur(array $override = []): Partner
    {
        $user = User::factory()->create();
        $user->assignRole('depanneur');

        return Partner::create(array_merge([
            'user_id' => $user->id,
            'type' => 'depanneur',
            'company_name' => 'Dépannage Test',
            'contact_phone' => '+221770000002',
            'is_validated' => true,
            'is_available' => true,
            'base_lat' => self::DAKAR['lat'],
            'base_lng' => self::DAKAR['lng'],
            'radius_km' => 30,
        ], $override));
    }

    private function sos(?User $user = null, array $override = []): SosRequest
    {
        $user ??= $this->client();

        return app(SosService::class)->create($user, array_merge([
            'vehicle_id' => $this->vehicleFor($user)->id,
            'problem' => 'battery',
            'lat' => 14.70,
            'lng' => -17.45,
        ], $override));
    }

    public function test_un_client_envoie_une_demande_avec_position(): void
    {
        $user = $this->client();

        $this->actingAs($user)->post(route('client.sos.store'), [
            'vehicle_id' => $this->vehicleFor($user)->id,
            'problem' => 'tire',
            'lat' => '14.7000000',
            'lng' => '-17.4500000',
        ])->assertRedirect();

        $sos = $user->sosRequests()->first();
        $this->assertSame(SosStatus::PENDING, $sos->status);
    }

    public function test_position_ou_lieu_obligatoire(): void
    {
        $user = $this->client();
        $payload = ['vehicle_id' => $this->vehicleFor($user)->id, 'problem' => 'fuel'];

        $this->actingAs($user)->post(route('client.sos.store'), $payload)
            ->assertSessionHasErrors('location_text');

        $this->actingAs($user)->post(route('client.sos.store'), $payload + ['location_text' => 'VDN, station Total'])
            ->assertSessionHasNoErrors();
    }

    public function test_une_seule_demande_ouverte_par_client(): void
    {
        $user = $this->client();
        $this->sos($user);

        $this->actingAs($user)->post(route('client.sos.store'), [
            'vehicle_id' => $user->vehicles()->first()->id,
            'problem' => 'engine',
            'location_text' => 'Almadies',
        ])->assertSessionHasErrors('sos');

        $this->assertSame(1, $user->sosRequests()->count());
    }

    public function test_impossible_avec_le_vehicule_d_un_autre(): void
    {
        $a = $this->client();
        $b = $this->client();

        $this->actingAs($a)->post(route('client.sos.store'), [
            'vehicle_id' => $this->vehicleFor($b)->id,
            'problem' => 'engine',
            'location_text' => 'Almadies',
        ])->assertSessionHasErrors('vehicle_id');
    }

    public function test_le_depanneur_ne_voit_que_les_demandes_de_son_rayon(): void
    {
        $partner = $this->depanneur();
        $near = $this->sos();
        // Saint-Louis : environ 250 km de Dakar
        $this->sos(null, ['lat' => 16.0326, 'lng' => -16.4818]);

        $rows = app(SosService::class)->availableFor($partner);

        $this->assertCount(1, $rows);
        $this->assertSame($near->id, $rows->first()['request']->id);
    }

    public function test_un_depanneur_indisponible_ou_non_valide_ne_voit_rien(): void
    {
        $this->sos();
        $service = app(SosService::class);

        $this->assertCount(0, $service->availableFor($this->depanneur(['is_available' => false])));
        $this->assertCount(0, $service->availableFor($this->depanneur(['is_validated' => false])));
    }

    public function test_un_depanneur_non_valide_ne_peut_pas_accepter(): void
    {
        $sos = $this->sos();

        $this->expectException(DomainException::class);
        app(SosService::class)->accept($sos, $this->depanneur(['is_validated' => false]));
    }

    public function test_le_premier_qui_accepte_gagne(): void
    {
        $sos = $this->sos();
        $first = $this->depanneur();
        $second = $this->depanneur();

        $this->actingAs($first->user)->post(route('partner.sos.accept', $sos))
            ->assertSessionHasNoErrors();

        $this->actingAs($second->user)->post(route('partner.sos.accept', $sos))
            ->assertSessionHasErrors('sos');

        $sos->refresh();
        $this->assertSame(SosStatus::ACCEPTED, $sos->status);
        $this->assertSame($first->id, $sos->partner_id);
    }

    public function test_un_refus_masque_la_demande_pour_ce_depanneur_seulement(): void
    {
        $sos = $this->sos();
        $a = $this->depanneur();
        $b = $this->depanneur();

        $this->actingAs($a->user)->post(route('partner.sos.decline', $sos))->assertRedirect();

        $service = app(SosService::class);
        $this->assertCount(0, $service->availableFor($a));
        $this->assertCount(1, $service->availableFor($b));
    }

    public function test_deroulement_complet_de_l_intervention(): void
    {
        $sos = $this->sos();
        $partner = $this->depanneur();

        $this->actingAs($partner->user);
        $this->post(route('partner.sos.accept', $sos));
        $this->post(route('partner.sos.arrive', $sos))->assertSessionHasNoErrors();
        $this->post(route('partner.sos.complete', $sos))->assertSessionHasNoErrors();

        $sos->refresh();
        $this->assertSame(SosStatus::COMPLETED, $sos->status);
        $this->assertNotNull($sos->accepted_at);
        $this->assertNotNull($sos->arrived_at);
        $this->assertNotNull($sos->completed_at);
        $this->assertDatabaseCount('notifications', 3); // acceptée, arrivée, terminée
    }

    public function test_on_ne_peut_pas_terminer_sans_etre_arrive(): void
    {
        $sos = $this->sos();
        $partner = $this->depanneur();
        app(SosService::class)->accept($sos, $partner);

        $this->actingAs($partner->user)->post(route('partner.sos.complete', $sos))
            ->assertSessionHasErrors('sos');
        $this->assertSame(SosStatus::ACCEPTED, $sos->fresh()->status);
    }

    public function test_un_autre_depanneur_ne_peut_pas_gerer_l_intervention(): void
    {
        $sos = $this->sos();
        $owner = $this->depanneur();
        $other = $this->depanneur();
        app(SosService::class)->accept($sos, $owner);

        $this->actingAs($other->user)->post(route('partner.sos.arrive', $sos))->assertForbidden();
        $this->actingAs($other->user)->get(route('partner.sos.show', $sos))->assertForbidden();
    }

    public function test_un_depanneur_ne_prend_pas_deux_interventions(): void
    {
        $partner = $this->depanneur();
        $service = app(SosService::class);
        $service->accept($this->sos(), $partner);

        $this->expectException(DomainException::class);
        $service->accept($this->sos(), $partner);
    }

    public function test_le_client_annule_et_les_autres_n_y_ont_pas_acces(): void
    {
        $owner = $this->client();
        $other = $this->client();
        $sos = $this->sos($owner);

        $this->actingAs($other)->get(route('client.sos.show', $sos))->assertForbidden();
        $this->actingAs($other)->post(route('client.sos.cancel', $sos))->assertForbidden();

        $this->actingAs($owner)->post(route('client.sos.cancel', $sos))->assertRedirect();
        $this->assertSame(SosStatus::CANCELLED, $sos->fresh()->status);
    }

    public function test_un_client_n_accede_pas_a_l_espace_depanneur(): void
    {
        $this->actingAs($this->client())->get(route('partner.sos.index'))->assertForbidden();
    }

    public function test_calcul_de_distance(): void
    {
        $this->assertEqualsWithDelta(0, Geo::distanceKm(14.69, -17.44, 14.69, -17.44), 0.001);

        // Dakar - Thiès : environ 55 km
        $km = Geo::distanceKm(14.6937, -17.4441, 14.7910, -16.9359);
        $this->assertGreaterThan(50, $km);
        $this->assertLessThan(60, $km);
    }
}