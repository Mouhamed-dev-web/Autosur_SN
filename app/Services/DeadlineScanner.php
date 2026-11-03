<?php

namespace App\Services;

use App\Enums\ReminderType;
use App\Models\Document;
use App\Models\Reminder;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\DeadlineReminderNotification;
use Carbon\CarbonInterface;

class DeadlineScanner
{
    /** Seuils de rappel, en jours avant l'échéance. */
    public const OFFSETS = [0, 7, 30];

    /** Retourne le nombre de rappels créés (donc de notifications envoyées). */
    public function run(?CarbonInterface $today = null): int
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $from = $today->toDateString();
        $to = $today->copy()->addDays(max(self::OFFSETS))->toDateString();
        $created = 0;

        Vehicle::with('user')
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('insurance_expires_at', [$from, $to])
                    ->orWhereBetween('technical_visit_expires_at', [$from, $to]);
            })
            ->each(function (Vehicle $vehicle) use ($today, &$created) {
                $dates = [
                    [ReminderType::INSURANCE, $vehicle->insurance_expires_at],
                    [ReminderType::TECHNICAL_VISIT, $vehicle->technical_visit_expires_at],
                ];

                foreach ($dates as [$type, $due]) {
                    if ($due && $this->remind($type, $due, $today, $vehicle->user, $vehicle, null)) {
                        $created++;
                    }
                }
            });

        Document::with(['user', 'vehicle'])
            ->whereBetween('expires_at', [$from, $to])
            ->each(function (Document $document) use ($today, &$created) {
                if ($this->remind(ReminderType::DOCUMENT, $document->expires_at, $today, $document->user, $document->vehicle, $document)) {
                    $created++;
                }
            });

        return $created;
    }

    /** Plus petit seuil qui couvre le délai restant, ou null si trop tôt ou dépassé. */
    public function offsetFor(int $daysLeft): ?int
    {
        if ($daysLeft < 0) {
            return null;
        }

        foreach (self::OFFSETS as $offset) { // du plus petit au plus grand
            if ($daysLeft <= $offset) {
                return $offset;
            }
        }

        return null;
    }

    private function remind(
        ReminderType $type,
        CarbonInterface $due,
        CarbonInterface $today,
        User $user,
        ?Vehicle $vehicle,
        ?Document $document,
    ): bool {
        $daysLeft = (int) $today->diffInDays($due->copy()->startOfDay(), false);
        $offset = $this->offsetFor($daysLeft);

        if ($offset === null) {
            return false;
        }

        $key = implode(':', [
            $type->value,
            $vehicle?->id ?? 0,
            $document?->id ?? 0,
            $due->toDateString(),
            $offset,
        ]);

        $reminder = Reminder::firstOrCreate(
            ['dedupe_key' => $key],
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle?->id,
                'document_id' => $document?->id,
                'type' => $type,
                'due_date' => $due->toDateString(),
                'days_before' => $offset,
            ],
        );

        if (! $reminder->wasRecentlyCreated) {
            return false; // déjà notifié pour ce seuil
        }

        $user->notify(new DeadlineReminderNotification($reminder));

        return true;
    }
}