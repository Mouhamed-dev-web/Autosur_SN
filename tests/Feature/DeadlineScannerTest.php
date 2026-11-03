<?php

namespace Tests\Feature;

use App\Contracts\SmsGatewayInterface;
use App\Models\Reminder;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\DeadlineReminderNotification;
use App\Services\DeadlineScanner;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeadlineScannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function client(): User
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        return $user;
    }

    private function vehicleData(array $override = []): array
    {
        return array_merge([
            'plate' => 'DK-1234-AB',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2018,
            'insurance_status' => 'active',
        ], $override);
    }

    public function test_un_rappel_est_cree_une_seule_fois(): void
    {
        Notification::fake();
        $user = $this->client();
        $user->vehicles()->create($this->vehicleData([
            'insurance_expires_at' => now()->addDays(5)->toDateString(),
        ]));

        $scanner = app(DeadlineScanner::class);

        $this->assertSame(1, $scanner->run());
        $this->assertSame(0, $scanner->run());

        $this->assertDatabaseCount('reminders', 1);
        $this->assertSame(7, Reminder::first()->days_before);
        Notification::assertSentToTimes($user, DeadlineReminderNotification::class, 1);
    }

    public function test_aucune_alerte_si_echeance_lointaine_ou_depassee(): void
    {
        Notification::fake();
        $user = $this->client();
        $user->vehicles()->create($this->vehicleData([
            'plate' => 'DK-1111-AA',
            'insurance_expires_at' => now()->addDays(90)->toDateString(),
        ]));
        $user->vehicles()->create($this->vehicleData([
            'plate' => 'DK-2222-BB',
            'insurance_expires_at' => now()->subDays(3)->toDateString(),
        ]));

        $this->assertSame(0, app(DeadlineScanner::class)->run());
        Notification::assertNothingSent();
    }

    public function test_le_seuil_choisi_est_le_plus_proche(): void
    {
        $scanner = app(DeadlineScanner::class);

        $this->assertSame(0, $scanner->offsetFor(0));
        $this->assertSame(7, $scanner->offsetFor(5));
        $this->assertSame(30, $scanner->offsetFor(20));
        $this->assertNull($scanner->offsetFor(45));
        $this->assertNull($scanner->offsetFor(-1));
    }

    public function test_le_canal_sms_depend_des_preferences(): void
    {
        Notification::fake();
        $user = $this->client();
        $user->update(['phone' => '+221771234567']);
        $user->vehicles()->create($this->vehicleData([
            'insurance_expires_at' => now()->addDays(2)->toDateString(),
        ]));
        app(DeadlineScanner::class)->run();

        $notification = new DeadlineReminderNotification(Reminder::first());

        $user->update(['notify_sms' => false]);
        $this->assertNotContains(SmsChannel::class, $notification->via($user->fresh()));

        $user->update(['notify_sms' => true]);
        $this->assertContains(SmsChannel::class, $notification->via($user->fresh()));
    }

    public function test_le_sms_est_envoye_et_la_notification_enregistree(): void
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

        $user = $this->client();
        $user->update(['phone' => '+221771234567', 'notify_sms' => true]);
        $user->vehicles()->create($this->vehicleData([
            'technical_visit_expires_at' => now()->addDays(3)->toDateString(),
        ]));

        app(DeadlineScanner::class)->run();

        $this->assertCount(1, $sms->sent);
        $this->assertSame('+221771234567', $sms->sent[0][0]);
        $this->assertDatabaseCount('notifications', 1);
    }
}