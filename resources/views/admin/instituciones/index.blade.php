@extends('layouts.app')

@section('title', 'Instituciones - Administrador')

@section('page-title', 'Instituciones aliadas')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <x-page-header title="Gestión de instituciones" subtitle="Escuelas, clínicas y organizaciones vinculadas al programa NutriKids." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-lg shadow-md border border-slate-200/80 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-700 dark:text-slate-200">Institución</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-700 dark:text-slate-200">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-700 dark:text-slate-200">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-700 dark:text-slate-200"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-800">
                        @forelse($instituciones as $inst)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $inst['nombre'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-slate-500">{{ $inst['ciudad'] ?? '—' }} · {{ $inst['contacto_email'] ?? 'Sin contacto' }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm capitalize text-gray-700 dark:text-slate-300">{{ $inst['tipo'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($inst['activa'] ?? true)
                                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 dark:bg-emerald-950/50 text-green-800 dark:text-emerald-200">Activa</span>
                                    @else
                                        <span class="text-xs px-2 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">Inactiva</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('admin.instituciones.toggle', $inst['id']) }}" method="POST">@csrf
                                        <button type="submit" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline">Alternar estado</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state icon="fa-school" title="Sin instituciones" message="Registra la primera institución aliada." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800 h-fit">
            <h3 class="font-semibold text-gray-800 dark:text-slate-100 mb-4">Registrar institución</h3>
            <form action="{{ route('admin.instituciones.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm text-gray-700 dark:text-slate-300 mb-1">Nombre</label>
                    <input name="nombre" required class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 dark:text-slate-300 mb-1">Tipo</label>
                    <select name="tipo" required class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600 dark:text-slate-100">
                        <option value="escuela">Escuela</option>
                        <option value="clinica">Clínica</option>
                        <option value="ong">ONG</option>
                        <option value="gobierno">Gobierno</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 dark:text-slate-300 mb-1">Ciudad</label>
                    <input name="ciudad" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 dark:text-slate-300 mb-1">Email de contacto</label>
                    <input type="email" name="contacto_email" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600 dark:text-slate-100">
                </div>
                <button type="submit" class="w-full py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium">Guardar</button>
            </form>
        </div>
    </div>
@endsection
