<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Transaction;
use App\Services\Payment\SimulatedGateway;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Page du « faux opérateur ». Uniquement en développement. */
class PaymentSimulationController extends Controller
{
    private function guard(Request $request, Transaction $transaction): void
    {
        abort_if(app()->isProduction(), 404);
        abort_unless(config('payments.driver') === 'simulation', 404);
        abort_unless($request->user()->id === $transaction->user_id, 403);
    }

    public function show(Request $request, Transaction $transaction)
    {
        $this->guard($request, $transaction);

        if ($transaction->status !== PaymentStatus::PENDING) {
            return redirect()->route('client.payments.result', $transaction);
        }

        return view('payments.simulation', compact('transaction'));
    }

    public function complete(Request $request, Transaction $transaction, PaymentService $payments)
    {
        $this->guard($request, $transaction);

        $outcome = $request->input('outcome') === 'paid' ? 'paid' : 'failed';

        $body = json_encode([
            'event_id' => (string) Str::uuid(),
            'reference' => $transaction->reference,
            'status' => $outcome,
            'amount' => $transaction->amount,
        ]);

        $gateway = app(SimulatedGateway::class);

        // Même chemin qu'un vrai opérateur : webhook signé, vérifié par le service
        $webhook = Request::create(
            route('webhooks.payment', 'simulation'),
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGNATURE' => $gateway->sign($body)],
            $body,
        );

        $payments->handleWebhook('simulation', $webhook);

        return redirect()->route('client.payments.result', $transaction);
    }
}