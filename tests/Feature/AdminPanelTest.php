<?php

namespace Tests\Feature;

use App\Contracts\SmsGatewayInterface;
use App\Models\Partner;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\StatusNotification;
use App\Services\PartnerService;
use App\Services\TechnicalVisitService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_l_admin_ouvre_toutes_les_pages_du_back_office(): void
    {
        $admin = $this->userWithRole('admin');

        foreach (['', '/partners', '/ratings', '/contact-messages', '/insurance-offers', '/transactions', '/users', '/vehicles', '/settings'] as $path) {
            $this->actingAs($admin)->get('/admin'.$path)->assertOk();
        }
    }

    public function test_un_client_ou_un_partenaire_n_entre_pas_dans_le_back_office(): void
    {
        foreach (['client', 'garage', 'depanneur', 'assurance'] as $role) {
            $this->actingAs($this->userWithRole($role))->get('/admin')->assertForbidden();
        }
    }

    public function test_la_validation_active_le_partenaire_et_le_previent(): void
    {
        $user = $this->userWithRole('garage');
        $partner = Partner::create([
            'user_id' => $user->id, 'type' => 'garage', 'company_name' => 'Garage Test',
            'contact_phone' => '+221770000011', 'is_validated' => false,
        ]);

        app(PartnerService::class)->validate($partner);
        $this->assertTrue($partner->fresh()->is_validated);
        $this->assertDatabaseCount('notifications', 1);

        $partner->update(['is_available' => true]);
        app(PartnerService::class)->suspend($partner);
        $partner->refresh();
        $this->assertFalse($partner->is_validated);
        $this->assertFalse($partner->is_available);
    }

    public function test_les_frais_viennent_des_parametres(): void
    {
        Setting::where('name', 'technical_visit_fee')->firstOrFail()->update(['value' => '2500']);

        $this->assertSame(2500, app(TechnicalVisitService::class)->fee());
    }

    public function test_les_regles_des_parametres_bornent_les_frais(): void
    {
        $this->assertStringContainsString('between:1500,3000', Setting::RULES['technical_visit_fee']);
    }

    public function test_le_sms_peut_etre_desactive_globalement(): void
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

        $user = $this->userWithRole('client');
        $user->update(['phone' => '+221771234567', 'notify_sms' => true]);

        $user->notify(new StatusNotification('Test', 'Message', 'http://localhost'));
        $this->assertCount(1, $sms->sent);

        Setting::where('name', 'channel_sms_enabled')->firstOrFail()->update(['value' => '0']);
        $user->notify(new StatusNotification('Test', 'Message 2', 'http://localhost'));
        $this->assertCount(1, $sms->sent); // aucun SMS de plus
    }
}