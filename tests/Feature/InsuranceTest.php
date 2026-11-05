public function test_calcul_de_la_prime(): void
{
    $service = app(InsuranceService::class);
    $offer = $this->offer();
    $user = $this->client();

    $this->assertSame(74000, $service->premium($offer, $this->quote($user)));

    $secondVehicle = $this->vehicleFor($user, 'DK-2222-BB');
    $this->assertSame(37000, $service->premium($offer, $user->insuranceQuotes()->create([
        'vehicle_id' => $secondVehicle->id,
        'cover_type' => 'tiers',
        'fiscal_power' => 6,
        'duration_months' => 6,
    ])));
}