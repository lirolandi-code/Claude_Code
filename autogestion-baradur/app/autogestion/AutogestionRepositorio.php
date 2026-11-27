<?php

/**
 * Acceso a datos de la autogestión.
 *
 * Todas las consultas SQL del sitio están en esta clase. Si su base de RR.HH. usa otras
 * tablas, adapte solo estas consultas manteniendo los nombres de columnas que devuelven
 * (los alias del SELECT), y el resto del sitio sigue funcionando.
 *
 * Todas las consultas filtran por empleado_id, que sale de la sesión y nunca de la URL.
 * Usa parámetros (?) en lugar de concatenar valores.
 */
class AutogestionRepositorio
{
    /* ---------------- infraestructura ---------------- */

    /** Ejecuta un SELECT y devuelve un array de arrays asociativos. */
    protected function filas($sql, $parametros)
    {
        $resultado = DB::select($sql, $parametros);
        $filas = array();
        if (!$resultado) {
            return $filas;
        }
        foreach ($resultado as $fila) {
            if (is_array($fila)) {
                $filas[] = $fila;
            } elseif (is_object($fila) && method_exists($fila, 'toArray')) {
                $filas[] = $fila->toArray();
            } else {
                $filas[] = get_object_vars($fila);
            }
        }
        return $filas;
    }

    protected function fila($sql, $parametros)
    {
        $filas = $this->filas($sql, $parametros);
        return count($filas) ? $filas[0] : null;
    }

    protected function ejecutar($sql, $parametros)
    {
        return DB::statement($sql, $parametros);
    }

    /* ---------------- empleado ---------------- */

    public function empleadoParaIngreso($usuario)
    {
        $usuario = trim((string) $usuario);
        $soloDigitos = preg_replace('/\D/', '', $usuario);
        if ($soloDigitos === '') {
            return null;
        }
        // Acepta legajo o CUIL (con o sin guiones).
        return $this->fila(
            "SELECT id, legajo, clave, activo FROM ag_empleado
             WHERE activo = 1 AND (legajo = ? OR REPLACE(cuil, '-', '') = ?)",
            array($soloDigitos, $soloDigitos)
        );
    }

    public function empleado($empleadoId)
    {
        return $this->fila(
            "SELECT id, legajo, cuil, apellido, nombre, tipo_documento, nro_documento, fecha_nacimiento,
                    lugar_nacimiento, sexo, estado_civil, nacionalidad, nivel_estudios, titulo, domicilio,
                    email, telefono, dependencia_codigo, dependencia_nombre, sede, modalidad, escalafon,
                    agrupamiento, nivel, grado, tramo, fecha_ingreso, horario_desde, horario_hasta,
                    categoria_horaria, creditos_actuales, creditos_requeridos, foto
             FROM ag_empleado WHERE id = ? AND activo = 1",
            array((int) $empleadoId)
        );
    }

    public function documentacion($empleadoId)
    {
        return $this->filas(
            "SELECT id, descripcion, estado, fecha_presentacion, fecha_vencimiento, archivo
             FROM ag_documentacion WHERE empleado_id = ? ORDER BY descripcion",
            array((int) $empleadoId)
        );
    }

    public function documento($empleadoId, $documentoId)
    {
        return $this->fila(
            "SELECT id, descripcion, estado FROM ag_documentacion WHERE id = ? AND empleado_id = ?",
            array((int) $documentoId, (int) $empleadoId)
        );
    }

    public function guardarArchivoDocumento($empleadoId, $documentoId, $archivo, $hoy)
    {
        return $this->ejecutar(
            "UPDATE ag_documentacion SET archivo = ?, estado = 'en_revision', fecha_presentacion = ?
             WHERE id = ? AND empleado_id = ?",
            array($archivo, $hoy, (int) $documentoId, (int) $empleadoId)
        );
    }

    /* ---------------- haberes ---------------- */

    public function aniosConRecibos($empleadoId)
    {
        $filas = $this->filas(
            "SELECT DISTINCT YEAR(periodo) AS anio FROM ag_recibo WHERE empleado_id = ? ORDER BY anio DESC",
            array((int) $empleadoId)
        );
        $anios = array();
        foreach ($filas as $f) {
            $anios[] = (int) $f['anio'];
        }
        return $anios;
    }

    public function recibos($empleadoId, $anio)
    {
        return $this->filas(
            "SELECT id, periodo, tipo, descripcion, fecha_pago, neto, retiene_ganancias, archivo
             FROM ag_recibo
             WHERE empleado_id = ? AND periodo >= ? AND periodo <= ?
             ORDER BY periodo DESC, fecha_pago DESC",
            array((int) $empleadoId, $anio . '-01-01', $anio . '-12-31')
        );
    }

    public function ultimoRecibo($empleadoId)
    {
        return $this->fila(
            "SELECT id, periodo, tipo, descripcion, fecha_pago, neto, retiene_ganancias, archivo
             FROM ag_recibo WHERE empleado_id = ? ORDER BY fecha_pago DESC, periodo DESC LIMIT 1",
            array((int) $empleadoId)
        );
    }

    public function recibo($empleadoId, $reciboId)
    {
        return $this->fila(
            "SELECT id, descripcion, archivo FROM ag_recibo WHERE id = ? AND empleado_id = ?",
            array((int) $reciboId, (int) $empleadoId)
        );
    }

