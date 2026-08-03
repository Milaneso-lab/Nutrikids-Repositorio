<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Comentario;
use App\Models\Contacto;
use App\Models\Discusion;
use App\Models\Evaluacion;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Agrega actividad reciente del sistema sin tabla de auditoría dedicada (fase evolutiva).
 */
class AuditLogService
{
    public function recentEntries(int $limit = 25): Collection
    {
        $entries = collect();

        User::query()->latest('id_usuario')->take(8)->get()->each(function (User $u) use ($entries) {
            $entries->push([
                'tipo' => 'usuario',
                'accion' => 'Registro de usuario',
                'detalle' => trim("{$u->nombre} {$u->apellido_paterno}") . " ({$u->rol})",
                'fecha' => $u->created_at,
                'icon' => 'fa-user-plus',
            ]);
        });

        Cita::query()->latest('id')->take(8)->get()->each(function (Cita $c) use ($entries) {
            $entries->push([
                'tipo' => 'cita',
                'accion' => 'Cita ' . str_replace('_', ' ', $c->estado ?? 'registrada'),
                'detalle' => 'Solicitud #' . $c->id . ' · ' . optional($c->fecha_preferida)->format('d/m/Y'),
                'fecha' => $c->updated_at ?? $c->created_at,
                'icon' => 'fa-calendar-check',
            ]);
        });

        Contacto::query()->latest('id')->take(5)->get()->each(function (Contacto $c) use ($entries) {
            $entries->push([
                'tipo' => 'contacto',
                'accion' => 'Mensaje de contacto',
                'detalle' => trim("{$c->nombre} {$c->apellido}") . ' · ' . \Illuminate\Support\Str::limit($c->mensaje ?? '', 60),
                'fecha' => $c->created_at,
                'icon' => 'fa-envelope',
            ]);
        });

        Evaluacion::query()->latest('id')->take(5)->get()->each(function (Evaluacion $e) use ($entries) {
            $entries->push([
                'tipo' => 'evaluacion',
                'accion' => 'Evaluación clínica',
                'detalle' => 'Paciente #' . $e->paciente_id . ' · Peso ' . ($e->peso ?? '—') . ' kg',
                'fecha' => $e->created_at,
                'icon' => 'fa-clipboard-check',
            ]);
        });

        Paciente::query()->latest('id')->take(5)->get()->each(function (Paciente $p) use ($entries) {
            $entries->push([
                'tipo' => 'paciente',
                'accion' => 'Alta de paciente',
                'detalle' => trim("{$p->nombre} {$p->apellidos}"),
                'fecha' => $p->created_at,
                'icon' => 'fa-child',
            ]);
        });

        Comentario::query()->latest('id')->take(3)->get()->each(function (Comentario $c) use ($entries) {
            $entries->push([
                'tipo' => 'comunidad',
                'accion' => 'Comentario publicado',
                'detalle' => trim("{$c->nombre} {$c->apellido}"),
                'fecha' => $c->created_at,
                'icon' => 'fa-comment',
            ]);
        });

        Discusion::query()->latest('id')->take(3)->get()->each(function (Discusion $d) use ($entries) {
            $entries->push([
                'tipo' => 'comunidad',
                'accion' => 'Discusión en foro',
                'detalle' => \Illuminate\Support\Str::limit($d->tema ?? 'Sin tema', 60),
                'fecha' => $d->created_at,
                'icon' => 'fa-comments',
            ]);
        });

        return $entries
            ->filter(fn ($e) => $e['fecha'] !== null)
            ->sortByDesc(fn ($e) => $e['fecha']->timestamp)
            ->take($limit)
            ->values();
    }
}
