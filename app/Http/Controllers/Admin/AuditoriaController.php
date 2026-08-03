<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;

class AuditoriaController extends Controller
{
    public function index(AuditLogService $auditLog)
    {
        $entradas = $auditLog->recentEntries(40);

        return view('admin.auditoria.index', compact('entradas'));
    }
}
