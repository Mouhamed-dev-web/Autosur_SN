<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\OtpCode;
use App\Models\Partner;
use App\Models\Rating;
use App\Models\Setting;
use App\Models\User;
use App\Services\PartnerService;
use App\Services\PaymentService;
use App\Services\TechnicalVisitService;
use Database\Seeders\RoleSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinalizationTest extends TestCase
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

    private function sos(User $user, string $status, array $extra = [])
    {
        return $user->sosRequests()->create($extra + [
            'status' => $status,
            'problem' => 'battery',
            'lat' => 14.7,
            'lng' => -17.4,
            'location_text' => 'VDN',
        ]);
    }

    public function test_les_pages_legales_s_affichent(): void
    {
        foreach (['legal.notice', 'legal.terms', 'legal.privacy'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_les_en_tetes_de_securite_sont_presents(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_la_purge_efface_les_positions_anciennes_seulement(): void
    {
        $user = $this->userWithRole('client');
        $old = $this->sos($user, 'completed');
        $recent = $this->sos($user, 'completed');
        DB::table('sos_requests')->where('id', $old->id)->update(['updated_at' => now()->subDays(40)]);

        $this->artisan('autosur:purge-data')->assertExitCode(0);

        $old->refresh();
        $this->assertNull($old->lat);
        $this->assertNull($old->lng);
        $this->assertNull($old->location_text);
        $this->assertNotNull($recent->fresh()->lat);
    }

    public function test_un_sos_sans_reponse_depuis_un_jour_est_annule(): void
    {
        $user = $this->userWithRole('client');
        $pending = $this->sos($user, 'pending');
        DB::table('sos_requests')->where('id', $pending->id)->update(['created_at' => now()->subDays(2)]);

        $this->artisan('autosur:purge-data')->assertExitCode(0);

        $this->assertSame('cancelled', $pending->fresh()->status->value);
    }

    public function test_le_mode_simulation_ne_modifie_rien(): void
    {
        $old = $this->sos($this->userWithRole('client'), 'completed');
        DB::table('sos_requests')->where('id', $old->id)->update(['updated_at' => now()->subDays(40)]);

        $this->artisan('autosur:purge-data', ['--dry-run' => true])->assertExitCode(0);

        $this->assertNotNull($old->fresh()->lat);
    }

    public function test_la_purge_nettoie_les_otp_et_les_adresses_ip(): void
    {
        $otp = OtpCode::create(['phone' => '+221770000001', 'code_hash' => 'x', 'expires_at' => now()->subDays(2)]);
        DB::table('otp_codes')->where('id', $otp->id)->update(['created_at' => now()->subDays(2)]);

        $fresh = OtpCode::create(['phone' => '+221770000002', 'code_hash' => 'x', 'expires_at' => now()->addMinutes(5)]);

        $contact = ContactMessage::create([
            'name' => 'Awa', 'email' => 'awa@example.com', 'subject' => 'question',
            'message' => 'Bonjour, une question.', 'ip' => '1.2.3.4',
        ]);
        DB::table('contact_messages')->where('id', $contact->id)->update(['created_at' => now()->subDays(100)]);

        $this->artisan('autosur:purge-data')->assertExitCode(0);

        $this->assertNull(OtpCode::find($otp->id));
        $this->assertNotNull(OtpCode::find($fresh->id));
        $this->assertNull($contact->fresh()->ip);
        $this->assertSame('awa@example.com', $contact->fresh()->email);
    }

    public function test_la_validation_d_un_partenaire_est_journalisee(): void
    {
        $admin = $this->userWithRole('admin');
        $garageUser = $this->userWithRole('garage');
        $partner = Partner::create([
            'user_id' => $garageUser->id, 'type' => 'garage', 'company_name' => 'Garage Audit',
            'contact_phone' => '+221770000011', 'is_validated' => false,
        ]);

        $this->actingAs($admin);
        app(PartnerService::class)->validate($partner);
        app(PartnerService::class)->suspend($partner);

        $this->assertDatabaseHas('audit_logs', ['action' => 'partner.validated', 'user_id' => $admin->id, 'subject_id' => $partner->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'partner.suspended', 'user_id' => $admin->id]);
    }

    public function test_le_changement_d_un_parametre_est_journalise(): void
    {
        Setting::where('name', 'technical_visit_fee')->firstOrFail()->update(['value' => '2500']);

        $log = AuditLog::where('action', 'setting.updated')->firstOrFail();
        $this->assertSame('2000', $log->details['from']);
        $this->assertSame('2500', $log->details['to']);
    }

    public function test_le_masquage_d_un_avis_est_journalise(): void
    {
        $client = $this->userWithRole('client');
        $partner = Partner::create([
            'user_id' => $this->userWithRole('garage')->id, 'type' => 'garage', 'company_name' => 'Garage Avis',
            'contact_phone' => '+221770000012', 'is_validated' => true,
        ]);
        $rating = Rating::create([
            'user_id' => $client->id, 'partner_id' => $partner->id,
            'rateable_type' => 'App\\Models\\TechnicalVisit', 'rateable_id' => 1, 'score' => 1,
        ]);

        $rating->update(['is_hidden' => true]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'rating.hidden', 'subject_id' => $rating->id]);
    }

    public function test_les_paiements_peuvent_etre_coupes(): void
    {
        config(['payments.enabled' => false, 'payments.driver' => 'simulation']);

        $user = $this->userWithRole('client');
        $vehicle = $user->vehicles()->create([
            'plate' => 'DK-1234-AB', 'brand' => 'Toyota', 'model' => 'Corolla',
            'year' => 2018, 'insurance_status' => 'active',
        ]);
        $visit = app(TechnicalVisitService::class)->create($user, $vehicle, now()->addDays(3));

        $this->expectException(DomainException::class);
        app(PaymentService::class)->start($user, $visit, PaymentProvider::WAVE);
    }
}