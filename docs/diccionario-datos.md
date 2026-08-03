# Diccionario de datos - NutriKids

> Documento **generado automaticamente** desde el esquema vivo de PostgreSQL.
> No editar a mano: regenerar con `./scripts/db/generar-diccionario.ps1`.

- Base de datos: `nutrikids`
- Motor: PostgreSQL 15
- Tablas documentadas: 43
- Generado: 2026-07-30 18:59

---

## alembic_version
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| version_num | character varying(32) | no | - | PK |

## alergias
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('alergias_id_seq'::regclass) | PK |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |
| nino_id | bigint | si | - | FK -> ninos |
| tipo | alergia_tipo_enum | si | - |  |
| descripcion | character varying(255) | si | - |  |
| severidad | alergia_severidad_enum | si | - |  |
| registrada_por_id | bigint | si | - | FK -> usuarios |

## alertas
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('alertas_id_seq'::regclass) | PK |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |
| nino_id | bigint | si | - | FK -> ninos |
| tipo | character varying(50) | si | - |  |
| severidad | alerta_severidad_enum | si | - |  |
| mensaje | text | si | - |  |
| atendida | boolean | no | false |  |
| atendida_por_id | bigint | si | - | FK -> usuarios |
| atendida_en | timestamp with time zone | si | - |  |

## cache
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| key | character varying(255) | no | - | PK |
| value | text | no | - |  |
| expiration | integer | no | - |  |

## cache_locks
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| key | character varying(255) | no | - | PK |
| owner | character varying(255) | no | - |  |
| expiration | integer | no | - |  |

## citas
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('citas_id_seq'::regclass) | PK |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |
| id_padre | bigint | no | - | FK -> usuarios |
| id_nutriologo | bigint | si | - | FK -> usuarios |
| fecha_preferida | date | no | - |  |
| franja | character varying(20) | no | 'manana'::character varying |  |
| telefono | character varying(30) | si | - |  |
| mensaje | text | si | - |  |
| estado | character varying(20) | no | 'pendiente'::character varying |  |
| nino_id | bigint | si | - | FK -> ninos |

## comentarios
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id_comentario | bigint | no | nextval('comentarios_id_comentario_seq'::regclass) | PK |
| nombre | character varying(50) | no | - |  |
| apellido | character varying(50) | no | - |  |
| comentario | text | no | - |  |
| fecha_comentario | timestamp(0) without time zone | no | CURRENT_TIMESTAMP |  |
| id_usuario | bigint | no | - | FK -> usuarios |

## contactos
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id_contacto | bigint | no | nextval('contactos_id_contacto_seq'::regclass) | PK |
| nombre | character varying(50) | no | - |  |
| apellido | character varying(50) | no | - |  |
| email | character varying(100) | no | - |  |
| mensaje | text | no | - |  |
| fecha_creacion | timestamp(0) without time zone | no | CURRENT_TIMESTAMP |  |

## discusiones
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id_discusion | bigint | no | nextval('discusiones_id_discusion_seq'::regclass) | PK |
| tema | character varying(255) | no | - |  |
| descripcion | text | no | - |  |
| fecha_creacion | timestamp(0) without time zone | no | CURRENT_TIMESTAMP |  |
| id_usuario | bigint | no | - | FK -> usuarios |

## evaluaciones
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('evaluaciones_id_seq'::regclass) | PK |
| paciente_id | bigint | si | - | FK -> pacientes |
| peso | character varying(20) | si | - |  |
| talla | character varying(20) | si | - |  |
| recomendaciones | text | si | - |  |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |
| nino_id | bigint | si | - | FK -> ninos |
| nutriologo_id | bigint | si | - | FK -> usuarios |
| peso_kg | numeric(5,2) | si | - |  |
| talla_cm | numeric(5,2) | si | - |  |
| imc | numeric(4,2) | si | - |  |
| percentil_oms | numeric(5,2) | si | - |  |
| fecha_evaluacion | date | no | CURRENT_DATE |  |

## failed_jobs
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('failed_jobs_id_seq'::regclass) | PK |
| uuid | character varying(255) | no | - | UNICO |
| connection | text | no | - |  |
| queue | text | no | - |  |
| payload | text | no | - |  |
| exception | text | no | - |  |
| failed_at | timestamp(0) without time zone | no | CURRENT_TIMESTAMP |  |

## habito_registros
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('habito_registros_id_seq'::regclass) | PK |
| nino_habito_id | bigint | no | - | FK -> nino_habitos, UNICO |
| fecha | date | no | - | UNICO |
| completado | boolean | no | false |  |
| registrado_en | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## habitos_catalogo
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('habitos_catalogo_id_seq'::regclass) | PK |
| nombre | character varying(150) | no | - |  |
| descripcion | text | si | - |  |
| categoria | habito_categoria_enum | no | - |  |
| icono | character varying(100) | si | - |  |
| puntos_base | integer | no | 0 |  |
| activo | boolean | no | true |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| updated_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## infantes
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('infantes_id_seq'::regclass) | PK |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |

