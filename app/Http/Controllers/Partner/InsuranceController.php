<?php

namespace App\Http\Controllers\Partner;

use App\Enums\PartnerType;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Partner\Concerns\ResolvesPartner;
use App\Models\InsuranceSubscription;
use App\Services\InsuranceService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    use ResolvesPartner;

    public function __construct(private InsuranceService $service) {}

    public function index(Request $request)
    {
        $partner = $this->partner($request, PartnerType::ASSURANCE);

        $subscriptions = InsuranceSubscription::with(['user', 'quote.vehicle', 'offer'])
            ->whereHas('offer', fn ($q) => $q->where('partner_id', $partner->id))
            ->latest()
            ->get();

        $active = $subscriptions->where('status', SubscriptionStatus::ACTIVE);

        return view('partner.insurance.index', [
            'partner' => $partner,
            'subscriptions' => $subscriptions,
            'activeCount' => $active->count(),
            'commissionTotal' => $active->sum('commission_amount'),
        ]);
    }

    public function activate(Request $request, InsuranceSubscription $subscription)
    {
        $partner = $this->partner($request, PartnerType::ASSURANCE);
        abort_unless($subscription->offer->partner_id === $partner->id, 403);

        $data = $request->validate([
            'start_date' => ['nullable', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addDays(30)->toDateString()],
        ], [
            'start_date.after_or_equal' => 'La date de début ne peut pas être passée.',
            'start_date.before_or_equal' => 'La date de début doit être dans les 30 prochains jours.',
        ]);

        try {
            $this->service->activateBy(
                $subscription,
                $partner,
                isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
            );
        } catch (DomainException $e) {
            return back()->withErrors(['partner' => $e->getMessage()]);
        }

        return back()->with('status', 'Souscription activée. Le client est prévenu et son véhicule est mis à jour.');
    }

    public function decline(Request $request, InsuranceSubscription $subscription)
    {
        $partner = $this->partner($request, PartnerType::ASSURANCE);
        abort_unless($subscription->offer->partner_id === $partner->id, 403);

        try {
            $this->service->declineBy($subscription, $partner);
        } catch (DomainException $e) {
            return back()->withErrors(['partner' => $e->getMessage()]);
        }

        return back()->with('status', 'Demande refusée. Le client est prévenu.');
    }
}