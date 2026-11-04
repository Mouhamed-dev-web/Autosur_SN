<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\TechnicalVisitRequest;
use App\Models\TechnicalVisit;
use App\Services\TechnicalVisitService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TechnicalVisitController extends Controller
{
    public function index(Request $request)
    {
        $visits = $request->user()->technicalVisits()
            ->with('vehicle')
            ->latest('scheduled_at')
            ->get();

        return view('client.visits.index', compact('visits'));
    }

    public function create(Request $request, TechnicalVisitService $service)
    {
        return view('client.visits.create', [
            'vehicles' => $request->user()->vehicles()->orderBy('plate')->get(),
            'selectedVehicle' => $request->integer('vehicle') ?: null,
            'fee' => $service->fee(),
        ]);
    }

    public function store(TechnicalVisitRequest $request, TechnicalVisitService $service)
    {
        $user = $request->user();
        $vehicle = $user->vehicles()->findOrFail($request->validated('vehicle_id'));

        $service->create(
            $user,
            $vehicle,
            Carbon::parse($request->validated('scheduled_at')),
            $request->validated('notes'),
        );

        return redirect()->route('client.visits.index')
            ->with('status', 'Demande de rendez-vous envoyée. Vous serez prévenu dès sa confirmation.');
    }

    public function cancel(TechnicalVisit $visit, TechnicalVisitService $service)
    {
        Gate::authorize('cancel', $visit);

        if (! $visit->canBeCancelled()) {
            return back()->withErrors(['visit' => 'Cette visite ne peut plus être annulée.']);
        }

        $service->cancel($visit);

        return back()->with('status', 'Rendez-vous annulé.');
    }
}