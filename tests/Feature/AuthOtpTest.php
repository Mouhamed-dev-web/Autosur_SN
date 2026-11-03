<?php

namespace Tests\Feature;

use App\Contracts\SmsGatewayInterface;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthOtpTest extends TestCase
{
    use RefreshDatabase;

    private object $sms;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->sms = new class implements SmsGatewayInterface
        {
            public array $sent = [];

            public function send(string $phone, string $message): bool
            {
                $this->sent[] = [$phone, $message];

                return true;
            }
        };

        $this->app->instance(SmsGatewayInterface::class, $this->sms);
    }

    private function register(): void
    {
        $this->post('/inscription', [
            'name' => 'Awa Diop',
            'phone' => '77 123 45 67',
            'password' => 'motdepasse1',
            'password_confirmation' => 'motdepasse1',
        ])->assertRedirect(route('otp.show'));
    }

    public function test_inscription_et_verification_otp(): void
    {
        $this->register();

        preg_match('/(\d{6})/', $this->sms->sent[0][1], $m);

        $this->post('/verification', ['code' => $m[1]])
            ->assertRedirect(route('client.dashboard'));

        $user = User::where('phone', '+221771234567')->first();

        $this->assertNotNull($user->phone_verified_at);
        $this->assertTrue($user->hasRole('client'));
    }

    public function test_mauvais_code_refuse(): void
    {
        $this->register();

        $this->post('/verification', ['code' => '000000'])
            ->assertSessionHasErrors('code');
    }
}