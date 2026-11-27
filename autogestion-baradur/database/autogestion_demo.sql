-- Datos de ejemplo (ficticios) para probar la autogestión. NO usar en producción.
-- Empleado de prueba: legajo 3172 / CUIL 20-25817734-6.
-- La contraseña se genera con el Hash del framework: ver README, sección 'Contraseña de prueba'.
SET NAMES utf8;

INSERT INTO ag_empleado (id, legajo, cuil, apellido, nombre, tipo_documento, nro_documento, fecha_nacimiento, lugar_nacimiento, sexo, estado_civil, nacionalidad, nivel_estudios, titulo, domicilio, email, telefono, dependencia_codigo, dependencia_nombre, sede, modalidad, escalafon, agrupamiento, nivel, grado, tramo, fecha_ingreso, horario_desde, horario_hasta, categoria_horaria, creditos_actuales, creditos_requeridos, clave, activo) VALUES
(1, 3172, '20-25817734-6', 'Sosa', 'Martín Ezequiel', 'DNI', 25817734, '1977-07-17', 'Ramos Mejía, Buenos Aires', 'Masculino', 'Casado', 'Argentina (nativo)', 'Universitario de grado', 'Licenciado en Administración Pública', 'Av. Rivadavia 14.250, Ramos Mejía', 'msosa@organismo.gob.ar', '11 4658-0000', 'A406010400', 'Dirección General de Recursos Humanos', 'Edificio central · Piso 4', 'Planta permanente', 'SINEP', 'General', 'B', 11, 'Intermedio', '2010-02-01', '10:30:00', '18:30:00', 'B11', 64, 72, NULL, 1);

INSERT INTO ag_articulo (codigo, descripcion, clase, jornada) VALUES
('13 a', 'Licencia anual ordinaria', 'nov', 1),
('14 f', 'Razones particulares', 'nov', 1),
('14 a', 'Atención de familiar enfermo', 'nov', 1),
('10 a', 'Enfermedad de corto tratamiento', 'nov', 1),
('Com', 'Comisión de servicio', 'nov', 1),
('FH', 'Franquicia horaria', 'franq', 0),
('Tar', 'Tardanza', 'nov', 0);

INSERT INTO ag_feriado (fecha, descripcion) VALUES
('2026-10-12', 'Día del Respeto a la Diversidad Cultural'),
('2026-11-23', 'Día de la Soberanía Nacional'),
('2026-12-08', 'Inmaculada Concepción de María'),
('2026-12-25', 'Navidad');

INSERT INTO ag_documentacion (empleado_id, descripcion, estado, fecha_presentacion, fecha_vencimiento) VALUES
(1, 'Constancia de CUIL', 'presentado', '2010-03-02', NULL),
(1, 'Título universitario', 'presentado', '2012-06-15', NULL),
(1, 'Certificado de domicilio', 'vencido', '2025-09-30', '2026-09-30'),
(1, 'Declaración jurada de cargos', 'pendiente', NULL, '2026-12-15'),
(1, 'Apto médico preocupacional', 'presentado', '2010-02-01', NULL);

INSERT INTO ag_recibo (empleado_id, periodo, tipo, descripcion, fecha_pago, neto, retiene_ganancias, archivo) VALUES
(1, '2026-10-01', 'mensual', 'Octubre 2026', '2026-11-03', 1842350.12, 1, 'recibos/1/2026-10_mensual.pdf'),
(1, '2026-09-01', 'mensual', 'Septiembre 2026', '2026-10-02', 1836904.55, 1, NULL),
(1, '2026-08-01', 'mensual', 'Agosto 2026', '2026-09-03', 1701118.40, 1, NULL),
(1, '2026-07-01', 'mensual', 'Julio 2026', '2026-08-04', 1698442.07, 1, NULL),
(1, '2026-06-01', 'sac', 'SAC 1.er semestre 2026', '2026-06-30', 851210.90, 0, NULL),
(1, '2026-06-01', 'mensual', 'Junio 2026', '2026-07-03', 1612770.31, 1, NULL),
(1, '2025-12-01', 'mensual', 'Diciembre 2025', '2026-01-05', 1420110.00, 1, NULL),
(1, '2025-11-01', 'mensual', 'Noviembre 2025', '2025-12-03', 1398004.21, 1, NULL);

INSERT INTO ag_formulario1359 (empleado_id, anio, archivo) VALUES (1, 2025, 'f1359/1/2025.pdf');

