@php
    $variant = $variant ?? 'hero';
    [$from, $to] = $variant === 'hero'
        ? ['rgba(6, 40, 30, .45)', 'rgba(6, 40, 30, .65)']
        : ['rgba(249, 250, 251, .90)', 'rgba(249, 250, 251, .94)'];
    $photo = asset('images/car-bg.jpg');
@endphp

<div class="car-bg" aria-hidden="true">
    <div class="car-bg__img" style="--bg-from: {{ $from }}; --bg-to: {{ $to }}; --bg-photo: url('{{ $photo }}');"></div>
</div>

<style>
    .car-bg {
        position: fixed;
        inset: 0;
        z-index: -1;
        overflow: hidden;
        background: #064e3b;
    }
    .car-bg__img {
        position: absolute;
        inset: -6%;
        background:
            linear-gradient(var(--bg-from), var(--bg-to)),
            var(--bg-photo) center / cover no-repeat,
            linear-gradient(135deg, #065f46, #047857 50%, #0f172a);
        will-change: transform;
        animation: car-bg-move 28s ease-in-out infinite alternate;
    }
    @keyframes car-bg-move {
        from { transform: scale(1) translate3d(0, 0, 0); }
        to   { transform: scale(1.12) translate3d(-2%, -1.5%, 0); }
    }
    @media (prefers-reduced-motion: reduce) {
        .car-bg__img { animation: none; }
    }
</style>