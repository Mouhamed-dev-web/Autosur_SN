
@php
    $input = 'mt-2 block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition duration-200 placeholder:text-gray-400 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10';

    $date = fn ($field) => old($field, optional($vehicle->{$field})->format('Y-m-d'));
@endphp

{{-- ERREURS --}}
@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
        <div class="flex items-center gap-2">
            <span class="text-lg">⚠️</span>
            <h3 class="font-semibold text-red-800">
                Veuillez corriger les erreurs suivantes :
            </h3>
        </div>

        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


{{-- SECTION 1 : INFORMATIONS DU VÉHICULE --}}
<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

    <div class="border-b border-gray-100 bg-gray-50 px-5 py-5 sm:px-7">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-xl">
                🚗
            </div>

            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Informations du véhicule
                </h2>
                <p class="text-sm text-gray-500">
                    Renseignez les caractéristiques de votre véhicule.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-7">

        {{-- Immatriculation --}}
        <div>
            <label for="plate" class="text-sm font-semibold text-gray-700">
                Immatriculation <span class="text-red-500">*</span>
            </label>

            <input
                id="plate"
                name="plate"
                type="text"
                value="{{ old('plate', $vehicle->plate) }}"
                placeholder="DK-1234-AB"
                required
                class="{{ $input }} uppercase"
            >
        </div>

        {{-- Numéro de châssis --}}
        <div>
            <label for="vin" class="text-sm font-semibold text-gray-700">
                Numéro de châssis
            </label>

            <input
                id="vin"
                name="vin"
                type="text"
                value="{{ old('vin', $vehicle->vin) }}"
                maxlength="17"
                placeholder="Numéro de châssis"
                class="{{ $input }} uppercase"
            >
        </div>

        {{-- Marque --}}
        <div>
            <label for="brand" class="text-sm font-semibold text-gray-700">
                Marque <span class="text-red-500">*</span>
            </label>

            <input
                id="brand"
                name="brand"
                type="text"
                value="{{ old('brand', $vehicle->brand) }}"
                placeholder="Ex : Toyota, Peugeot..."
                required
                class="{{ $input }}"
            >
        </div>

        {{-- Modèle --}}
        <div>
            <label for="model" class="text-sm font-semibold text-gray-700">
                Modèle <span class="text-red-500">*</span>
            </label>

            <input
                id="model"
                name="model"
                type="text"
                value="{{ old('model', $vehicle->model) }}"
                placeholder="Ex : Corolla, 208..."
                required
                class="{{ $input }}"
            >
        </div>

        {{-- Année --}}
        <div>
            <label for="year" class="text-sm font-semibold text-gray-700">
                Année de fabrication <span class="text-red-500">*</span>
            </label>

            <input
                id="year"
                name="year"
                type="number"
                min="1950"
                max="{{ now()->year + 1 }}"
                value="{{ old('year', $vehicle->year) }}"
                placeholder="Ex : 2022"
                required
                class="{{ $input }}"
            >
        </div>

        {{-- Couleur --}}
        <div>
            <label for="color" class="text-sm font-semibold text-gray-700">
                Couleur
            </label>

            <input
                id="color"
                name="color"
                type="text"
                value="{{ old('color', $vehicle->color) }}"
                placeholder="Ex : Noir, blanc, gris..."
                class="{{ $input }}"
            >
        </div>

    </div>
</div>


{{-- SECTION 2 : PHOTO DU VÉHICULE --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

    <div class="border-b border-gray-100 bg-gray-50 px-5 py-5 sm:px-7">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-xl">
                📸
            </div>

            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Photo du véhicule
                </h2>
                <p class="text-sm text-gray-500">
                    Ajoutez une image pour identifier facilement votre véhicule.
                </p>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-7">

        @if ($vehicle->photoUrl())
            <div class="mb-5">
                <p class="mb-2 text-sm font-semibold text-gray-700">
                    Photo actuelle
                </p>

                <img
                    src="{{ $vehicle->photoUrl() }}"
                    alt="Photo actuelle du véhicule"
                    class="h-40 w-full rounded-xl border border-gray-200 object-cover sm:w-64"
                >
            </div>
        @endif

        <label for="photo" class="mb-2 block text-sm font-semibold text-gray-700">
            Choisir une nouvelle photo
        </label>

        <input
            id="photo"
            name="photo"
            type="file"
            accept="image/png,image/jpeg"
            class="block w-full cursor-pointer rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-600 file:mr-4 file:border-0 file:bg-emerald-700 file:px-4 file:py-3 file:font-semibold file:text-white hover:file:bg-emerald-800"
        >

        <p class="mt-2 text-xs text-gray-400">
            Formats acceptés : JPG et PNG. Taille maximale : 2 Mo.
        </p>

    </div>
</div>


{{-- SECTION 3 : ASSURANCE --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

    <div class="border-b border-gray-100 bg-gray-50 px-5 py-5 sm:px-7">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-xl">
                🛡️
            </div>

            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Assurance
                </h2>
                <p class="text-sm text-gray-500">
                    Informations relatives à votre assurance automobile.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-7">

        {{-- Statut --}}
        <div>
            <label for="insurance_status" class="text-sm font-semibold text-gray-700">
                Statut de l'assurance <span class="text-red-500">*</span>
            </label>

            <select
                id="insurance_status"
                name="insurance_status"
                required
                class="{{ $input }}"
            >
                @foreach (\App\Enums\InsuranceStatus::cases() as $status)

                    <option
                        value="{{ $status->value }}"
                        @selected(old('insurance_status', $vehicle->insurance_status?->value) === $status->value)
                    >
                        {{ $status->label() }}
                    </option>

                @endforeach
            </select>
        </div>

        {{-- Date expiration --}}
        <div>
            <label for="insurance_expires_at" class="text-sm font-semibold text-gray-700">
                Date d'expiration
            </label>

            <input
                id="insurance_expires_at"
                name="insurance_expires_at"
                type="date"
                value="{{ $date('insurance_expires_at') }}"
                class="{{ $input }}"
            >
        </div>

    </div>
</div>


{{-- SECTION 4 : VISITE TECHNIQUE --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

    <div class="border-b border-gray-100 bg-gray-50 px-5 py-5 sm:px-7">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-100 text-xl">
                🔧
            </div>

            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Visite technique
                </h2>
                <p class="text-sm text-gray-500">
                    Suivez les dates de contrôle technique de votre véhicule.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-7">

        {{-- Dernière visite --}}
        <div>
            <label for="technical_visit_last_at" class="text-sm font-semibold text-gray-700">
                Date de la dernière visite
            </label>

            <input
                id="technical_visit_last_at"
                name="technical_visit_last_at"
                type="date"
                value="{{ $date('technical_visit_last_at') }}"
                class="{{ $input }}"
            >
        </div>

        {{-- Prochaine échéance --}}
        <div>
            <label for="technical_visit_expires_at" class="text-sm font-semibold text-gray-700">
                Prochaine échéance
            </label>

            <input
                id="technical_visit_expires_at"
                name="technical_visit_expires_at"
                type="date"
                value="{{ $date('technical_visit_expires_at') }}"
                class="{{ $input }}"
            >
        </div>

    </div>
</div>