## job_batches
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | character varying(255) | no | - | PK |
| name | character varying(255) | no | - |  |
| total_jobs | integer | no | - |  |
| pending_jobs | integer | no | - |  |
| failed_jobs | integer | no | - |  |
| failed_job_ids | text | no | - |  |
| options | text | si | - |  |
| cancelled_at | integer | si | - |  |
| created_at | integer | no | - |  |
| finished_at | integer | si | - |  |

## jobs
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('jobs_id_seq'::regclass) | PK |
| queue | character varying(255) | no | - |  |
| payload | text | no | - |  |
| attempts | smallint | no | - |  |
| reserved_at | integer | si | - |  |
| available_at | integer | no | - |  |
| created_at | integer | no | - |  |

## login_attempts
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('login_attempts_id_seq'::regclass) | PK |
| email | character varying(150) | no | - |  |
| ip_address | character varying(45) | si | - |  |
| exito | boolean | no | false |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## logros_catalogo
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('logros_catalogo_id_seq'::regclass) | PK |
| nombre | character varying(150) | no | - |  |
| descripcion | text | si | - |  |
| icono | character varying(100) | si | - |  |
| criterio | jsonb | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| updated_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## menu_items
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('menu_items_id_seq'::regclass) | PK |
| menu_id | bigint | no | - | FK -> menus |
| dia_semana | dia_semana_enum | no | - |  |
| tipo_comida | tipo_comida_enum | no | - |  |
| descripcion | text | no | - |  |
| calorias_aprox | integer | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## menus
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('menus_id_seq'::regclass) | PK |
| nombre | character varying(150) | si | - |  |
| paciente_id | bigint | si | - | FK -> pacientes |
| descripcion | text | si | - |  |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |
| nino_id | bigint | si | - | FK -> ninos |
| nutriologo_id | bigint | si | - | FK -> usuarios |
| objetivo | character varying(150) | si | - |  |
| fecha_inicio | date | si | - |  |
| fecha_fin | date | si | - |  |
| estado | character varying(20) | no | 'activo'::character varying |  |
| duplicado_de_id | bigint | si | - |  |

## menus_semanales
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('menus_semanales_id_seq'::regclass) | PK |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |
| nombre | character varying(150) | si | - |  |
| descripcion | text | si | - |  |
| creado_por_id | bigint | si | - | FK -> usuarios |
| publico | boolean | no | false |  |

## migrations
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | integer | no | nextval('migrations_id_seq'::regclass) | PK |
| migration | character varying(255) | no | - |  |
| batch | integer | no | - |  |

## nino_credenciales
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| nino_id | bigint | no | - | FK -> ninos, PK |
| pin_hash | character varying(255) | no | - |  |
| dispositivo_id | character varying(255) | si | - |  |
| vinculado_en | timestamp with time zone | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| updated_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## nino_habitos
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('nino_habitos_id_seq'::regclass) | PK |
| nino_id | bigint | no | - | FK -> ninos |
| habito_id | bigint | no | - | FK -> habitos_catalogo |
| frecuencia | habito_frecuencia_enum | no | - |  |
| asignado_por_id | bigint | si | - | FK -> usuarios |
| activo | boolean | no | true |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## nino_logros
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('nino_logros_id_seq'::regclass) | PK |
| nino_id | bigint | no | - | FK -> ninos, UNICO |
| logro_id | bigint | no | - | FK -> logros_catalogo, UNICO |
| obtenido_en | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## nino_puntos
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| nino_id | bigint | no | - | FK -> ninos, PK |
| puntos_totales | integer | no | 0 |  |
| nivel_actual | integer | no | 1 |  |
| actualizado_en | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## nino_recompensas
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('nino_recompensas_id_seq'::regclass) | PK |
| nino_id | bigint | no | - | FK -> ninos |
| recompensa_id | bigint | no | - | FK -> recompensas_catalogo |
| canjeado_en | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| estado | recompensa_estado_enum | no | 'pendiente'::recompensa_estado_enum |  |

## nino_retos
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('nino_retos_id_seq'::regclass) | PK |
| nino_id | bigint | no | - | FK -> ninos |
| reto_id | bigint | no | - | FK -> retos_catalogo |
| progreso | jsonb | si | - |  |
| completado | boolean | no | false |  |
| completado_en | timestamp with time zone | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## ninos
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('ninos_id_seq'::regclass) | PK |
| padre_id | bigint | no | - | FK -> usuarios |
| nutriologo_asignado_id | bigint | si | - | FK -> usuarios |
| nombre | character varying(100) | no | - |  |
| apellidos | character varying(100) | no | - |  |
| fecha_nacimiento | date | no | - |  |
| sexo | nino_sexo_enum | no | - |  |
| peso_actual_kg | numeric(5,2) | si | - |  |
| talla_actual_cm | numeric(5,2) | si | - |  |
| avatar_config | jsonb | si | - |  |
| codigo_vinculacion | character varying(12) | si | - | UNICO |
| requiere_vinculacion_padre | boolean | no | false |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| updated_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| deleted_at | timestamp with time zone | si | - |  |

