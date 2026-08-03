@props([
    'label',
    'value',
    'icon' => 'fa-chart-line',
    'color' => 'emerald',
    'hint' => null,
    'href' => null,
])

@php
    $colors = [
        'emerald' => ['border' => 'border-emerald-500', 'bg' => 'bg-emerald-100 dark:bg-emerald-900/40', 'icon' => 'text-emerald-600 dark:text-emerald-300'],
        'sky' => ['border' => 'border-sky-500', 'bg' => 'bg-sky-100 dark:bg-sky-900/40', 'icon' => 'text-sky-600 dark:text-sky-300'],
        'amber' => ['border' => 'border-amber-500', 'bg' => 'bg-amber-100 dark:bg-amber-900/40', 'icon' => 'text-amber-600 dark:text-amber-300'],
        'violet' => ['border' => 'border-violet-500', 'bg' => 'bg-violet-100 dark:bg-violet-900/40', 'icon' => 'text-violet-600 dark:text-violet-300'],
        'rose' => ['border' => 'border-rose-500', 'bg' => 'bg-rose-100 dark:bg-rose-900/40', 'icon' => 'text-rose-600 dark:text-rose-300'],
    ];
    $c = $colors[$color] ?? $colors['emerald'];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'block bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border-l-4 '.$c['border'].' border border-slate-200/80 dark:border-slate-800 transition hover:shadow-lg']) }}
>
    <div class="flex items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="text-gray-600 dark:text-slate-400 text-sm">{{ $label }}</p>
            <p class="text-3xl font-bold text-gray-800 dark:text-slate-100 mt-2 truncate">{{ $value }}</p>
            @if($hint)
                <p class="text-xs text-gray-500 dark:text-slate-500 mt-1">{{ $hint }}</p>
            @endif
        </div>
        <div class="{{ $c['bg'] }} p-4 rounded-full shrink-0">
            <i class="fas {{ $icon }} {{ $c['icon'] }} text-2xl"></i>
        </div>
    </div>
</{{ $tag }}>
