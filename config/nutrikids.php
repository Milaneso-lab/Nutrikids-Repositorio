<?php

return [
    'roles' => [
        'admin' => 'Administrador',
        'nutriologo' => 'Nutriólogo',
        'padre' => 'Padre / tutor',
        'nino' => 'Niño (app móvil)',
    ],

    'redirects' => [
        'admin' => 'admin.dashboard',
        'nutriologo' => 'nutriologo.dashboard',
        'padre' => 'flask.portal',
    ],

    'permissions' => [
        'pacientes.leer' => 'Consultar expedientes',
        'pacientes.escribir' => 'Editar expedientes',
        'evaluaciones.leer' => 'Consultar evaluaciones',
        'evaluaciones.escribir' => 'Registrar evaluaciones',
        'menus.leer' => 'Consultar menús',
        'menus.escribir' => 'Gestionar menús',
        'reportes.leer' => 'Consultar reportes',
        'reportes.escribir' => 'Generar reportes',
        'citas.leer' => 'Consultar citas',
        'citas.asignar' => 'Gestionar citas clínicas',
        'citas.agendar' => 'Agendar citas',
        'contenido.moderar' => 'Moderar contenido',
        'usuarios.administrar' => 'Administrar usuarios',
        'roles.administrar' => 'Gestionar roles y permisos',
        'instituciones.administrar' => 'Gestionar instituciones',
        'auditoria.leer' => 'Consultar auditoría',
        'configuracion.administrar' => 'Configuración del sistema',
    ],

    'role_permissions' => [
        'admin' => [
            'pacientes.leer', 'pacientes.escribir', 'evaluaciones.leer', 'evaluaciones.escribir',
            'menus.leer', 'menus.escribir', 'reportes.leer', 'reportes.escribir',
            'citas.leer', 'citas.asignar', 'citas.agendar', 'contenido.moderar',
            'usuarios.administrar', 'roles.administrar', 'instituciones.administrar',
            'auditoria.leer', 'configuracion.administrar',
        ],
        'nutriologo' => [
            'pacientes.leer', 'pacientes.escribir', 'evaluaciones.leer', 'evaluaciones.escribir',
            'menus.leer', 'menus.escribir', 'reportes.leer', 'reportes.escribir',
            'citas.leer', 'citas.asignar',
        ],
        'padre' => [
            'pacientes.leer', 'pacientes.escribir', 'evaluaciones.leer', 'menus.leer', 'reportes.leer',
            'citas.leer', 'citas.agendar', 'gamificacion.participar',
        ],
        'nino' => [],
    ],
];
