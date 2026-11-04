<?php

namespace App\Http\Controllers\Client;

use App\Enums\CoverType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\InsuranceQuoteRequest;
use App\Models\InsuranceOffer;
use App\Models\InsuranceQuote;
use App\Services\InsuranceService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InsuranceController extends Controller
{
    public function index(Request $request)
    {
        $quotes = $request->user()->insuranceQuotes()
            ->with(['vehicle', 'subscription.offer'])
            ->latest()
            ->get();

        return view('client.insurance.index', compact('quotes'));
    }

    public function create(Request $request)
    {
        return view('client.insurance.create', [
            'vehicles' => $request->user()->vehicles()->orderBy('plate')->get(),
            'selectedVehicle' => $request->integer('vehicle') ?: null,
            'covers' => CoverType::cases(),
        ]);
    }

    public function store(InsuranceQuoteRequest $request)
    {
        $data = $request->validated();

        if (! CoverType::from($data['cover_type'])->needsValue()) {
            $data['vehicle_value'] = null;
        }

        $quote = $request->user()->insuranceQuotes()->create($data);

        return redirect()->route('client.insurance.show', $quote);
    }

    public function show(InsuranceQuote $quote, InsuranceService $service)
    {
        Gate::authorize('view', $quote);

        $quote->load(['vehicle', 'subscription.offer']);

        return view('client.insurance.show', [
            'quote' => $quote,
            'offers' => $service->offersFor($quote),
        ]);
    }

    public function subscribe(Request $request, InsuranceQuote $quote, InsuranceOffer $offer, InsuranceService $service)
    {
        Gate::authorize('view', $quote);

        $request->validate(
            ['consent' => ['accepted']],
            ['consent.accepted' => 'Vous devez accepter le partage de vos coordonnées avec l\'assureur.'],
        );

        try {
            $service->subscribe($quote, $offer);
        } catch (DomainException $e) {
            return back()->withErrors(['subscribe' => $e->getMessage()]);
        }

        return redirect()->route('client.insurance.show', $quote)
            ->with('status', 'Demande de souscription enregistrée.');
    }
}