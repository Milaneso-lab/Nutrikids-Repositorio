<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $reporte->titulo ?: 'Reporte de progreso nutricional' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
        }
        .header {
            margin-bottom: 24px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #166534;
            margin-bottom: 4px;
        }
        .subtitle {
            color: #4b5563;
            margin-bottom: 0;
        }
        .cards {
            width: 100%;
            margin-bottom: 24px;
        }
        .card {
            width: 31%;
            display: inline-block;
            vertical-align: top;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
            margin-right: 2%;
            box-sizing: border-box;
        }
        .card:last-child {
            margin-right: 0;
        }
        .label {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .value {
            font-size: 14px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
            font-size: 11px;
            text-transform: uppercase;
        }
        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $reporte->titulo ?: 'Reporte de progreso nutricional' }}</div>
        <p class="subtitle">Generado el {{ optional($reporte->created_at)->format('d/m/Y H:i') }}</p>
    </div>

    <div class="cards">
        <div class="card">
            <div class="label">Paciente</div>
            <div class="value">{{ $reportData['paciente_nombre'] }}</div>
        </div>
        <div class="card">
            <div class="label">Período</div>
            <div class="value">{{ $reportData['periodo'] }}</div>
        </div>
        <div class="card">
            <div class="label">IMC promedio</div>
            <div class="value">{{ $reportData['imc_promedio'] !== null ? number_format($reportData['imc_promedio'], 2) : 'Sin datos' }}</div>
        </div>
    </div>

    <p><strong>Total de evaluaciones:</strong> {{ $reportData['total_evaluaciones'] }}</p>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Peso (kg)</th>
                <th>Talla (cm)</th>
                <th>IMC</th>
                <th>Recomendaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['evaluaciones'] as $evaluacion)
                <tr>
                    <td>{{ $evaluacion['fecha'] ?? 'Sin fecha' }}</td>
                    <td>{{ $evaluacion['peso'] ?? 'Sin dato' }}</td>
                    <td>{{ $evaluacion['talla'] ?? 'Sin dato' }}</td>
                    <td>{{ $evaluacion['imc'] !== null ? number_format($evaluacion['imc'], 2) : 'Sin dato' }}</td>
                    <td>{{ $evaluacion['recomendaciones'] ?: 'Sin recomendaciones' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">No hay evaluaciones registradas para este paciente.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
 