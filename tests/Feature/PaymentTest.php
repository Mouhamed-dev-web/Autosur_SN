<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\TechnicalVisit;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\TechnicalVisitService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        config([
            'payments.driver' => 'simulation',
            'payments.simulation.secret' => 'test-secret',
        ]);
    }

    private function client(): User
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        return $user;
    }

    private function visitFor(User $user): TechnicalVisit
    {
        $vehicle = $user->vehicles()->create([
            'plate' => 'DK-'.random_int(1000, 9999).'-AB', 'brand' => 'Toyota', 'model' => 'Corolla',
            'year' => 2018, 'insurance_status' => 'active',
        ]);

        return app(TechnicalVisitService::class)->create($user, $vehicle, now()->addDays(3));
    }

    private function startPayment(User $user, TechnicalVisit $visit): Transaction
    {
        app(PaymentService::class)->start($user, $visit, PaymentProvider::WAVE);

        return Transaction::latest('id')->firstOrFail();
    }

    /** @return array{0: string, 1: string} corps JSON et signature */
    private function signed(Transaction $tx, string $status = 'paid', ?int $amount = null, ?string $eventId = null): array
    {
        $body = json_encode([
            'event_id' => $eventId ?? (string) Str::uuid(),
            'reference' => $tx->reference,
            'status' => $status,
            'amount' => $amount ?? $tx->amount,
        ]);

        return [$body, hash_hmac('sha256', $body, 'test-secret')];
    }

    private function webhook(string $body, string $signature)
    {
        return $this->call('POST', route('webhooks.payment', 'simulation'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature,
        ], $body);
    }

    public function test_le_client_demarre_un_paiement_avec_le_montant_du_serveur(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);

        // Le client tente d'imposer un montant : il est ignoré
        $this->actingAs($user)
            ->post(route('client.visits.pay', $visit), ['provider' => 'wave', 'amount' => 1])
            ->assertRedirect();

        $tx = Transaction::firstOrFail();
        $this->assertSame($visit->fee_amount, $tx->amount);
        $this->assertSame(PaymentStatus::PENDING, $tx->status);
        $this->assertNull($visit->fresh()->fee_paid_at);
    }

    public function test_operateur_invalide_refuse(): void
    {
        $user = $this->client();

        $this->actingAs($user)
            ->post(route('client.visits.pay', $this->visitFor($user)), ['provider' => 'bitcoin'])
            ->assertSessionHasErrors('provider');
    }

    public function test_impossible_de_payer_la_visite_d_un_autre(): void
    {
        $owner = $this->client();
        $other = $this->client();
        $visit = $this->visitFor($owner);

        $this->actingAs($other)
            ->post(route('client.visits.pay', $visit), ['provider' => 'wave'])
            ->assertForbidden();

        $this->assertSame(0, Transaction::count());
    }

    public function test_webhook_valide_confirme_le_paiement(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);
        $tx = $this->startPayment($user, $visit);

        [$body, $sig] = $this->signed($tx);
        $this->webhook($body, $sig)->assertOk()->assertJson(['status' => 'paid']);

        $this->assertSame(PaymentStatus::PAID, $tx->fresh()->status);
        $this->assertNotNull($tx->fresh()->paid_at);
        $this->assertNotNull($visit->fresh()->fee_paid_at);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_signature_invalide_refusee_sans_aucun_effet(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);
        $tx = $this->startPayment($user, $visit);

        [$body] = $this->signed($tx);
        $this->webhook($body, 'signature-falsifiee')->assertUnauthorized();

        $this->assertSame(PaymentStatus::PENDING, $tx->fresh()->status);
        $this->assertNull($visit->fresh()->fee_paid_at);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_sans_secret_configure_tout_est_refuse(): void
    {
        $user = $this->client();
        $tx = $this->startPayment($user, $this->visitFor($user));

        config(['payments.simulation.secret' => null]);

        $body = json_encode(['event_id' => 'x', 'reference' => $tx->reference, 'status' => 'paid', 'amount' => $tx->amount]);
        $this->webhook($body, hash_hmac('sha256', $body, ''))->assertUnauthorized();
        $this->assertSame(PaymentStatus::PENDING, $tx->fresh()->status);
    }

    public function test_un_webhook_rejoue_n_a_pas_de_double_effet(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);
        $tx = $this->startPayment($user, $visit);

        [$body, $sig] = $this->signed($tx);
        $this->webhook($body, $sig)->assertJson(['status' => 'paid']);
        $paidAt = $tx->fresh()->paid_at;

        $this->webhook($body, $sig)->assertOk()->assertJson(['status' => 'ignored']);

        $this->assertEquals($paidAt, $tx->fresh()->paid_at);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_montant_different_refuse(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);
        $tx = $this->startPayment($user, $visit);

        [$body, $sig] = $this->signed($tx, 'paid', 100);
        $this->webhook($body, $sig)->assertJson(['status' => 'amount_mismatch']);

        $this->assertSame(PaymentStatus::FAILED, $tx->fresh()->status);
        $this->assertNull($visit->fresh()->fee_paid_at);
    }

    public function test_reference_inconnue(): void
    {
        $body = json_encode(['event_id' => 'e1', 'reference' => 'INCONNUE', 'status' => 'paid', 'amount' => 2000]);

        $this->webhook($body, hash_hmac('sha256', $body, 'test-secret'))->assertNotFound();
    }

    public function test_echec_puis_nouvelle_tentative_possible(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);
        $first = $this->startPayment($user, $visit);

        [$body, $sig] = $this->signed($first, 'failed');
        $this->webhook($body, $sig)->assertJson(['status' => 'failed']);
        $this->assertSame(PaymentStatus::FAILED, $first->fresh()->status);
        $this->assertNull($visit->fresh()->fee_paid_at);

        $second = $this->startPayment($user, $visit);
        $this->assertNotSame($first->reference, $second->reference);
        $this->assertSame(PaymentStatus::PENDING, $second->status);
    }

    public function test_une_seule_transaction_en_attente_par_visite(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);

        $first = $this->startPayment($user, $visit);
        $this->startPayment($user, $visit);

        $this->assertSame(PaymentStatus::EXPIRED, $first->fresh()->status);
        $this->assertSame(1, Transaction::where('status', 'pending')->count());
    }

    public function test_impossible_de_payer_deux_fois(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);
        $tx = $this->startPayment($user, $visit);

        [$body, $sig] = $this->signed($tx);
        $this->webhook($body, $sig);

        $this->actingAs($user)
            ->post(route('client.visits.pay', $visit), ['provider' => 'wave'])
            ->assertSessionHasErrors('payment');
        $this->assertSame(1, Transaction::count());
    }

    public function test_une_visite_annulee_ne_se_paie_pas(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);
        app(TechnicalVisitService::class)->cancel($visit);

        $this->actingAs($user)
            ->post(route('client.visits.pay', $visit), ['provider' => 'orange_money'])
            ->assertSessionHasErrors('payment');
    }

    public function test_parcours_complet_via_la_page_de_simulation(): void
    {
        $user = $this->client();
        $visit = $this->visitFor($user);
        $tx = $this->startPayment($user, $visit);

        $this->actingAs($user)->get(route('payments.simulation.show', $tx))->assertOk();

        $this->actingAs($user)
            ->post(route('payments.simulation.complete', $tx), ['outcome' => 'paid'])
            ->assertRedirect(route('client.payments.result', $tx));

        $this->assertSame(PaymentStatus::PAID, $tx->fresh()->status);
        $this->assertNotNull($visit->fresh()->fee_paid_at);
    }

    public function test_la_simulation_est_fermee_aux_autres(): void
    {
        $owner = $this->client();
        $other = $this->client();
        $tx = $this->startPayment($owner, $this->visitFor($owner));

        $this->actingAs($other)->get(route('payments.simulation.show', $tx))->assertForbidden();
        $this->actingAs($other)
            ->post(route('payments.simulation.complete', $tx), ['outcome' => 'paid'])
            ->assertForbidden();
        $this->assertSame(PaymentStatus::PENDING, $tx->fresh()->status);
    }

    public function test_chacun_ne_voit_que_ses_paiements(): void
    {
        $a = $this->client();
        $b = $this->client();
        $txA = $this->startPayment($a, $this->visitFor($a));
        $txB = $this->startPayment($b, $this->visitFor($b));

        $this->actingAs($a)->get(route('client.payments.index'))
            ->assertOk()
            ->assertSee($txA->reference)
            ->assertDontSee($txB->reference);

        $this->actingAs($a)->get(route('client.payments.result', $txB))->assertForbidden();
    }
}