<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
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

    private function payload(array $override = []): array
    {
        return array_merge([
            'plate' => 'dk 1234 ab',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2018,
            'insurance_status' => 'active',
        ], $override);
    }

    public function test_un_client_peut_ajouter_un_vehicule(): void
    {
        $user = $this->client();

        $this->actingAs($user)
            ->post(route('client.vehicles.store'), $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('vehicles', ['user_id' => $user->id, 'plate' => 'DK 1234 AB']);
    }

    public function test_la_liste_ne_montre_que_ses_vehicules(): void
    {
        $a = $this->client();
        $b = $this->client();
        $a->vehicles()->create($this->payload(['plate' => 'DK-1111-AA']));
        $b->vehicles()->create($this->payload(['plate' => 'DK-2222-BB']));

        $this->actingAs($a)->get(route('client.vehicles.index'))
            ->assertOk()
            ->assertSee('DK-1111-AA')
            ->assertDontSee('DK-2222-BB');
    }

    public function test_on_ne_peut_pas_toucher_au_vehicule_d_un_autre(): void
    {
        $a = $this->client();
        $b = $this->client();
        $vehicle = $b->vehicles()->create($this->payload(['plate' => 'DK-3333-CC']));

        $this->actingAs($a)->get(route('client.vehicles.show', $vehicle))->assertForbidden();
        $this->actingAs($a)->put(route('client.vehicles.update', $vehicle), $this->payload(['plate' => 'DK-9999-ZZ']))->assertForbidden();
        $this->actingAs($a)->delete(route('client.vehicles.destroy', $vehicle))->assertForbidden();

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'plate' => 'DK-3333-CC']);
    }

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $this->get(route('client.vehicles.index'))->assertRedirect(route('login'));
    }

    public function test_immatriculation_unique(): void
    {
        $user = $this->client();
        $user->vehicles()->create($this->payload(['plate' => 'DK-4444-DD']));

        $this->actingAs($user)
            ->post(route('client.vehicles.store'), $this->payload(['plate' => 'dk-4444-dd']))
            ->assertSessionHasErrors('plate');
    }
}