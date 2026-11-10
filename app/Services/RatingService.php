<?php

namespace App\Services;

use App\Enums\SosStatus;
use App\Enums\VisitStatus;
use App\Models\Rating;
use App\Models\SosRequest;
use App\Models\TechnicalVisit;
use App\Models\User;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;

class RatingService
{
    public function rate(User $user, TechnicalVisit|SosRequest $rateable, int $score, ?string $comment = null): Rating
    {
        $done = $rateable instanceof TechnicalVisit
            ? $rateable->status === VisitStatus::COMPLETED
            : $rateable->status === SosStatus::COMPLETED;

        if (! $done || ! $rateable->partner_id) {
            throw new DomainException('Vous pourrez noter ce prestataire une fois l\'intervention terminée.');
        }

        if ($rateable->rating()->exists()) {
            throw new DomainException('Vous avez déjà noté cette intervention.');
        }

        try {
            return Rating::create([
                'user_id' => $user->id,
                'partner_id' => $rateable->partner_id,
                'rateable_type' => $rateable->getMorphClass(),
                'rateable_id' => $rateable->getKey(),
                'score' => max(1, min(5, $score)),
                'comment' => $comment ?: null,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw new DomainException('Vous avez déjà noté cette intervention.');
        }
    }
}