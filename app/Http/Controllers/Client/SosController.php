<?php

namespace App\Http\Controllers\Client;

use App\Enums\ProblemType;
use App\Enums\SosStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\SosRequestRequest;
use App\Models\SosRequest;
use App\Services\SosService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SosController extends Controller
{
    public function index(Request $request)
    {
        $requests = $request->user()->sosRequests()
            ->with(['vehicle', 'partner'])
            ->latest()
            ->get();

        return view('client.sos.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $open = $request->user()->sosRequests()
            ->whereIn('status', SosStatus::openValues())
            ->latest()
            ->first();

        if ($open) {
            return redirect()->route('client.sos.show', $open);
        }

        return view('client.sos.create', [
            'vehicles' => $request->user()->vehicles()->orderBy('plate')->get(),
            'problems' => ProblemType::cases(),
        ]);
    }

    public function store(SosRequestRequest $request, SosService $service)
    {
        try {
            $sos = $service->create($request->user(), $request->validated());
        } catch (DomainException $e) {
            return back()->withErrors(['sos' => $e->getMessage()])->withInput();
        }

        return redirect()->route('client.sos.show', $sos)
            ->with('status', 'Votre demande est diffusée aux dépanneurs proches.');
    }

    public function show(SosRequest $sosRequest)
    {
        Gate::authorize('view', $sosRequest);

        $sosRequest->load(['vehicle', 'partner']);

        return view('client.sos.show', ['sos' => $sosRequest]);
    }

    /** Interrogé toutes les 8 secondes par la page de suivi. */
    public function status(SosRequest $sosRequest)
    {
        Gate::authorize('view', $sosRequest);

        return response()->json([
            'status' => $sosRequest->status->value,
            'label' => $sosRequest->status->label(),
        ]);
    }

    public function cancel(SosRequest $sosRequest, SosService $service)
    {
        Gate::authorize('view', $sosRequest);

        try {
            $service->cancel($sosRequest);
        } catch (DomainException $e) {
            return back()->withErrors(['sos' => $e->getMessage()]);
        }

        return redirect()->route('client.sos.index')->with('status', 'Demande SOS annulée.');
    }
}