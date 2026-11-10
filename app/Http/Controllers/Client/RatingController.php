<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SosRequest;
use App\Models\TechnicalVisit;
use App\Services\RatingService;
use DomainException;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function visit(Request $request, TechnicalVisit $visit, RatingService $ratings)
    {
        abort_unless($visit->user_id === $request->user()->id, 403);

        return $this->store($request, $visit, $ratings);
    }

    public function sos(Request $request, SosRequest $sosRequest, RatingService $ratings)
    {
        abort_unless($sosRequest->user_id === $request->user()->id, 403);

        return $this->store($request, $sosRequest, $ratings);
    }

    private function store(Request $request, TechnicalVisit|SosRequest $rateable, RatingService $ratings)
    {
        $data = $request->validate([
            'score' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ], [
            'score.required' => 'Choisissez une note de 1 à 5.',
            'score.between' => 'La note doit être comprise entre 1 et 5.',
        ]);

        try {
            $ratings->rate($request->user(), $rateable, (int) $data['score'], $data['comment'] ?? null);
        } catch (DomainException $e) {
            return back()->withErrors(['rating' => $e->getMessage()]);
        }

        return back()->with('status', 'Merci pour votre avis !');
    }
}