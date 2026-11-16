<?php

namespace Tests\Feature;

use App\Contracts\SmsGatewayInterface;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\Support\FakeSmsGateway;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private FakeSmsGateway $sms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->sms = new FakeSmsGateway;
        $this->app->instance(SmsGatewayInterface::class, $this->sms);
    }

    /** @param array<string, mixed> $attributes */
    private function client(array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['password' => 'ancienmotdepasse']);
        $user->assignRole('client');

        return $user;
    }

    public function test_mot_de_passe_oublie_par_sms(): void
    {
        $user = $this->client(['phone' => '+221771110001']);

        $this->post(route('password.send'), ['phone' => '77 111 00 01'])
            ->assertRedirect(route('password.reset.form'));
        $this->assertCount(1, $this->sms->sent);

        $this->post(route('password.reset'), [
            'code' => $this->sms->lastCode(),
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('nouveaumotdepasse', $user->fresh()->password));
    }

    public function test_un_numero_inconnu_recoit_la_meme_reponse_sans_sms(): void
    {
        $this->post(route('password.send'), ['phone' => '77 999 99 99'])
            ->assertRedirect(route('password.reset.form'));

        $this->assertCount(0, $this->sms->sent);
    }

    public function test_un_mauvais_code_ne_change_rien(): void
    {
        $user = $this->client(['phone' => '+221771110002']);

        $this->post(route('password.send'), ['phone' => '77 111 00 02']);
        $this->post(route('password.reset'), [
            'code' => '000000',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ])->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('ancienmotdepasse', $user->fresh()->password));
    }

    public function test_le_code_d_un_autre_usage_n_est_pas_accepte(): void
    {
        $this->client(['phone' => '+221771110003']);
        $other = $this->client(['phone' => '+221771110004']);

        // code de changement de numéro de l'autre utilisateur, utilisé pour réinitialiser le mot de passe du premier
        $this->actingAs($other)->post(route('client.security.phone.request'), [
            'new_phone' => '77 111 00 05',
            'current_password' => 'ancienmotdepasse',
        ]);
        $code = $this->sms->lastCode();
        Auth::logout();

        $this->post(route('password.send'), ['phone' => '77 111 00 03']);
        $this->post(route('password.reset'), [
            'code' => $code,
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ])->assertSessionHasErrors('code');
    }

    public function test_changement_de_mot_de_passe_exige_l_ancien(): void
    {
        $user = $this->client();

        $this->actingAs($user)->put(route('client.security.password'), [
            'current_password' => 'faux',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('client.security.password'), [
            'current_password' => 'ancienmotdepasse',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('nouveaumotdepasse', $user->fresh()->password));
    }

    public function test_changement_de_numero_avec_verification(): void
    {
        $user = $this->client(['phone' => '+221771110006']);

        $this->actingAs($user)->post(route('client.security.phone.request'), [
            'new_phone' => '77 111 00 07',
            'current_password' => 'ancienmotdepasse',
        ])->assertSessionHasNoErrors();

        $this->assertSame('+221771110007', $this->sms->lastPhone());
        $this->assertSame('+221771110006', $user->fresh()->phone); // pas encore changé

        $this->actingAs($user)->post(route('client.security.phone.confirm'), [
            'code' => $this->sms->lastCode(),
        ])->assertSessionHasNoErrors();

        $this->assertSame('+221771110007', $user->fresh()->phone);
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_numero_deja_utilise_refuse_et_mot_de_passe_requis(): void
    {
        $this->client(['phone' => '+221771110008']);
        $user = $this->client(['phone' => '+221771110009']);

        $this->actingAs($user)->post(route('client.security.phone.request'), [
            'new_phone' => '77 111 00 08',
            'current_password' => 'ancienmotdepasse',
        ])->assertSessionHasErrors('new_phone');

        $this->actingAs($user)->post(route('client.security.phone.request'), [
            'new_phone' => '77 111 00 10',
            'current_password' => 'faux',
        ])->assertSessionHasErrors('current_password');

        $this->assertCount(0, $this->sms->sent);
    }

    public function test_la_page_securite_est_privee(): void
    {
        $this->get(route('client.security.edit'))->assertRedirect(route('login'));
    }
}