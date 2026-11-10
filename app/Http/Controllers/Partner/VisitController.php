<?php

namespace App\Http\Controllers\Partner;

use App\Enums\PartnerType;
use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Partner\Concerns\ResolvesPartner;
use App\Models\TechnicalVisit;
use App\Services\TechnicalVisitService;
use DomainException;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    use ResolvesPartner;

    public function __construct(private TechnicalVisitService $service) {}

    public function index(Request $request)
    {
        $partner = $this->partner($request, PartnerType::GARAGE);

        $available = $partner->is_validated
            ? TechnicalVisit::with('vehicle')
                ->where('status', VisitStatus::REQUESTED->value)
                ->whereNull('partner_id')
                ->where('scheduled_at', '>', now())
                ->oldest('scheduled_at')
                ->get()
            : collect();

        $mine = $partner->visits()
            ->with(['vehicle', 'user'])
            ->whereIn('status', [VisitStatus::REQUESTED->value, VisitStatus::CONFIRMED->value])
            ->orderBy('scheduled_at')
            ->get();

        $history = $partner->visits()
            ->with(['vehicle', 'rating'])
            ->whereIn('status', [VisitStatus::COMPLETED->value, VisitStatus::CANCELLED->value])
            ->latest('scheduled_at')
            ->limit(10)
            ->get();

        return view('partner.visits.index', compact('partner', 'available', 'mine', 'history'));
    }

    public function confirm(Request $request, TechnicalVisit $visit)
    {
        $partner = $this->partner($request, PartnerType::GARAGE);

        // une demande attribuée à un autre garage n'est pas la vôtre
        abort_unless($visit->partner_id === null || $visit->partner_id === $partner->id, 403);

        return $this->run(fn () => $this->service->confirmBy($visit, $partner), 'Rendez-vous confirmé. Le client est prévenu.');
    }

    public function reject(Request $request, TechnicalVisit $visit)
    {
        $partner = $this->partner($request, PartnerType::GARAGE);
        abort_unless($visit->partner_id === $partner->id, 403);

        return $this->run(fn () => $this->service->rejectBy($visit, $partner), 'Rendez-vous refusé. Le client est prévenu.');
    }

    public function complete(Request $request, TechnicalVisit $visit)
    {
        $partner = $this->partner($request, PartnerType::GARAGE);
        abort_unless($visit->partner_id === $partner->id, 403);

        return $this->run(fn () => $this->service->completeBy($visit, $partner), 'Visite terminée. Les dates du véhicule sont mises à jour.');
    }

    private function run(\Closure $action, string $success)
    {
        try {
            $action();
        } catch (DomainException $e) {
            return back()->withErrors(['partner' => $e->getMessage()]);
        }

        return back()->with('status', $success);
    }
}