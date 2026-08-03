<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700 text-sm">
        <thead class="bg-slate-100 dark:bg-slate-800">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Fecha</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Peso</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Talla</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Recomendaciones</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-slate-800">
            @forelse($evaluaciones as $evaluacion)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                    <td class="px-4 py-3">{{ optional($evaluacion->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $evaluacion->peso }}</td>
                    <td class="px-4 py-3">{{ $evaluacion->talla }}</td>
                    <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($evaluacion->recomendaciones ?: '—', 80) }}</td>
                    <td class="px-4 py-3"><a href="{{ route('nutriologo.evaluaciones.edit', $evaluacion) }}" class="text-emerald-600 hover:underline">Editar</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Sin consultas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
