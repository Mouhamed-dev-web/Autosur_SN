<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountService
{
    /** @return list<string> raisons qui empêchent la suppression */
    public function blockers(User $user): array
    {
        $reasons = [];

        if ($user->sosRequests()->whereIn('status', ['pending', 'accepted', 'arrived'])->exists()) {
            $reasons[] = 'Une demande SOS est en cours.';
        }
        if ($user->technicalVisits()->whereIn('status', ['requested', 'confirmed'])->exists()) {
            $reasons[] = 'Un rendez-vous de visite technique est en cours.';
        }
        if ($user->insuranceQuotes()->whereHas('subscription', fn ($q) => $q->where('status', 'requested'))->exists()) {
            $reasons[] = 'Une demande de souscription d\'assurance est en cours.';
        }
        if ($user->transactions()->where('status', 'pending')->exists()) {
            $reasons[] = 'Un paiement est en attente.';
        }

        return $reasons;
    }

    public function delete(User $user): void
    {
        if (! $user->hasRole('client') || $user->hasAnyRole(['admin', 'garage', 'depanneur', 'assurance'])) {
            throw new DomainException('Ce type de compte doit passer par le support pour être supprimé.');
        }

        if ($reasons = $this->blockers($user)) {
            throw new DomainException(implode(' ', $reasons));
        }

        $this->deleteFiles($user);
        $keepAccounting = $user->transactions()->exists();
        $id = $user->id;

        DB::transaction(function () use ($user, $keepAccounting) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->notifications()->delete();

            if ($keepAccounting) {
                // Paiements conservés : le compte est anonymisé et désactivé
                $user->vehicles()->delete();
                $user->documents()->delete();
                $user->forceFill([
                    'name' => 'Compte supprimé',
                    'first_name' => null,
                    'last_name' => null,
                    'email' => null,
                    'phone' => null,
                    'address' => null,
                    'photo_path' => null,
                    'password' => Str::random(64),
                    'remember_token' => null,
                    'notify_mail' => false,
                    'notify_sms' => false,
                    'anonymized_at' => now(),
                ])->save();
                $user->syncRoles([]);
            } else {
                $user->delete();
            }
        });

        AuditLog::record('account.deleted', null, ['user_id' => $id, 'anonymized' => $keepAccounting]);
    }

    private function deleteFiles(User $user): void
    {
        foreach ($user->documents as $document) {
            Storage::disk('local')->delete($document->path);
        }

        foreach ($user->vehicles as $vehicle) {
            if ($vehicle->photo_path) {
                Storage::disk('public')->delete($vehicle->photo_path);
            }
        }

        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
        }
    }
}