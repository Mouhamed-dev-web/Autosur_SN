<?php

namespace Tests\Feature;

use App\Services\Sms\HttpSmsGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'sms.http.url' => 'https://sms.example.test/send',
            'sms.http.token' => 'secret-token',
            'sms.http.sender' => 'AutoSur',
            'sms.http.auth' => 'bearer',
            'sms.http.format' => 'json',
            'sms.http.strip_plus' => false,
        ]);
    }

    public function test_envoi_reussi_avec_jeton_et_champs_configures(): void
    {
        Http::fake(['sms.example.test/*' => Http::response(['ok' => true], 200)]);

        $this->assertTrue(app(HttpSmsGateway::class)->send('+221771234567', 'Bonjour'));

        Http::assertSent(fn ($request) => $request->url() === 'https://sms.example.test/send'
            && $request->hasHeader('Authorization', 'Bearer secret-token')
            && $request['to'] === '+221771234567'
            && $request['message'] === 'Bonjour'
            && $request['from'] === 'AutoSur');
    }

    public function test_le_plus_peut_etre_retire_du_numero(): void
    {
        config(['sms.http.strip_plus' => true]);
        Http::fake();

        app(HttpSmsGateway::class)->send('+221771234567', 'Bonjour');

        Http::assertSent(fn ($request) => $request['to'] === '221771234567');
    }

    public function test_un_refus_du_fournisseur_retourne_faux(): void
    {
        Http::fake(['*' => Http::response('erreur', 500)]);

        $this->assertFalse(app(HttpSmsGateway::class)->send('+221771234567', 'Bonjour'));
    }

    public function test_une_panne_reseau_retourne_faux(): void
    {
        Http::fake(['*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout')]);

        $this->assertFalse(app(HttpSmsGateway::class)->send('+221771234567', 'Bonjour'));
    }

    public function test_sans_url_rien_n_est_envoye(): void
    {
        config(['sms.http.url' => null]);
        Http::fake();

        $this->assertFalse(app(HttpSmsGateway::class)->send('+221771234567', 'Bonjour'));
        Http::assertNothingSent();
    }

    public function test_le_pilote_http_est_choisi_par_la_configuration(): void
    {
        config(['sms.driver' => 'http']);

        $this->assertInstanceOf(
            HttpSmsGateway::class,
            app(\App\Contracts\SmsGatewayInterface::class),
        );
    }
}