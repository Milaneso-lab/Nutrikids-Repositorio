@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4']) }}>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-slate-100">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if(trim($slot ?? '') !== '')
        <div class="flex flex-wrap gap-2 shrink-0">{{ $slot }}</div>
    @endif
</div>