INSERT INTO ag_fichada (empleado_id, fecha_hora, tipo, reloj) VALUES
(1, '2026-10-01 10:27:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-10-01 18:22:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-10-02 10:18:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-02 18:07:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-05 10:26:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-05 18:25:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-06 10:20:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-06 18:12:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-07 10:12:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-10-07 18:33:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-10-08 10:34:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-08 18:17:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-09 10:19:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-09 18:46:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-13 10:21:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-10-13 18:32:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-10-14 10:36:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-14 18:47:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-15 10:28:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-15 18:20:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-16 10:09:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-10-16 18:37:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-10-19 10:33:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-10-19 18:27:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-10-20 10:11:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-20 18:37:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-22 10:15:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-10-22 18:31:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-10-23 10:24:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-23 18:07:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-26 10:13:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-26 18:08:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-27 10:18:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-27 18:07:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-28 10:10:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-10-28 18:40:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-10-29 10:30:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-29 18:26:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-10-30 10:24:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-10-30 18:26:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-02 10:37:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-02 18:42:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-03 10:11:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-03 18:21:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-04 10:36:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-11-04 18:23:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-11-05 10:18:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-05 18:46:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-09 10:23:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-09 18:21:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-10 10:19:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-11-10 18:28:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-11-11 10:18:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-11 18:10:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-13 10:16:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-11-13 18:13:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-11-16 10:34:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-11-16 18:37:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-11-17 10:24:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-18 10:13:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-18 18:33:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-19 10:18:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-11-19 16:30:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-11-20 10:33:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-20 18:19:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-24 10:35:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-24 18:25:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-25 11:20:00', 'E', 'Anexo · Reloj 2'),
(1, '2026-11-25 18:45:00', 'S', 'Anexo · Reloj 2'),
(1, '2026-11-26 10:28:00', 'E', 'Hall central · Reloj 1'),
(1, '2026-11-26 18:30:00', 'S', 'Hall central · Reloj 1'),
(1, '2026-11-27 10:22:00', 'E', 'Hall central · Reloj 1');

INSERT INTO ag_novedad (empleado_id, articulo_codigo, fecha_desde, fecha_hasta, estado, observacion) VALUES
(1, '14 f', '2026-10-21', '2026-10-21', 'aprobada', NULL),
(1, '14 f', '2026-11-06', '2026-11-06', 'aprobada', NULL),
(1, 'Com', '2026-11-12', '2026-11-12', 'aprobada', 'Delegación Rosario'),
(1, 'FH', '2026-11-19', '2026-11-19', 'aprobada', 'Salida 16:30'),
(1, 'Tar', '2026-11-25', '2026-11-25', 'pendiente', 'Ingreso 11:20');

INSERT INTO ag_licencia_saldo (empleado_id, articulo_codigo, anio, unidad, otorgado, usado, vence, disponible_desde, destacado) VALUES
(1, '13 a', 2025, 'dias', 25, 18, '2027-05-31', NULL, 1),
(1, '13 a', 2026, 'dias', 25, 0, NULL, '2026-12-01', 1),
(1, '14 f', 2026, 'dias', 6, 4, '2026-12-31', NULL, 1),
(1, '10 a', 2026, 'dias', 45, 3, NULL, NULL, 0),
(1, 'FH', 2026, 'horas', 20, 9, NULL, NULL, 0),
(1, '14 a', 2026, 'dias', 20, 0, NULL, NULL, 0);

INSERT INTO ag_capacitacion (empleado_id, actividad, organismo, fecha_fin, creditos, estado) VALUES
(1, 'Gestión por resultados en el sector público', 'INAP', '2026-10-12', 12, 'aprobado'),
(1, 'Excel avanzado para análisis de datos', 'INAP', '2026-08-30', 9, 'aprobado'),
(1, 'Ética pública y transparencia', 'Oficina Anticorrupción', '2026-05-05', 6, 'aprobado'),
(1, 'Redacción administrativa', 'INAP', NULL, 8, 'en_curso');

INSERT INTO ag_evaluacion (empleado_id, anio, calificacion, puntaje) VALUES
(1, 2025, 'Muy destacado', 5), (1, 2024, 'Destacado', 4), (1, 2023, 'Destacado', 4), (1, 2022, 'Bueno', 3);

INSERT INTO ag_carrera (empleado_id, descripcion, fecha_desde, fecha_hasta, acto) VALUES
(1, 'Grado 11 · Tramo intermedio', '2025-01-01', NULL, 'Res. 214/2024'),
(1, 'Grado 10', '2023-01-01', '2024-12-31', NULL),
(1, 'Promoción a Nivel B', '2018-07-01', '2022-12-31', 'Concurso 45/2018'),
(1, 'Ingreso a planta permanente · Nivel C, Grado 0', '2010-02-01', '2018-06-30', NULL);

INSERT INTO ag_mensaje (empleado_id, remitente, asunto, cuerpo, fecha, leido) VALUES
(1, 'Recursos Humanos', 'Recibo de octubre disponible', 'Ya puede descargar su recibo de sueldo de octubre 2026 desde la sección Haberes.', '2026-11-03 09:15:00', 0),
(1, 'Asistencia', 'Tardanza pendiente de justificación', 'Registramos un ingreso a las 11:20 el 25/11/2026.\n\nTiene 48 horas para justificarla desde la sección Asistencia.', '2026-11-25 12:02:00', 0),
(1, 'Capacitación', 'Inscripción abierta: Redacción administrativa', 'Curso virtual de 8 créditos. Cupos limitados.', '2026-10-14 10:00:00', 1),
(1, 'Recursos Humanos', 'Actualización de domicilio', 'Recuerde mantener su certificado de domicilio vigente.', '2026-10-01 08:30:00', 1);
