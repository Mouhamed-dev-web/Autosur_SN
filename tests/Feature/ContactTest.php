<?php

namespace Tests\Feature;

use App\Mail\ContactReceived;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Awa Diop',
            'email' => 'awa@example.com',
            'subject' => 'question',
            'message' => 'Bonjour, comment fonctionne la visite technique ?',
        ], $override);
    }

    public function test_les_pages_publiques_s_affichent(): void
    {
        $this->get(route('contact'))->assertOk();
        $this->get(route('partners'))->assertOk()->assertSee('Les premiers partenaires arrivent bientôt');
    }

    public function test_un_message_est_enregistre_et_envoye(): void
    {
        Mail::fake();
        config(['autosur.contact_email' => 'equipe@autosur.test']);

        $this->post(route('contact.store'), $this->payload())->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', ['name' => 'Awa Diop', 'email' => 'awa@example.com']);
        Mail::assertSent(ContactReceived::class);
    }

    public function test_un_moyen_de_reponse_est_obligatoire(): void
    {
        $this->post(route('contact.store'), $this->payload(['email' => '']))
            ->assertSessionHasErrors(['email', 'phone']);
        $this->assertSame(0, ContactMessage::count());
    }

    public function test_le_champ_piege_bloque_les_robots(): void
    {
        $this->post(route('contact.store'), $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');
        $this->assertSame(0, ContactMessage::count());
    }

    public function test_limite_de_frequence(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $this->payload())->assertRedirect();
        }

        $this->post(route('contact.store'), $this->payload())->assertStatus(429);
    }

    public function test_un_partenaire_valide_apparait_et_pas_les_autres(): void
    {
        $make = function (string $name, bool $validated) {
            $user = \App\Models\User::factory()->create();

            return \App\Models\Partner::create([
                'user_id' => $user->id, 'type' => 'garage', 'company_name' => $name,
                'contact_phone' => '+221770000009', 'is_validated' => $validated,
            ]);
        };
        $make('Garage Visible', true);
        $make('Garage Cache', false);

        $this->get(route('partners'))->assertSee('Garage Visible')->assertDontSee('Garage Cache');
    }
}