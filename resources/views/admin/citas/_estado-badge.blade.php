@php
    $map = [
        'pendiente' => ['bg-amber-100 dark:bg-amber-950/70', 'text-amber-900 dark:text-amber-50', 'Pendiente'],
        'asignada' => ['bg-blue-100 dark:bg-blue-950/60', 'text-blue-900 dark:text-blue-50', 'Asignada'],
        'confirmada' => ['bg-green-100 dark:bg-emerald-950/55', 'text-green-900 dark:text-emerald-50', 'Confirmada'],
        'cancelada' => ['bg-red-100 dark:bg-red-950/55', 'text-red-900 dark:text-red-50', 'Cancelada'],
    ];
    [$bg, $fg, $label] = $map[$estado] ?? ['bg-gray-100 dark:bg-slate-700', 'text-gray-800 dark:text-slate-100', $estado];
@endphp
<span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold ring-1 ring-black/5 dark:ring-white/10 {{ $bg }} {{ $fg }}">{{ $label }}</span>
