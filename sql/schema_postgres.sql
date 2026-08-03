-- PostgreSQL schema objetivo NutriKids
-- Referencia completa según ProyectoIA/DocumentacionProyecto/03_BaseDatos.md
-- Aplicar vía: php artisan migrate && cd fastapi && python -m alembic upgrade head

-- =============================================================================
-- ENUMS
-- =============================================================================

DO $$ BEGIN CREATE TYPE usuario_estado_enum AS ENUM ('activo','suspendido','pendiente_verificacion'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE nino_sexo_enum AS ENUM ('masculino','femenino','otro'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE alergia_tipo_enum AS ENUM ('alimentaria','ambiental','medicamento','otra'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE alergia_severidad_enum AS ENUM ('leve','moderada','grave'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE alerta_severidad_enum AS ENUM ('info','advertencia','critica'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE dia_semana_enum AS ENUM ('lun','mar','mie','jue','vie','sab','dom'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE tipo_comida_enum AS ENUM ('desayuno','colacion','comida','cena'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE habito_categoria_enum AS ENUM ('alimentacion','actividad','sueno','higiene'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE habito_frecuencia_enum AS ENUM ('diaria','semanal'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE reto_tipo_enum AS ENUM ('individual','semanal','especial'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;
DO $$ BEGIN CREATE TYPE recompensa_estado_enum AS ENUM ('pendiente','entregada'); EXCEPTION WHEN duplicate_object THEN NULL; END $$;

-- =============================================================================
-- DOMINIO 1 — IDENTIDAD
-- =============================================================================

CREATE TABLE IF NOT EXISTS roles (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permisos (
    id BIGSERIAL PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rol_permiso (
    rol_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permiso_id BIGINT NOT NULL REFERENCES permisos(id) ON DELETE CASCADE,
    PRIMARY KEY (rol_id, permiso_id)
);
CREATE INDEX IF NOT EXISTS rol_permiso_permiso_id_index ON rol_permiso (permiso_id);

-- usuarios: PK legacy id_usuario (compatibilidad Laravel/FastAPI)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100),
    email VARCHAR(150) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL DEFAULT 'padre',
    telefono VARCHAR(30),
    rol_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE RESTRICT,
    estado usuario_estado_enum NOT NULL DEFAULT 'activo',
    email_verificado_en TIMESTAMPTZ,
    ultimo_login_en TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS usuarios_rol_id_index ON usuarios (rol_id);
CREATE INDEX IF NOT EXISTS usuarios_estado_index ON usuarios (estado);

CREATE TABLE IF NOT EXISTS refresh_tokens (
    id BIGSERIAL PRIMARY KEY,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    token_hash VARCHAR(255) NOT NULL,
    dispositivo VARCHAR(255),
    expira_en TIMESTAMPTZ NOT NULL,
    revocado_en TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS refresh_tokens_usuario_id_index ON refresh_tokens (usuario_id);
CREATE INDEX IF NOT EXISTS refresh_tokens_expira_en_index ON refresh_tokens (expira_en);

CREATE TABLE IF NOT EXISTS ninos (
    id BIGSERIAL PRIMARY KEY,
    padre_id BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    nutriologo_asignado_id BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo nino_sexo_enum NOT NULL,
    peso_actual_kg NUMERIC(5,2),
    talla_actual_cm NUMERIC(5,2),
    avatar_config JSONB,
    codigo_vinculacion VARCHAR(12) UNIQUE,
    requiere_vinculacion_padre BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ
);
CREATE INDEX IF NOT EXISTS ninos_padre_id_index ON ninos (padre_id);
CREATE INDEX IF NOT EXISTS ninos_nutriologo_asignado_id_index ON ninos (nutriologo_asignado_id);
CREATE UNIQUE INDEX IF NOT EXISTS ninos_codigo_vinculacion_partial_index ON ninos (codigo_vinculacion) WHERE codigo_vinculacion IS NOT NULL;

CREATE TABLE IF NOT EXISTS nino_credenciales (
    nino_id BIGINT PRIMARY KEY REFERENCES ninos(id) ON DELETE CASCADE,
    pin_hash VARCHAR(255) NOT NULL,
    dispositivo_id VARCHAR(255),
    vinculado_en TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =============================================================================
-- DOMINIO 2 — CLÍNICO
-- =============================================================================

CREATE TABLE IF NOT EXISTS evaluaciones (
    id BIGSERIAL PRIMARY KEY,
    paciente_id BIGINT REFERENCES pacientes(id) ON DELETE SET NULL,
    nino_id BIGINT REFERENCES ninos(id) ON DELETE CASCADE,
    nutriologo_id BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    peso VARCHAR(20),
    talla VARCHAR(20),
    peso_kg NUMERIC(5,2),
    talla_cm NUMERIC(5,2),
    imc NUMERIC(4,2),
    percentil_oms NUMERIC(5,2),
    recomendaciones TEXT,
    fecha_evaluacion DATE NOT NULL DEFAULT CURRENT_DATE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS evaluaciones_nino_id_index ON evaluaciones (nino_id);
CREATE INDEX IF NOT EXISTS evaluaciones_nino_fecha_index ON evaluaciones (nino_id, fecha_evaluacion DESC);

CREATE TABLE IF NOT EXISTS alergias (
    id BIGSERIAL PRIMARY KEY,
    nino_id BIGINT REFERENCES ninos(id) ON DELETE CASCADE,
    tipo alergia_tipo_enum,
    descripcion VARCHAR(255),
    severidad alergia_severidad_enum,
    registrada_por_id BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS alergias_nino_id_index ON alergias (nino_id);

CREATE TABLE IF NOT EXISTS alertas (
    id BIGSERIAL PRIMARY KEY,
    nino_id BIGINT REFERENCES ninos(id) ON DELETE CASCADE,
    tipo VARCHAR(50),
    severidad alerta_severidad_enum,
    mensaje TEXT,
    atendida BOOLEAN NOT NULL DEFAULT false,
    atendida_por_id BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    atendida_en TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS alertas_atendida_severidad_index ON alertas (atendida, severidad);

CREATE TABLE IF NOT EXISTS notas_nutriologo (
    id BIGSERIAL PRIMARY KEY,
    nino_id BIGINT REFERENCES ninos(id) ON DELETE CASCADE,
    nutriologo_id BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    nota TEXT,
    privada BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS notas_nutriologo_nino_id_index ON notas_nutriologo (nino_id);

CREATE TABLE IF NOT EXISTS menus (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(150),
    paciente_id BIGINT REFERENCES pacientes(id) ON DELETE SET NULL,
    nino_id BIGINT REFERENCES ninos(id) ON DELETE CASCADE,
    nutriologo_id BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    descripcion TEXT,
    objetivo VARCHAR(150),
    fecha_inicio DATE,
    fecha_fin DATE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS menus_nino_id_index ON menus (nino_id);

CREATE TABLE IF NOT EXISTS menu_items (
    id BIGSERIAL PRIMARY KEY,
    menu_id BIGINT NOT NULL REFERENCES menus(id) ON DELETE CASCADE,
    dia_semana dia_semana_enum NOT NULL,
    tipo_comida tipo_comida_enum NOT NULL,
    descripcion TEXT NOT NULL,
    calorias_aprox INTEGER,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS menu_items_menu_id_index ON menu_items (menu_id);

CREATE TABLE IF NOT EXISTS menus_semanales (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(150),
    descripcion TEXT,
    creado_por_id BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    publico BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reportes (
    id BIGSERIAL PRIMARY KEY,
    paciente_id BIGINT REFERENCES pacientes(id) ON DELETE SET NULL,
    nino_id BIGINT REFERENCES ninos(id) ON DELETE CASCADE,
    nutriologo_id BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    titulo VARCHAR(150),
    contenido TEXT,
    pdf_generado_en TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS reportes_nino_id_index ON reportes (nino_id);

CREATE TABLE IF NOT EXISTS citas (
    id BIGSERIAL PRIMARY KEY,
    id_padre BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    id_nutriologo BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    nino_id BIGINT REFERENCES ninos(id) ON DELETE SET NULL,
    fecha_preferida DATE NOT NULL,
    franja VARCHAR(20) NOT NULL DEFAULT 'manana',
    telefono VARCHAR(30),
    mensaje TEXT,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS citas_estado_index ON citas (estado);
CREATE INDEX IF NOT EXISTS citas_nino_id_index ON citas (nino_id);

-- =============================================================================
-- DOMINIO 3 — COMUNIDAD
-- =============================================================================

CREATE TABLE IF NOT EXISTS contactos (
    id_contacto BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_creacion TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS comentarios (
    id_comentario BIGSERIAL PRIMARY KEY,
    id_usuario BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    comentario TEXT NOT NULL,
    fecha_comentario TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS discusiones (
    id_discusion BIGSERIAL PRIMARY KEY,
    id_usuario BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    tema VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_creacion TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =============================================================================
-- DOMINIO 4 — GAMIFICACIÓN
-- =============================================================================

CREATE TABLE IF NOT EXISTS habitos_catalogo (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    categoria habito_categoria_enum NOT NULL,
    icono VARCHAR(100),
    puntos_base INTEGER NOT NULL DEFAULT 0,
    activo BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS habitos_catalogo_categoria_index ON habitos_catalogo (categoria);
CREATE INDEX IF NOT EXISTS habitos_catalogo_activo_index ON habitos_catalogo (activo);

CREATE TABLE IF NOT EXISTS nino_habitos (
    id BIGSERIAL PRIMARY KEY,
    nino_id BIGINT NOT NULL REFERENCES ninos(id) ON DELETE CASCADE,
    habito_id BIGINT NOT NULL REFERENCES habitos_catalogo(id) ON DELETE CASCADE,
    frecuencia habito_frecuencia_enum NOT NULL,
    asignado_por_id BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    activo BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS nino_habitos_nino_id_index ON nino_habitos (nino_id);
CREATE INDEX IF NOT EXISTS nino_habitos_habito_id_index ON nino_habitos (habito_id);

CREATE TABLE IF NOT EXISTS habito_registros (
    id BIGSERIAL PRIMARY KEY,
    nino_habito_id BIGINT NOT NULL REFERENCES nino_habitos(id) ON DELETE CASCADE,
    fecha DATE NOT NULL,
    completado BOOLEAN NOT NULL DEFAULT false,
    registrado_en TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (nino_habito_id, fecha)
);
CREATE INDEX IF NOT EXISTS habito_registros_fecha_index ON habito_registros (fecha);

CREATE TABLE IF NOT EXISTS retos_catalogo (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    tipo reto_tipo_enum NOT NULL,
    condicion JSONB NOT NULL,
    puntos_recompensa INTEGER NOT NULL DEFAULT 0,
    activo BOOLEAN NOT NULL DEFAULT true,
    fecha_inicio DATE,
    fecha_fin DATE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS nino_retos (
    id BIGSERIAL PRIMARY KEY,
    nino_id BIGINT NOT NULL REFERENCES ninos(id) ON DELETE CASCADE,
    reto_id BIGINT NOT NULL REFERENCES retos_catalogo(id) ON DELETE CASCADE,
    progreso JSONB,
    completado BOOLEAN NOT NULL DEFAULT false,
    completado_en TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS nino_retos_nino_id_index ON nino_retos (nino_id);

CREATE TABLE IF NOT EXISTS logros_catalogo (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(100),
    criterio JSONB,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS nino_logros (
    id BIGSERIAL PRIMARY KEY,
    nino_id BIGINT NOT NULL REFERENCES ninos(id) ON DELETE CASCADE,
    logro_id BIGINT NOT NULL REFERENCES logros_catalogo(id) ON DELETE CASCADE,
    obtenido_en TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (nino_id, logro_id)
);

CREATE TABLE IF NOT EXISTS nino_puntos (
    nino_id BIGINT PRIMARY KEY REFERENCES ninos(id) ON DELETE CASCADE,
    puntos_totales INTEGER NOT NULL DEFAULT 0,
    nivel_actual INTEGER NOT NULL DEFAULT 1,
    actualizado_en TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS recompensas_catalogo (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    costo_puntos INTEGER NOT NULL,
    stock INTEGER,
    activo BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS nino_recompensas (
    id BIGSERIAL PRIMARY KEY,
    nino_id BIGINT NOT NULL REFERENCES ninos(id) ON DELETE CASCADE,
    recompensa_id BIGINT NOT NULL REFERENCES recompensas_catalogo(id) ON DELETE CASCADE,
    canjeado_en TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado recompensa_estado_enum NOT NULL DEFAULT 'pendiente'
);
CREATE INDEX IF NOT EXISTS nino_recompensas_nino_id_index ON nino_recompensas (nino_id);

-- =============================================================================
-- LEGACY (conservadas para compatibilidad — no eliminar en Fase DB)
-- =============================================================================

CREATE TABLE IF NOT EXISTS pacientes (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    apellidos VARCHAR(100),
    fecha_nacimiento TIMESTAMP,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS infantes (
    id BIGSERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =============================================================================
-- LARAVEL FRAMEWORK (sesiones, caché, colas)
-- =============================================================================

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(100) PRIMARY KEY,
    token VARCHAR(255),
    created_at TIMESTAMPTZ
);

CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT,
    expiration INTEGER
);

CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255),
    payload TEXT,
    attempts INTEGER DEFAULT 0,
    reserved_at INTEGER,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
