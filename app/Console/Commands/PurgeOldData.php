<?php

namespace App\Console\Commands;

use App\Models\ContactMessage;
use App\Models\OtpCode;
use App\Models\SosRequest;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class PurgeOldData extends Command
{
    protected $signature = 'autosur:purge-data {--dry-run : Affiche ce qui serait traité, sans rien modifier}';

    protected $description = 'Applique les durées de conservation des données personnelles';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $config = config('autosur.retention');

        // 1. SOS restés sans réponse depuis plus de 24 h : annulés
        $expired = SosRequest::where('status', 'pending')->where('created_at', '<', now()->subDay());
        $this->report('SOS sans réponse annulés', $expired->count(), $dry);
        if (! $dry) {
            $expired->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }

        // 2. Position des SOS terminés ou annulés
        $sos = SosRequest::whereIn('status', ['completed', 'cancelled'])
            ->where('updated_at', '<', now()->subDays($config['sos_days']))
            ->where(fn ($q) => $q->whereNotNull('lat')->orWhereNotNull('lng')->orWhereNotNull('location_text'));
        $this->report('Positions SOS effacées', $sos->count(), $dry);
        if (! $dry) {
            $sos->update(['lat' => null, 'lng' => null, 'location_text' => null]);
        }

        // 3. Adresses IP des messages de contact
        $contacts = ContactMessage::whereNotNull('ip')->where('created_at', '<', now()->subDays($config['contact_ip_days']));
        $this->report('Adresses IP de contact effacées', $contacts->count(), $dry);
        if (! $dry) {
            $contacts->update(['ip' => null]);
        }

        // 4. Codes OTP de plus de 24 h
        $otp = OtpCode::where('created_at', '<', now()->subDay());
        $this->report('Codes OTP supprimés', $otp->count(), $dry);
        if (! $dry) {
            $otp->delete();
        }

        // 5. Notifications lues depuis longtemps
        $notifications = DatabaseNotification::whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($config['read_notifications_days']));
        $this->report('Notifications lues supprimées', $notifications->count(), $dry);
        if (! $dry) {
            $notifications->delete();
        }

        return self::SUCCESS;
    }

    private function report(string $label, int $count, bool $dry): void
    {
        $this->line(($dry ? '[simulation] ' : '').$label.' : '.$count);
    }
}