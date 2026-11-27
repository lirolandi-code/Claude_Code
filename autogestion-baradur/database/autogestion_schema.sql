-- ============================================================================
-- Autogestión del empleado - esquema de ejemplo
-- MySQL 5.7.18 · InnoDB · utf8
--
-- Se usa utf8 (y no utf8mb4) porque la librería cliente de PHP 5.1.6 no conoce
-- el juego de caracteres utf8mb4.
--
-- Si su base de RR.HH. ya tiene estas entidades con otros nombres, no hace falta
-- crear estas tablas: alcanza con adaptar las consultas de
-- app/autogestion/AutogestionRepositorio.php (todas están en ese archivo).
-- ============================================================================

SET NAMES utf8;

CREATE TABLE IF NOT EXISTS ag_empleado (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  legajo              INT UNSIGNED NOT NULL,
  cuil                VARCHAR(13)  NOT NULL,           -- con guiones: 20-25817734-6
  apellido            VARCHAR(80)  NOT NULL,
  nombre              VARCHAR(80)  NOT NULL,
  tipo_documento      VARCHAR(10)  NOT NULL DEFAULT 'DNI',
  nro_documento       INT UNSIGNED NOT NULL,
  fecha_nacimiento    DATE         NULL,
  lugar_nacimiento    VARCHAR(120) NULL,
  sexo                VARCHAR(20)  NULL,
  estado_civil        VARCHAR(30)  NULL,
  nacionalidad        VARCHAR(60)  NULL,
  nivel_estudios      VARCHAR(60)  NULL,
  titulo              VARCHAR(150) NULL,
  domicilio           VARCHAR(150) NULL,
  email               VARCHAR(120) NULL,
  telefono            VARCHAR(40)  NULL,
  dependencia_codigo  VARCHAR(12)  NULL,
  dependencia_nombre  VARCHAR(150) NULL,
  sede                VARCHAR(120) NULL,
  modalidad           VARCHAR(60)  NULL,               -- Planta permanente, Ley Marco, etc.
  escalafon           VARCHAR(30)  NULL,
  agrupamiento        VARCHAR(40)  NULL,
  nivel               VARCHAR(5)   NULL,
  grado               TINYINT UNSIGNED NULL,
  tramo               VARCHAR(30)  NULL,
  fecha_ingreso       DATE         NULL,
  horario_desde       TIME         NULL,               -- horario oficial
  horario_hasta       TIME         NULL,
  categoria_horaria   VARCHAR(10)  NULL,
  creditos_actuales   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  creditos_requeridos SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  foto                VARCHAR(200) NULL,               -- ruta relativa en storage/app/autogestion
  clave               VARCHAR(100) NULL,               -- hash generado con Hash::make() del framework
  activo              TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_legajo (legajo),
  UNIQUE KEY uk_cuil (cuil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_documentacion (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id        INT UNSIGNED NOT NULL,
  descripcion        VARCHAR(150) NOT NULL,
  estado             ENUM('presentado','pendiente','vencido','en_revision') NOT NULL DEFAULT 'pendiente',
  fecha_presentacion DATE NULL,
  fecha_vencimiento  DATE NULL,
  archivo            VARCHAR(200) NULL,
  PRIMARY KEY (id),
  KEY ix_doc_empleado (empleado_id),
  CONSTRAINT fk_doc_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_recibo (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id       INT UNSIGNED NOT NULL,
  periodo           DATE NOT NULL,                     -- primer día del mes liquidado
  tipo              ENUM('mensual','sac','complementaria') NOT NULL DEFAULT 'mensual',
  descripcion       VARCHAR(80) NOT NULL,              -- 'Octubre 2026', 'SAC 1.er semestre 2026'
  fecha_pago        DATE NULL,
  neto              DECIMAL(14,2) NOT NULL DEFAULT 0,
  retiene_ganancias TINYINT(1) NOT NULL DEFAULT 0,
  archivo           VARCHAR(200) NULL,                 -- PDF firmado, ruta relativa
  PRIMARY KEY (id),
  KEY ix_recibo_empleado (empleado_id, periodo),
  CONSTRAINT fk_recibo_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_formulario1359 (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id INT UNSIGNED NOT NULL,
  anio        SMALLINT UNSIGNED NOT NULL,
  archivo     VARCHAR(200) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_f1359 (empleado_id, anio),
  CONSTRAINT fk_f1359_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_fichada (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id INT UNSIGNED NOT NULL,
  fecha_hora  DATETIME NOT NULL,
  tipo        CHAR(1) NOT NULL,                        -- 'E' entrada, 'S' salida
  reloj       VARCHAR(60) NULL,
  PRIMARY KEY (id),
  KEY ix_fichada (empleado_id, fecha_hora),
  CONSTRAINT fk_fichada_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_articulo (
  codigo      VARCHAR(10)  NOT NULL,                   -- '13 a', '14 f', 'Com'
  descripcion VARCHAR(120) NOT NULL,
  clase       ENUM('nov','franq') NOT NULL DEFAULT 'nov', -- color en calendario
  jornada     TINYINT(1) NOT NULL DEFAULT 1,           -- 1: cubre la jornada completa
  PRIMARY KEY (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_novedad (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id     INT UNSIGNED NOT NULL,
  articulo_codigo VARCHAR(10) NOT NULL,
  fecha_desde     DATE NOT NULL,
  fecha_hasta     DATE NOT NULL,
  estado          ENUM('aprobada','pendiente','injustificada','descontada') NOT NULL DEFAULT 'aprobada',
  observacion     VARCHAR(255) NULL,
  PRIMARY KEY (id),
  KEY ix_novedad (empleado_id, fecha_desde, fecha_hasta),
  CONSTRAINT fk_novedad_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id),
  CONSTRAINT fk_novedad_articulo FOREIGN KEY (articulo_codigo) REFERENCES ag_articulo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_feriado (
  fecha       DATE NOT NULL,
  descripcion VARCHAR(120) NOT NULL,
  PRIMARY KEY (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_licencia_saldo (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id      INT UNSIGNED NOT NULL,
  articulo_codigo  VARCHAR(10) NOT NULL,
  anio             SMALLINT UNSIGNED NOT NULL,
  unidad           ENUM('dias','horas') NOT NULL DEFAULT 'dias',
  otorgado         DECIMAL(6,1) NOT NULL DEFAULT 0,
  usado            DECIMAL(6,1) NOT NULL DEFAULT 0,
  vence            DATE NULL,
  disponible_desde DATE NULL,
  destacado        TINYINT(1) NOT NULL DEFAULT 0,      -- 1: se muestra como tarjeta arriba
  PRIMARY KEY (id),
  KEY ix_saldo (empleado_id, anio),
  CONSTRAINT fk_saldo_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id),
  CONSTRAINT fk_saldo_articulo FOREIGN KEY (articulo_codigo) REFERENCES ag_articulo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_capacitacion (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id INT UNSIGNED NOT NULL,
  actividad   VARCHAR(200) NOT NULL,
  organismo   VARCHAR(120) NULL,
  fecha_fin   DATE NULL,                               -- NULL: en curso
  creditos    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  estado      ENUM('aprobado','en_curso','desaprobado') NOT NULL DEFAULT 'en_curso',
  PRIMARY KEY (id),
  KEY ix_cap_empleado (empleado_id),
  CONSTRAINT fk_cap_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_evaluacion (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id  INT UNSIGNED NOT NULL,
  anio         SMALLINT UNSIGNED NOT NULL,
  calificacion VARCHAR(40) NOT NULL,
  puntaje      TINYINT UNSIGNED NOT NULL,              -- 1 a 5
  PRIMARY KEY (id),
  UNIQUE KEY uk_eval (empleado_id, anio),
  CONSTRAINT fk_eval_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_carrera (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id INT UNSIGNED NOT NULL,
  descripcion VARCHAR(150) NOT NULL,
  fecha_desde DATE NOT NULL,
  fecha_hasta DATE NULL,                               -- NULL: situación actual
  acto        VARCHAR(80) NULL,                        -- resolución, concurso
  PRIMARY KEY (id),
  KEY ix_carrera_empleado (empleado_id),
  CONSTRAINT fk_carrera_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ag_mensaje (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id INT UNSIGNED NOT NULL,
  remitente   VARCHAR(80) NOT NULL,
  asunto      VARCHAR(150) NOT NULL,
  cuerpo      TEXT NOT NULL,
  fecha       DATETIME NOT NULL,
  leido       TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY ix_mensaje (empleado_id, fecha),
  CONSTRAINT fk_mensaje_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Pedidos que hace el empleado desde el sitio (licencias, reclamos, actualización de datos).
CREATE TABLE IF NOT EXISTS ag_solicitud (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empleado_id INT UNSIGNED NOT NULL,
  tipo        ENUM('licencia','reclamo','datos') NOT NULL,
  articulo_codigo VARCHAR(10) NULL,
  fecha_desde DATE NULL,
  fecha_hasta DATE NULL,
  detalle     TEXT NOT NULL,
  fecha       DATETIME NOT NULL,
  estado      ENUM('enviada','en_tramite','resuelta','rechazada') NOT NULL DEFAULT 'enviada',
  PRIMARY KEY (id),
  KEY ix_solicitud (empleado_id, fecha),
  CONSTRAINT fk_solicitud_empleado FOREIGN KEY (empleado_id) REFERENCES ag_empleado (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