## notas_nutriologo
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('notas_nutriologo_id_seq'::regclass) | PK |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |
| nino_id | bigint | si | - | FK -> ninos |
| nutriologo_id | bigint | si | - | FK -> usuarios |
| nota | text | si | - |  |
| privada | boolean | no | true |  |

## pacientes
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('pacientes_id_seq'::regclass) | PK |
| nombre | character varying(100) | si | - |  |
| apellidos | character varying(100) | si | - |  |
| fecha_nacimiento | timestamp(0) without time zone | si | - |  |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |
| estado_paciente | character varying(30) | no | 'activo'::character varying |  |
| historia_clinica | text | si | - |  |
| antecedentes | text | si | - |  |
| alergias | text | si | - |  |
| objetivo_nutricional | text | si | - |  |
| notas_seguimiento | text | si | - |  |

## password_history
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('password_history_id_seq'::regclass) | PK |
| usuario_id | bigint | no | - | FK -> usuarios |
| contrasena_hash | character varying(255) | no | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## password_reset_tokens
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| email | character varying(255) | no | - | PK |
| token | character varying(255) | no | - |  |
| created_at | timestamp(0) without time zone | si | - |  |

## permisos
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('permisos_id_seq'::regclass) | PK |
| clave | character varying(100) | no | - | UNICO |
| descripcion | character varying(255) | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| updated_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## recompensas_catalogo
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('recompensas_catalogo_id_seq'::regclass) | PK |
| nombre | character varying(150) | no | - |  |
| descripcion | text | si | - |  |
| costo_puntos | integer | no | - |  |
| stock | integer | si | - |  |
| activo | boolean | no | true |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| updated_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## refresh_tokens
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('refresh_tokens_id_seq'::regclass) | PK |
| usuario_id | bigint | no | - | FK -> usuarios |
| token_hash | character varying(255) | no | - |  |
| dispositivo | character varying(255) | si | - |  |
| expira_en | timestamp with time zone | no | - |  |
| revocado_en | timestamp with time zone | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## reportes
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('reportes_id_seq'::regclass) | PK |
| paciente_id | bigint | si | - | FK -> pacientes |
| titulo | character varying(150) | si | - |  |
| contenido | text | si | - |  |
| created_at | timestamp(0) without time zone | si | - |  |
| updated_at | timestamp(0) without time zone | si | - |  |
| nino_id | bigint | si | - | FK -> ninos |
| nutriologo_id | bigint | si | - | FK -> usuarios |
| pdf_generado_en | timestamp with time zone | si | - |  |

## retos_catalogo
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('retos_catalogo_id_seq'::regclass) | PK |
| nombre | character varying(150) | no | - |  |
| descripcion | text | si | - |  |
| tipo | reto_tipo_enum | no | - |  |
| condicion | jsonb | no | - |  |
| puntos_recompensa | integer | no | 0 |  |
| activo | boolean | no | true |  |
| fecha_inicio | date | si | - |  |
| fecha_fin | date | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| updated_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## rol_permiso
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| rol_id | bigint | no | - | FK -> roles, PK |
| permiso_id | bigint | no | - | FK -> permisos, PK |

## roles
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('roles_id_seq'::regclass) | PK |
| nombre | character varying(50) | no | - | UNICO |
| descripcion | character varying(255) | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| updated_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## security_audit_logs
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | bigint | no | nextval('security_audit_logs_id_seq'::regclass) | PK |
| usuario_id | bigint | si | - | FK -> usuarios |
| accion | character varying(100) | no | - |  |
| recurso | character varying(150) | si | - |  |
| ip_address | character varying(45) | si | - |  |
| detalles | jsonb | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |

## sessions
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id | character varying(255) | no | - | PK |
| user_id | bigint | si | - |  |
| ip_address | character varying(45) | si | - |  |
| user_agent | text | si | - |  |
| payload | text | no | - |  |
| last_activity | integer | no | - |  |

## usuarios
| Columna | Tipo | Nulo | Predeterminado | Clave |
|---------|------|------|----------------|-------|
| id_usuario | bigint | no | nextval('usuarios_id_usuario_seq'::regclass) | PK |
| nombre | character varying(100) | no | - |  |
| apellido_paterno | character varying(100) | no | - |  |
| apellido_materno | character varying(100) | si | - |  |
| email | character varying(150) | no | - | UNICO |
| contrasena | character varying(255) | no | - |  |
| rol | character varying(20) | no | 'padre'::character varying |  |
| telefono | character varying(30) | si | - |  |
| rol_id | bigint | no | - | FK -> roles |
| estado | usuario_estado_enum | no | 'activo'::usuario_estado_enum |  |
| email_verificado_en | timestamp with time zone | si | - |  |
| ultimo_login_en | timestamp with time zone | si | - |  |
| created_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| updated_at | timestamp with time zone | no | CURRENT_TIMESTAMP |  |
| especialidad | character varying(120) | si | - |  |
| disponibilidad | character varying(255) | si | - |  |
| foto_path | character varying(255) | si | - |  |
