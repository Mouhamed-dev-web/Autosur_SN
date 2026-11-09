<?php

namespace App\Http\Controllers\Client;

use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Models\TechnicalVisit;
use App\Models\Transaction;
use App\Services\PaymentService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $transactions = $request->user()->transactions()
            ->with('payable')
            ->latest()
            ->get();

        return view('client.payments.index', compact('transactions'));
    }

    /** Paiement des frais d'une visite technique. Le montant n'est PAS lu dans la requête. */
    public function pay(Request $request, TechnicalVisit $visit, PaymentService $payments)
    {
        Gate::authorize('pay', $visit);

        $data = $request->validate([
            'provider' => ['required', Rule::enum(PaymentProvider::class)],
        ]);

        try {
            $url = $payments->start($request->user(), $visit, PaymentProvider::from($data['provider']));
        } catch (DomainException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return redirect()->away($url);
    }

    /** Page de retour : affiche l'état, sans rien valider (seul le webhook valide). */
    public function result(Transaction $transaction)
    {
        Gate::authorize('view', $transaction);

        return view('client.payments.result', [
            'transaction' => $transaction->load('payable'),
        ]);
    }
}