<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function partner(string $type): Partner
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole($type);

        return Partner::create([
            'user_id' => $user->id, 'type' => $type, 'company_name' => ucfirst($type).' Dash',
            'contact_phone' => '+221770000020', 'is_validated' => true, 'is_available' => $type === 'depanneur',
        ]);
    }

    public function test_chaque_type_de_partenaire_voit_son_tableau_de_bord(): void
    {
        foreach (['garage' => 'À traiter', 'depanneur' => 'Demandes proches', 'assurance' => 'Prospects en attente'] as $type => $label) {
            $partner = $this->partner($type);

            $this->actingAs($partner->user)->get(route('partner.dashboard'))
                ->assertOk()
                ->assertSee($label);
        }
    }

    public function test_un_client_n_y_accede_pas(): void
    {
        /** @var User $client */
        $client = User::factory()->create();
        $client->assignRole('client');

        $this->actingAs($client)->get(route('partner.dashboard'))->assertForbidden();
    }
}