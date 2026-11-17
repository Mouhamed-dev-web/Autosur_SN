<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('local');
        Storage::fake('public');
    }

    private function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk;
    }

    private function client(): User
    {
        /** @var User $user */
        $user = User::factory()->create(['password' => 'motdepasse1', 'phone' => '+221771230000']);
        $user->assignRole('client');

        return $user;
    }

    /** @return array<string, string> */
    private function confirm(string $password = 'motdepasse1', string $word = 'SUPPRIMER'): array
    {
        return ['current_password' => $password, 'confirmation' => $word];
    }

    public function test_suppression_efface_donnees_et_fichiers(): void
    {
        $user = $this->client();

        $file = UploadedFile::fake()->create('cg.pdf', 10, 'application/pdf');
        $path = (string) $file->store('documents/'.$user->id, 'local');

        $user->documents()->create([
            'type' => 'carte_grise', 'title' => 'CG', 'path' => $path,
            'original_name' => 'cg.pdf', 'mime' => 'application/pdf', 'size' => 10,
        ]);

        $this->actingAs($user)->delete(route('client.account.destroy'), $this->confirm())
            ->assertRedirect(route('home'));

        $this->assertNull(User::find($user->id));
        $this->disk()->assertMissing($path);
        $this->assertSame(0, Document::count());
    }

    public function test_mot_de_passe_et_mot_de_confirmation_requis(): void
    {
        $user = $this->client();

        $this->actingAs($user)->delete(route('client.account.destroy'), $this->confirm('faux'))
            ->assertSessionHasErrors('current_password');
        $this->actingAs($user)->delete(route('client.account.destroy'), $this->confirm('motdepasse1', 'oui'))
            ->assertSessionHasErrors('confirmation');

        $this->assertNotNull(User::find($user->id));
    }

    public function test_une_operation_en_cours_bloque_la_suppression(): void
    {
        $user = $this->client();
        $user->sosRequests()->create(['status' => 'pending', 'problem' => 'battery', 'location_text' => 'VDN']);

        $this->actingAs($user)->delete(route('client.account.destroy'), $this->confirm())
            ->assertSessionHasErrors('account');

        $this->assertNotNull(User::find($user->id));
    }

    public function test_un_compte_avec_paiements_est_anonymise(): void
    {
        $user = $this->client();
        $vehicle = $user->vehicles()->create([
            'plate' => 'DK-1111-AA', 'brand' => 'Toyota', 'model' => 'Corolla',
            'year' => 2018, 'insurance_status' => 'active',
        ]);
        $visit = $user->technicalVisits()->create([
            'vehicle_id' => $vehicle->id, 'scheduled_at' => now()->addDays(3),
            'status' => 'completed', 'fee_amount' => 2000,
        ]);
        $visit->transactions()->create([
            'user_id' => $user->id, 'provider' => 'wave', 'gateway' => 'simulation',
            'reference' => 'AS-TEST-1', 'amount' => 2000, 'status' => 'paid',
        ]);

        $this->actingAs($user)->delete(route('client.account.destroy'), $this->confirm())
            ->assertRedirect(route('home'));

        $user->refresh();
        $this->assertNotNull($user->anonymized_at);
        $this->assertNull($user->phone);
        $this->assertNull($user->email);
        $this->assertSame(0, $user->vehicles()->count());
        $this->assertDatabaseHas('transactions', ['reference' => 'AS-TEST-1']);
    }

    public function test_un_partenaire_ne_peut_pas_se_supprimer_ici(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['password' => 'motdepasse1']);
        $user->assignRole('garage');

        $this->actingAs($user)->delete(route('client.account.destroy'), $this->confirm())->assertForbidden();
    }

    public function test_la_suppression_est_journalisee(): void
    {
        $user = $this->client();

        $this->actingAs($user)->delete(route('client.account.destroy'), $this->confirm());

        $this->assertDatabaseHas('audit_logs', ['action' => 'account.deleted']);
    }
}