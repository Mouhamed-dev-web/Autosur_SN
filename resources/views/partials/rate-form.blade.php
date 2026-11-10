<form method="POST" action="{{ $action }}" class="mt-3 border-t pt-3 text-sm">
    @csrf
    <p class="font-medium">{{ $label ?? 'Notez ce prestataire' }}</p>

    <div class="mt-2 flex flex-wrap items-center gap-3">
        @for ($i = 1; $i <= 5; $i++)
            <label class="flex items-center gap-1">
                <input type="radio" name="score" value="{{ $i }}" required> {{ $i }} ★
            </label>
        @endfor
    </div>

    <textarea name="comment" rows="2" maxlength="500" placeholder="Votre avis (facultatif)"
              class="mt-2 w-full rounded border border-gray-300 p-2"></textarea>

    <button class="mt-2 rounded bg-emerald-700 px-3 py-1.5 text-white">Envoyer ma note</button>
</form>