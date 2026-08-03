@props(['estado' => 'activo'])

@php
    $label = match($estado) {
        'activo' => 'Activo',
        'seguimiento' => 'En seguimiento',
        'inactivo' => 'Inactivo',
        'alta' => 'Alta clínica',
        default => ucfirst($estado),
    };
    $classes = match($estado) {
        'activo' => 'bg-emerald-100 dark:bg-emerald-950/55 text-emerald-900 dark:text-emerald-100',
        'seguimiento' => 'bg-sky-100 dark:bg-sky-950/55 text-sky-900 dark:text-sky-100',
        'inactivo' => 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200',
        'alta' => 'bg-violet-100 dark:bg-violet-950/55 text-violet-900 dark:text-violet-100',
        default => 'bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'px-2 py-1 text-xs font-semibold rounded-full ring-1 ring-black/5 dark:ring-white/10 '.$classes]) }}>
    {{ $label }}
</span>
