@extends('layouts.app')

@section('title', 'Expediente - ' . trim($paciente->nombre . ' ' . $paciente->apellidos))

@section('page-title', 'Expediente clínico')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
@php
    $tabs = [
        'general' => ['icon' => 'fa-id-card', 'label' => 'General'],
        'historia' => ['icon' => 'fa-notes-medical', 'label' => 'Historia clínica'],
        'antropometria' => ['icon' => 'fa-ruler-combined', 'label' => 'Antropometría'],
        'antecedentes' => ['icon' => 'fa-allergies', 'label' => 'Antecedentes'],
        'habitos' => ['icon' => 'fa-heart', 'label' => 'Hábitos'],
        'consultas' => ['icon' => 'fa-stethoscope', 'label' => 'Consultas'],
        'planes' => ['icon' => 'fa-utensils', 'label' => 'Planes'],
        'recomendaciones' => ['icon' => 'fa-comment-medical', 'label' => 'Recomendaciones'],
        'documentos' => ['icon' => 'fa-file-medical', 'label' => 'Documentos'],
        'seguimiento' => ['icon' => 'fa-chart-line', 'label' => 'Seguimiento'],
    ];
    $activeTab = $tab ?? 'general';
@endphp

<div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-6 border border-slate-200/80 dark:border-slate-800">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-20 h-20 bg-green-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center shrink-0">
                <i class="fas fa-child text-green-600 dark:text-emerald-300 text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-slate-100">{{ trim($paciente->nombre . ' ' . $paciente->apellidos) }}</h2>
                <p class="text-gray-600 dark:text-slate-400">
                    {{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age . ' años · ' . $paciente->fecha_nacimiento->format('d/m/Y') : 'Sin fecha de nacimiento' }}
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <x-patient-status-badge :estado="$paciente->estado_paciente ?? 'activo'" />
                    @if($resumen['clasificacion_label'] ?? null)
                        <span class="px-2 py-1 text-xs rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">{{ $resumen['clasificacion_label'] }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('nutriologo.pacientes.edit', $paciente) }}" class="px-4 py-2 bg-blue-600 dark:bg-sky-600 text-white rounded-lg hover:bg-blue-700 text-sm"><i class="fas fa-edit mr-1"></i>Editar</a>
            <a href="{{ route('nutriologo.evaluaciones.create', ['paciente_id' => $paciente->id]) }}" class="px-4 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 text-sm"><i class="fas fa-plus mr-1"></i>Evaluación</a>
            <a href="{{ route('nutriologo.menus.create', ['paciente_id' => $paciente->id]) }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm"><i class="fas fa-utensils mr-1"></i>Plan</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        <div class="bg-slate-50 dark:bg-slate-800/80 p-4 rounded-lg"><p class="text-xs text-slate-500">Peso</p><p class="text-xl font-bold">{{ $resumen['peso'] ?? '—' }} @if($resumen['peso'] ?? null) kg @endif</p></div>
        <div class="bg-slate-50 dark:bg-slate-800/80 p-4 rounded-lg"><p class="text-xs text-slate-500">Talla</p><p class="text-xl font-bold">{{ $resumen['talla_cm'] ?? '—' }} @if($resumen['talla_cm'] ?? null) cm @endif</p></div>
        <div class="bg-slate-50 dark:bg-slate-800/80 p-4 rounded-lg"><p class="text-xs text-slate-500">IMC</p><p class="text-xl font-bold">{{ isset($resumen['imc']) ? number_format($resumen['imc'], 2) : '—' }}</p></div>
        <div class="bg-slate-50 dark:bg-slate-800/80 p-4 rounded-lg"><p class="text-xs text-slate-500">Cumplimiento</p><p class="text-xl font-bold">{{ $cumplimiento !== null ? $cumplimiento.'%' : '—' }}</p></div>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-slate-200/80 dark:border-slate-800 overflow-hidden">
    <nav class="flex overflow-x-auto border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50" role="tablist" aria-label="Expediente clínico">
        @foreach($tabs as $key => $meta)
            <button type="button" role="tab" data-tab="{{ $key }}"
                class="exp-tab-btn shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === $key ? 'border-emerald-500 text-emerald-700 dark:text-emerald-300 bg-white dark:bg-slate-900' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-emerald-600' }}"
                aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                <i class="fas {{ $meta['icon'] }} mr-1"></i>{{ $meta['label'] }}
            </button>
        @endforeach
    </nav>

    <div class="p-6">
        {{-- General --}}
        <section class="exp-tab-panel {{ $activeTab !== 'general' ? 'hidden' : '' }}" data-panel="general">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-500">Nombre completo</dt><dd class="font-medium">{{ trim($paciente->nombre . ' ' . $paciente->apellidos) }}</dd></div>
                <div><dt class="text-slate-500">Estado del paciente</dt><dd><x-patient-status-badge :estado="$paciente->estado_paciente ?? 'activo'" /></dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500">Objetivo nutricional</dt><dd class="mt-1">{{ $paciente->objetivo_nutricional ?: 'Sin objetivo registrado.' }}</dd></div>
                <div><dt class="text-slate-500">Registro</dt><dd>{{ optional($paciente->created_at)->format('d/m/Y H:i') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Última evaluación</dt><dd>{{ $resumen['ultima_fecha'] ?? 'Sin evaluación' }}</dd></div>
            </dl>
        </section>

        {{-- Historia clínica --}}
        <section class="exp-tab-panel {{ $activeTab !== 'historia' ? 'hidden' : '' }}" data-panel="historia">
            <div class="prose dark:prose-invert max-w-none text-sm whitespace-pre-line">{{ $paciente->historia_clinica ?: 'No hay historia clínica registrada. Edita el expediente para agregarla.' }}</div>
        </section>

        {{-- Antropometría --}}
        <section class="exp-tab-panel {{ $activeTab !== 'antropometria' ? 'hidden' : '' }}" data-panel="antropometria">
            @if(!empty(json_decode($chartJson, true)['imc']))
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div><h4 class="font-semibold mb-2">Evolución IMC</h4><div style="height:220px"><canvas id="chartImc"></canvas></div></div>
                <div><h4 class="font-semibold mb-2">Peso (kg)</h4><div style="height:220px"><canvas id="chartPeso"></canvas></div></div>
                <div><h4 class="font-semibold mb-2">Talla (cm)</h4><div style="height:220px"><canvas id="chartTalla"></canvas></div></div>
                <div><h4 class="font-semibold mb-2">Percentiles (referencia CDC)</h4><div style="height:220px"><canvas id="chartPercentil"></canvas></div></div>
            </div>
            @endif
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-100 dark:bg-slate-800"><tr>
                        <th class="px-3 py-2 text-left">Fecha</th><th class="px-3 py-2 text-left">Peso</th><th class="px-3 py-2 text-left">Talla</th><th class="px-3 py-2 text-left">IMC</th>
                    </tr></thead>
                    <tbody>
                        @foreach($paciente->evaluaciones as $ev)
                            @php
                                $p = is_numeric(str_replace(',','.',(string)$ev->peso)) ? (float)str_replace(',','.',(string)$ev->peso) : null;
                                $t = is_numeric(str_replace(',','.',(string)$ev->talla)) ? (float)str_replace(',','.',(string)$ev->talla) : null;
                                $tm = $t ? ($t > 3 ? $t/100 : $t) : null;
                                $imcEv = ($p && $tm) ? round($p/($tm*$tm),2) : null;
                            @endphp
                            <tr><td class="px-3 py-2">{{ optional($ev->created_at)->format('d/m/Y') }}</td><td class="px-3 py-2">{{ $ev->peso }}</td><td class="px-3 py-2">{{ $ev->talla }}</td><td class="px-3 py-2">{{ $imcEv ?? '—' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Antecedentes --}}
        <section class="exp-tab-panel {{ $activeTab !== 'antecedentes' ? 'hidden' : '' }}" data-panel="antecedentes">
            <h4 class="font-semibold mb-2">Antecedentes</h4>
            <p class="text-sm whitespace-pre-line mb-4">{{ $paciente->antecedentes ?: 'Sin antecedentes registrados.' }}</p>
            <h4 class="font-semibold mb-2">Alergias e intolerancias</h4>
            <p class="text-sm whitespace-pre-line">{{ $paciente->alergias ?: 'Sin alergias registradas.' }}</p>
        </section>

        {{-- Hábitos --}}
        <section class="exp-tab-panel {{ $activeTab !== 'habitos' ? 'hidden' : '' }}" data-panel="habitos">
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">Hábitos inferidos de evaluaciones y recomendaciones del nutriólogo.</p>
            @forelse($paciente->evaluaciones->filter(fn($e) => filled($e->recomendaciones)) as $ev)
                <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-3">
                    <p class="text-xs text-slate-500">{{ optional($ev->created_at)->format('d/m/Y') }}</p>
                    <p class="text-sm mt-1">{{ $ev->recomendaciones }}</p>
                </div>
            @empty
                <x-empty-state icon="fa-heart" title="Sin hábitos documentados" message="Las recomendaciones en evaluaciones alimentan esta sección." />
            @endforelse
        </section>

        {{-- Consultas --}}
        <section class="exp-tab-panel {{ $activeTab !== 'consultas' ? 'hidden' : '' }}" data-panel="consultas">
            @include('nutriologo.pacientes._evaluaciones-table', ['evaluaciones' => $paciente->evaluaciones])
        </section>

        {{-- Planes --}}
        <section class="exp-tab-panel {{ $activeTab !== 'planes' ? 'hidden' : '' }}" data-panel="planes">
            <div class="flex justify-end mb-4">
                <a href="{{ route('nutriologo.menus.create', ['paciente_id' => $paciente->id]) }}" class="text-sm text-emerald-600 hover:underline"><i class="fas fa-plus mr-1"></i>Nuevo plan</a>
            </div>
            @forelse($paciente->menus as $menu)
                <div class="border rounded-lg p-4 mb-3 dark:border-slate-700">
                    <div class="flex justify-between items-start gap-2">
                        <div><p class="font-semibold">{{ $menu->nombre }}</p><x-menu-estado-badge :estado="$menu->estado ?? 'activo'" class="mt-1" /></div>
                        <div class="flex gap-2 text-sm">
                            <a href="{{ route('nutriologo.menus.edit', $menu) }}" class="text-emerald-600 hover:underline">Editar</a>
                            <form action="{{ route('nutriologo.menus.duplicate', $menu) }}" method="POST">@csrf<button type="submit" class="text-sky-600 hover:underline">Duplicar</button></form>
                        </div>
                    </div>
                    <p class="text-sm mt-2 whitespace-pre-line text-slate-600 dark:text-slate-300">{{ $menu->descripcion }}</p>
                </div>
            @empty
                <x-empty-state icon="fa-utensils" title="Sin planes" message="Crea un plan alimenticio para este paciente." />
            @endforelse
        </section>

        {{-- Recomendaciones --}}
        <section class="exp-tab-panel {{ $activeTab !== 'recomendaciones' ? 'hidden' : '' }}" data-panel="recomendaciones">
            <form action="{{ route('nutriologo.recomendaciones.store') }}" method="POST" class="mb-6 p-4 bg-slate-50 dark:bg-slate-800/60 rounded-lg space-y-3">
                @csrf
                <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">
                <label class="block text-sm font-medium">Nueva recomendación (visible en portal del padre)</label>
                <textarea name="recomendaciones" rows="3" required minlength="10" class="w-full rounded-lg border dark:border-slate-600 dark:bg-slate-800 text-sm" placeholder="Indicaciones para la familia…"></textarea>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Registrar</button>
            </form>
            @forelse($paciente->evaluaciones->filter(fn($e) => filled($e->recomendaciones)) as $ev)
                <div class="border-l-4 border-emerald-500 pl-4 py-2 mb-3">
                    <p class="text-xs text-slate-500">{{ optional($ev->created_at)->format('d/m/Y H:i') }}</p>
                    <p class="text-sm">{{ $ev->recomendaciones }}</p>
                </div>
            @empty
                <x-empty-state icon="fa-comment-medical" title="Sin recomendaciones" message="Registra la primera recomendación arriba." />
            @endforelse
        </section>

        {{-- Documentos --}}
        <section class="exp-tab-panel {{ $activeTab !== 'documentos' ? 'hidden' : '' }}" data-panel="documentos">
            @forelse($paciente->reportes as $reporte)
                <div class="flex items-center justify-between p-4 border rounded-lg mb-2 dark:border-slate-700">
                    <div><p class="font-medium">{{ $reporte->titulo ?: 'Reporte #'.$reporte->id }}</p><p class="text-xs text-slate-500">{{ optional($reporte->created_at)->format('d/m/Y') }}</p></div>
                    <div class="flex gap-3 text-sm">
                        <a href="{{ route('nutriologo.reportes.show', $reporte) }}" class="text-emerald-600 hover:underline">Ver</a>
                        <a href="{{ route('nutriologo.reportes.pdf', $reporte) }}" class="text-red-600 hover:underline">PDF</a>
                    </div>
                </div>
            @empty
                <x-empty-state icon="fa-file-medical" title="Sin documentos" message="Genera reportes desde el módulo de reportes." />
            @endforelse
        </section>

        {{-- Seguimiento --}}
        <section class="exp-tab-panel {{ $activeTab !== 'seguimiento' ? 'hidden' : '' }}" data-panel="seguimiento">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950/30"><p class="text-xs text-emerald-700 dark:text-emerald-300">Evaluaciones</p><p class="text-2xl font-bold">{{ $paciente->evaluaciones->count() }}</p></div>
                <div class="p-4 rounded-lg bg-sky-50 dark:bg-sky-950/30"><p class="text-xs text-sky-700 dark:text-sky-300">Planes activos</p><p class="text-2xl font-bold">{{ $paciente->menus->where('estado', 'activo')->count() }}</p></div>
                <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-950/30"><p class="text-xs text-amber-700 dark:text-amber-300">Cumplimiento estimado</p><p class="text-2xl font-bold">{{ $cumplimiento !== null ? $cumplimiento.'%' : '—' }}</p></div>
            </div>
            <h4 class="font-semibold mb-2">Notas de seguimiento</h4>
            <p class="text-sm whitespace-pre-line">{{ $paciente->notas_seguimiento ?: 'Sin notas de seguimiento.' }}</p>
            @if(!empty(json_decode($chartJson, true)['imc']))
            <div class="mt-6"><h4 class="font-semibold mb-2">Objetivo vs evolución IMC</h4><div style="height:240px"><canvas id="chartObjetivo"></canvas></div></div>
            @endif
        </section>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/imc-cdc-lms.js') }}"></script>
<script>
(function(){
    var btns = document.querySelectorAll('.exp-tab-btn');
    var panels = document.querySelectorAll('.exp-tab-panel');
    btns.forEach(function(btn){
        btn.addEventListener('click', function(){
            var tab = btn.getAttribute('data-tab');
            btns.forEach(function(b){ b.classList.remove('border-emerald-500','text-emerald-700','dark:text-emerald-300','bg-white','dark:bg-slate-900'); b.setAttribute('aria-selected','false'); });
            btn.classList.add('border-emerald-500','text-emerald-700','dark:text-emerald-300','bg-white','dark:bg-slate-900');
            btn.setAttribute('aria-selected','true');
            panels.forEach(function(p){ p.classList.toggle('hidden', p.getAttribute('data-panel') !== tab); });
            if (tab === 'antropometria' || tab === 'seguimiento') initCharts();
        });
    });

    var raw = {!! $chartJson !!};
    var charts = {};
    function lineChart(id, label, data, color) {
        var el = document.getElementById(id);
        if (!el || !raw.labels || !raw.labels.length || typeof Chart === 'undefined') return;
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(el.getContext('2d'), {
            type: 'line',
            data: { labels: raw.labels, datasets: [{ label: label, data: data, borderColor: color, tension: 0.3, fill: false }] },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    function initCharts() {
        if (!raw.imc || !raw.imc.length) return;
        lineChart('chartImc', 'IMC', raw.imc, '#10b981');
        lineChart('chartPeso', 'Peso', raw.peso, '#0ea5e9');
        lineChart('chartTalla', 'Talla', raw.talla, '#8b5cf6');
        var edadMeses = {{ ($paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->diffInMonths(now()) : 60) }};
        var sexo = 1;
        var pct = raw.imc.map(function(imc){ return imc != null ? Math.min(99, Math.max(1, Math.round((imc / 25) * 50))) : null; });
        lineChart('chartPercentil', 'Percentil estimado', pct, '#f59e0b');
        lineChart('chartObjetivo', 'IMC', raw.imc, '#10b981');
    }
    var initialTab = new URLSearchParams(window.location.search).get('tab');
    if (initialTab) {
        var btn = document.querySelector('.exp-tab-btn[data-tab="' + initialTab + '"]');
        if (btn) btn.click();
    } else if (document.querySelector('[data-panel="antropometria"]:not(.hidden)')) initCharts();
})();
</script>
@endpush
@endsection
