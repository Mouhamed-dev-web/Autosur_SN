<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function client(): User
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('client');

        return $user;
    }

    public function test_tableau_de_bord_vide(): void
    {
        $this->actingAs($this->client())->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Aucune échéance dans les 30 prochains jours')
            ->assertSee('pas encore de véhicule');
    }

    public function test_les_echeances_sont_affichees_avec_leur_niveau(): void
    {
        $user = $this->client();
        $user->vehicles()->create([
            'plate' => 'DK-1111-AA', 'brand' => 'Toyota', 'model' => 'Corolla', 'year' => 2018,
            'insurance_status' => 'active',
            'insurance_expires_at' => now()->addDays(5)->toDateString(),
            'technical_visit_expires_at' => now()->subDays(3)->toDateString(),
        ]);
        $user->vehicles()->create([
            'plate' => 'DK-2222-BB', 'brand' => 'Kia', 'model' => 'Rio', 'year' => 2020,
            'insurance_status' => 'active',
            'insurance_expires_at' => now()->addDays(90)->toDateString(),
        ]);

        $this->actingAs($user)->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Expiré depuis 3 j')
            ->assertSee('Dans 5 j')
            ->assertDontSee('Dans 90 j');
    }

    public function test_les_donnees_d_un_autre_client_ne_sont_pas_visibles(): void
    {
        $other = $this->client();
        $other->vehicles()->create([
            'plate' => 'DK-9999-ZZ', 'brand' => 'Kia', 'model' => 'Rio', 'year' => 2020,
            'insurance_status' => 'active',
            'insurance_expires_at' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($this->client())->get(route('client.dashboard'))
            ->assertOk()
            ->assertDontSee('DK-9999-ZZ');
    }

    public function test_un_visiteur_est_redirige(): void
    {
        $this->get(route('client.dashboard'))->assertRedirect(route('login'));
    }
}