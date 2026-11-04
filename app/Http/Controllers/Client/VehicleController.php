<?php

namespace App\Http\Controllers\Client;

use Illuminate\Support\Facades\Storage;
use App\Enums\InsuranceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\VehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = $request->user()->vehicles()->latest()->get();

        return view('client.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $vehicle = new Vehicle(['insurance_status' => InsuranceStatus::UNKNOWN]);

        return view('client.vehicles.create', compact('vehicle'));
    }

   public function store(VehicleRequest $request)
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('vehicle-photos', 'public');
        }

        $vehicle = $request->user()->vehicles()->create($data);

        return redirect()->route('client.vehicles.show', $vehicle)
            ->with('status', 'Véhicule ajouté.');
    }

    public function show(Vehicle $vehicle)
    {
        Gate::authorize('view', $vehicle);

        return view('client.vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        Gate::authorize('update', $vehicle);

        return view('client.vehicles.edit', compact('vehicle'));
    }

   public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        Gate::authorize('update', $vehicle);

        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            if ($vehicle->photo_path) {
                Storage::disk('public')->delete($vehicle->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('vehicle-photos', 'public');
        }

        $vehicle->update($data);

        return redirect()->route('client.vehicles.show', $vehicle)
            ->with('status', 'Véhicule mis à jour.');
    }

    public function destroy(Vehicle $vehicle)
    {
        Gate::authorize('delete', $vehicle);

        $vehicle->delete();

        return redirect()->route('client.vehicles.index')
            ->with('status', 'Véhicule supprimé.');
    }
}