<?php

namespace App\Http\Controllers\Partner;

use App\Enums\PartnerType;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\SosRequest;
use App\Services\SosService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SosController extends Controller
{
    public function __construct(private SosService $service) {}

    private function partner(Request $request): Partner
    {
        $partner = $request->user()->partner;

        abort_unless($partner && $partner->type === PartnerType::DEPANNEUR, 403);

        return $partner;
    }

    public function index(Request $request)
    {
        $partner = $this->partner($request);

        return view('partner.sos.index', [
            'partner' => $partner,
            'current' => $this->service->currentJob($partner),
            'available' => $this->service->availableFor($partner),
        ]);
    }

    /** Interrogé toutes les 10 secondes : liste des demandes visibles. */
    public function available(Request $request)
    {
        $partner = $this->partner($request);

        return response()->json([
            'ids' => $this->service->availableFor($partner)->pluck('request.id')->values()->all(),
        ]);
    }

    public function toggle(Request $request)
    {
        $partner = $this->partner($request);

        abort_unless($partner->is_validated, 403);

        $partner->update(['is_available' => ! $partner->is_available]);

        return back()->with('status', $partner->is_available
            ? 'Vous êtes disponible : les demandes proches apparaissent ici.'
            : 'Vous êtes indisponible.');
    }

    public function show(Request $request, SosRequest $sosRequest)
    {
        $this->partner($request);
        Gate::authorize('manage', $sosRequest);

        $sosRequest->load(['vehicle', 'user']);

        return view('partner.sos.show', ['sos' => $sosRequest]);
    }

    public function accept(Request $request, SosRequest $sosRequest)
    {
        $partner = $this->partner($request);

        try {
            $this->service->accept($sosRequest, $partner);
        } catch (DomainException $e) {
            return back()->withErrors(['sos' => $e->getMessage()]);
        }

        return redirect()->route('partner.sos.show', $sosRequest)
            ->with('status', 'Demande acceptée. Contactez le client et rendez-vous sur place.');
    }

    public function decline(Request $request, SosRequest $sosRequest)
    {
        $this->service->decline($sosRequest, $this->partner($request));

        return back()->with('status', 'Demande masquée.');
    }

    public function arrive(Request $request, SosRequest $sosRequest)
    {
        $partner = $this->partner($request);
        Gate::authorize('manage', $sosRequest);

        try {
            $this->service->arrive($sosRequest, $partner);
        } catch (DomainException $e) {
            return back()->withErrors(['sos' => $e->getMessage()]);
        }

        return back()->with('status', 'Arrivée signalée au client.');
    }

    public function complete(Request $request, SosRequest $sosRequest)
    {
        $partner = $this->partner($request);
        Gate::authorize('manage', $sosRequest);

        try {
            $this->service->complete($sosRequest, $partner);
        } catch (DomainException $e) {
            return back()->withErrors(['sos' => $e->getMessage()]);
        }

        return redirect()->route('partner.sos.index')->with('status', 'Intervention terminée.');
    }
}