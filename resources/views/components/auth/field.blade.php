@props(['name', 'label', 'type' => 'text', 'icon' => null, 'hint' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <div class="relative mt-1">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">{{ $icon }}</span>
        @endif
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
               @if ($type !== 'password') value="{{ old($name) }}" @endif
               {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-gray-300 bg-gray-50 py-2.5 pr-3 text-sm focus:border-emerald-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200 '.($icon ? 'pl-10' : 'pl-3')]) }}>
    </div>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
</div>