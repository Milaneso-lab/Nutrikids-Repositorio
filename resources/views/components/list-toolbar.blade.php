@props(['action', 'method' => 'GET'])

<form action="{{ $action }}" method="{{ $method }}" class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        {{ $slot }}
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">
                <i class="fas fa-filter mr-1"></i>Filtrar
            </button>
            <a href="{{ $action }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 text-sm">Limpiar</a>
        </div>
    </div>
</form>
