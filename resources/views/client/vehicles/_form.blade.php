@php
    $input = 'mt-1 w-full rounded border border-gray-300 p-2';
    $date = fn ($field) => old($field, optional($vehicle->{$field})->format('Y-m-d'));
@endphp

@if ($errors->any())
    <div class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700">
        @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
    </div>
@endif

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium" for="plate">Immatriculation *</label>
        <input id="plate" name="plate" value="{{ old('plate', $vehicle->plate) }}" placeholder="DK-1234-AB" required class="{{ $input }} uppercase">
    </div>
    <div>
        <label class="block text-sm font-medium" for="vin">N° de châssis</label>
        <input id="vin" name="vin" value="{{ old('vin', $vehicle->vin) }}" maxlength="17" class="{{ $input }} uppercase">
    </div>
    <div>
        <label class="block text-sm font-medium" for="brand">Marque *</label>
        <input id="brand" name="brand" value="{{ old('brand', $vehicle->brand) }}" required class="{{ $input }}">
    </div>
    <div>
        <label class="block text-sm font-medium" for="model">Modèle *</label>
        <input id="model" name="model" value="{{ old('model', $vehicle->model) }}" required class="{{ $input }}">
    </div>
    <div>
        <label class="block text-sm font-medium" for="year">Année *</label>
        <input id="year" name="year" type="number" min="1950" max="{{ now()->year + 1 }}" value="{{ old('year', $vehicle->year) }}" required class="{{ $input }}">
    </div>
    <div>
        <label class="block text-sm font-medium" for="color">Couleur</label>
        <input id="color" name="color" value="{{ old('color', $vehicle->color) }}" class="{{ $input }}">
    </div>
</div>

<h2 class="mb-2 mt-6 font-semibold">Assurance</h2>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium" for="insurance_status">Statut *</label>
        <select id="insurance_status" name="insurance_status" class="{{ $input }}">
            @foreach (\App\Enums\InsuranceStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('insurance_status', $vehicle->insurance_status?->value) === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium" for="insurance_expires_at">Expiration de l'assurance</label>
        <input id="insurance_expires_at" name="insurance_expires_at" type="date" value="{{ $date('insurance_expires_at') }}" class="{{ $input }}">
    </div>
</div>

<h2 class="mb-2 mt-6 font-semibold">Visite technique</h2>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium" for="technical_visit_last_at">Dernière visite</label>
        <input id="technical_visit_last_at" name="technical_visit_last_at" type="date" value="{{ $date('technical_visit_last_at') }}" class="{{ $input }}">
    </div>
    <div>
        <label class="block text-sm font-medium" for="technical_visit_expires_at">Prochaine échéance</label>
        <input id="technical_visit_expires_at" name="technical_visit_expires_at" type="date" value="{{ $date('technical_visit_expires_at') }}" class="{{ $input }}">
    </div>
</div>