    public function formulario1359($empleadoId, $anio)
    {
        return $this->fila(
            "SELECT anio, archivo FROM ag_formulario1359 WHERE empleado_id = ? AND anio = ?",
            array((int) $empleadoId, (int) $anio)
        );
    }

    /* ---------------- asistencia ---------------- */

    public function fichadas($empleadoId, $desde, $hasta)
    {
        return $this->filas(
            "SELECT fecha_hora, tipo, reloj FROM ag_fichada
             WHERE empleado_id = ? AND fecha_hora >= ? AND fecha_hora <= ?
             ORDER BY fecha_hora",
            array((int) $empleadoId, $desde . ' 00:00:00', $hasta . ' 23:59:59')
        );
    }

    public function novedades($empleadoId, $desde, $hasta)
    {
        return $this->filas(
            "SELECT n.id, n.articulo_codigo, a.descripcion, a.clase, a.jornada, n.fecha_desde, n.fecha_hasta,
                    n.estado, n.observacion
             FROM ag_novedad n INNER JOIN ag_articulo a ON a.codigo = n.articulo_codigo
             WHERE n.empleado_id = ? AND n.fecha_desde <= ? AND n.fecha_hasta >= ?
             ORDER BY n.fecha_desde",
            array((int) $empleadoId, $hasta, $desde)
        );
    }

    public function feriados($desde, $hasta)
    {
        $filas = $this->filas(
            "SELECT fecha, descripcion FROM ag_feriado WHERE fecha >= ? AND fecha <= ? ORDER BY fecha",
            array($desde, $hasta)
        );
        $feriados = array();
        foreach ($filas as $f) {
            $feriados[substr($f['fecha'], 0, 10)] = $f['descripcion'];
        }
        return $feriados;
    }

    public function proximoFeriado($desde)
    {
        return $this->fila(
            "SELECT fecha, descripcion FROM ag_feriado WHERE fecha >= ? ORDER BY fecha LIMIT 1",
            array($desde)
        );
    }

    public function saldosLicencia($empleadoId)
    {
        return $this->filas(
            "SELECT s.articulo_codigo, a.descripcion, s.anio, s.unidad, s.otorgado, s.usado, s.vence,
                    s.disponible_desde, s.destacado
             FROM ag_licencia_saldo s INNER JOIN ag_articulo a ON a.codigo = s.articulo_codigo
             WHERE s.empleado_id = ?
             ORDER BY s.destacado DESC, s.anio, s.articulo_codigo",
            array((int) $empleadoId)
        );
    }

    public function articulosSolicitables()
    {
        return $this->filas("SELECT codigo, descripcion FROM ag_articulo ORDER BY codigo", array());
    }

    public function articuloExiste($codigo)
    {
        return $this->fila("SELECT codigo FROM ag_articulo WHERE codigo = ?", array($codigo)) !== null;
    }

    /* ---------------- carrera ---------------- */

    public function capacitaciones($empleadoId)
    {
        return $this->filas(
            "SELECT actividad, organismo, fecha_fin, creditos, estado FROM ag_capacitacion
             WHERE empleado_id = ? ORDER BY (fecha_fin IS NULL) DESC, fecha_fin DESC",
            array((int) $empleadoId)
        );
    }

    public function evaluaciones($empleadoId)
    {
        return $this->filas(
            "SELECT anio, calificacion, puntaje FROM ag_evaluacion WHERE empleado_id = ? ORDER BY anio DESC",
            array((int) $empleadoId)
        );
    }

    public function carrera($empleadoId)
    {
        return $this->filas(
            "SELECT descripcion, fecha_desde, fecha_hasta, acto FROM ag_carrera
             WHERE empleado_id = ? ORDER BY fecha_desde DESC",
            array((int) $empleadoId)
        );
    }

    /* ---------------- mensajes y solicitudes ---------------- */

    public function mensajes($empleadoId)
    {
        return $this->filas(
            "SELECT id, remitente, asunto, cuerpo, fecha, leido FROM ag_mensaje
             WHERE empleado_id = ? ORDER BY fecha DESC LIMIT 100",
            array((int) $empleadoId)
        );
    }

    public function mensaje($empleadoId, $mensajeId)
    {
        return $this->fila(
            "SELECT id, remitente, asunto, cuerpo, fecha, leido FROM ag_mensaje WHERE id = ? AND empleado_id = ?",
            array((int) $mensajeId, (int) $empleadoId)
        );
    }

    public function marcarLeido($empleadoId, $mensajeId)
    {
        return $this->ejecutar(
            "UPDATE ag_mensaje SET leido = 1 WHERE id = ? AND empleado_id = ?",
            array((int) $mensajeId, (int) $empleadoId)
        );
    }

    public function mensajesSinLeer($empleadoId)
    {
        $f = $this->fila(
            "SELECT COUNT(*) AS cantidad FROM ag_mensaje WHERE empleado_id = ? AND leido = 0",
            array((int) $empleadoId)
        );
        return $f ? (int) $f['cantidad'] : 0;
    }

    public function crearSolicitud($empleadoId, $tipo, $articulo, $desde, $hasta, $detalle, $ahora)
    {
        return $this->ejecutar(
            "INSERT INTO ag_solicitud (empleado_id, tipo, articulo_codigo, fecha_desde, fecha_hasta, detalle, fecha)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            array((int) $empleadoId, $tipo, $articulo, $desde, $hasta, $detalle, $ahora)
        );
    }
}
