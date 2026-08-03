@props(['estado' => 'activo'])

@php
    $label = match($estado) {
        'activo' => 'Activo',
        'borrador' => 'Borrador',
        'archivado' => 'Archivado',
        default => ucfirst($estado),
    };
    $classes = match($estado) {
        'activo' => 'bg-emerald-100 dark:bg-emerald-950/55 text-emerald-800 dark:text-emerald-100',
        'borrador' => 'bg-amber-100 dark:bg-amber-950/55 text-amber-900 dark:text-amber-100',
        'archivado' => 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200',
        default => 'bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'px-2 py-0.5 text-xs font-medium rounded-full '.$classes]) }}>{{ $label }}</span>
