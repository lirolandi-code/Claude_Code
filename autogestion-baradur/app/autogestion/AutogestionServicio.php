<?php

/**
 * Lógica de la autogestión: arma los datos que muestra cada vista.
 * Las vistas solo imprimen; todos los cálculos (horas, porcentajes, estados) se hacen acá.
 *
 * Compatible con PHP 5.1.6.
 */
class AutogestionServicio
{
    /** @var AutogestionRepositorio */
    protected $repo;

    public function __construct($repositorio = null)
    {
        $this->repo = $repositorio ? $repositorio : new AutogestionRepositorio();
        Ag::zonaHoraria();
    }

    /* ==================================================================
     * Datos comunes a todas las pantallas (barra lateral y superior)
     * ================================================================== */

    public function comun($empleadoId, $seccion, $titulo, $migas)
    {
        $e = $this->repo->empleado($empleadoId);
        if (!$e) {
            return null;
        }
        return array(
            'empleado' => $e,
            'iniciales' => Ag::iniciales($e['nombre'], $e['apellido']),
            'nombreCorto' => $this->nombreCorto($e),
            'sinLeer' => $this->repo->mensajesSinLeer($empleadoId),
            'seccion' => $seccion,
            'titulo' => $titulo,
            'migas' => $migas,
            'flash' => Ag::tomarFlash(),
        );
    }

    protected function nombreCorto($e)
    {
        $nombres = explode(' ', trim($e['nombre']));
        $texto = $nombres[0];
        if (isset($nombres[1]) && $nombres[1] !== '') {
            $texto .= ' ' . Ag::inicial($nombres[1]) . '.';
        }
        return $texto . ' ' . $e['apellido'];
    }

    /* ==================================================================
     * Inicio
     * ================================================================== */

    public function inicio($empleadoId)
    {
        $hoy = Ag::hoy();
        $e = $this->repo->empleado($empleadoId);
        $periodo = substr($hoy, 0, 7);
        $mes = $this->diasDelMes($empleadoId, $periodo, $e);

        // Semana actual (lunes a viernes).
        $ts = Ag::ts($hoy);
        $dow = (int) date('w', $ts);
        $lunes = $ts - (($dow == 0 ? 6 : $dow - 1) * 86400);
        $desdeSemana = date('Y-m-d', $lunes);
        $hastaSemana = date('Y-m-d', $lunes + 4 * 86400);
        $semana = $this->diasRango($empleadoId, $desdeSemana, $hastaSemana, $e);

        // Saldo de vacaciones: primera tarjeta destacada.
        $vacaciones = null;
        $saldos = $this->repo->saldosLicencia($empleadoId);
        foreach ($saldos as $s) {
            if ($s['destacado'] && (float) $s['otorgado'] > 0) {
                $vacaciones = $this->saldo($s);
                break;
            }
        }

        $creditos = $this->creditos($e);

        return array(
            'saludo' => $this->saludo(),
            'fechaLarga' => $this->fechaLarga($hoy),
            'primerNombre' => current(explode(' ', trim($e['nombre']))),
            'ultimoRecibo' => $this->reciboVista($this->repo->ultimoRecibo($empleadoId)),
            'semana' => $semana,
            'vacaciones' => $vacaciones,
            'promedio' => $mes['resumen']['promedio'],
            'diasCompletos' => $mes['resumen']['trabajados'],
            'horario' => $this->horarioTexto($e),
            'creditos' => $creditos,
            'pendientes' => $this->pendientes($empleadoId, $mes['dias']),
            'proximoFeriado' => $this->proximoFeriado($hoy),
        );
    }

    protected function saludo()
    {
        $h = (int) date('G');
        if ($h < 12) {
            return 'Buenos días';
        }
        return $h < 20 ? 'Buenas tardes' : 'Buenas noches';
    }

    protected function fechaLarga($ymd)
    {
        $ts = Ag::ts($ymd);
        return Ag::diaLargo(date('w', $ts)) . ' ' . (int) date('j', $ts) . ' de ' . Ag::nombreMes(date('n', $ts)) . ' de ' . date('Y', $ts);
    }

    protected function proximoFeriado($hoy)
    {
        $f = $this->repo->proximoFeriado($hoy);
        if (!$f) {
            return null;
        }
        $ts = Ag::ts($f['fecha']);
        return array(
            'fecha' => Ag::fecha($f['fecha']),
            'texto' => (int) date('j', $ts) . ' de ' . Ag::nombreMes(date('n', $ts)),
            'descripcion' => $f['descripcion'],
        );
    }

    protected function pendientes($empleadoId, $diasMes)
    {
        $lista = array();
        foreach ($diasMes as $d) {
            foreach ($d['novedades'] as $n) {
                if ($n['estado'] == 'pendiente') {
                    $lista[] = array('icono' => 'clock', 'tono' => 'warn', 'titulo' => 'Justificar: ' . $n['descripcion'],
                        'detalle' => $d['fechaTexto'] . ($d['entrada'] !== null ? ' · ingreso ' . Ag::horas($d['entrada']) : ''),
                        'accion' => 'Justificar', 'url' => Ag::url('solicitud/reclamo?fecha=' . $d['fecha']));
                }
            }
            if ($d['inconsistente']) {
                $lista[] = array('icono' => 'alert', 'tono' => 'danger', 'titulo' => 'Fichada de ' . ($d['entrada'] === null ? 'entrada' : 'salida') . ' faltante',
                    'detalle' => $d['fechaTexto'], 'accion' => 'Reclamar', 'url' => Ag::url('solicitud/reclamo?fecha=' . $d['fecha']));
            }
        }
        foreach ($this->repo->documentacion($empleadoId) as $doc) {
            if ($doc['estado'] == 'pendiente' || $doc['estado'] == 'vencido') {
                $lista[] = array('icono' => 'file', 'tono' => $doc['estado'] == 'vencido' ? 'danger' : 'brand', 'titulo' => $doc['descripcion'],
                    'detalle' => $doc['estado'] == 'vencido' ? 'Venció el ' . Ag::fecha($doc['fecha_vencimiento']) : ($doc['fecha_vencimiento'] ? 'Vence el ' . Ag::fecha($doc['fecha_vencimiento']) : 'Pendiente de presentación'),
                    'accion' => 'Presentar', 'url' => Ag::url('documentacion'));
            }
        }
        return $lista;
    }

    /* ==================================================================
     * Datos personales y documentación
     * ================================================================== */

    public function datos($empleadoId)
    {
        $e = $this->repo->empleado($empleadoId);
        return array(
            'identificacion' => array(
                array('Legajo', $e['legajo'], true),
                array('Apellido y nombre', $e['apellido'] . ', ' . $e['nombre'], false),
                array('Documento', $e['tipo_documento'] . ' ' . Ag::miles($e['nro_documento']), true),
                array('CUIL', $e['cuil'], true),
                array('Fecha de nacimiento', Ag::fecha($e['fecha_nacimiento']), true),
                array('Lugar de nacimiento', $e['lugar_nacimiento'], false),
                array('Sexo', $e['sexo'], false),
                array('Estado civil', $e['estado_civil'], false),
                array('Nacionalidad', $e['nacionalidad'], false),
            ),
            'formacion' => array(
                array('Nivel de estudios', $e['nivel_estudios'], false),
                array('Título', $e['titulo'], false),
            ),
            'contacto' => array(
                array('Domicilio', $e['domicilio'], false),
                array('Correo', $e['email'], false),
                array('Teléfono', $e['telefono'], true),
            ),
            'docsPendientes' => $this->contarDocsPendientes($empleadoId),
        );
    }

    protected function contarDocsPendientes($empleadoId)
    {
        $n = 0;
        foreach ($this->repo->documentacion($empleadoId) as $d) {
            if ($d['estado'] == 'pendiente' || $d['estado'] == 'vencido') {
                $n++;
            }
        }
        return $n;
    }

    public function documentacion($empleadoId)
    {
        $estados = array(
            'presentado' => array('ok', 'Presentado', 'check'),
            'en_revision' => array('info', 'En revisión', 'clock'),
            'pendiente' => array('warn', 'Pendiente', 'file'),
            'vencido' => array('danger', 'Vencido', 'alert'),
        );
        $docs = array();
        foreach ($this->repo->documentacion($empleadoId) as $d) {
            $est = isset($estados[$d['estado']]) ? $estados[$d['estado']] : $estados['pendiente'];
            if ($d['estado'] == 'vencido') {
                $detalle = 'Venció el ' . Ag::fecha($d['fecha_vencimiento']);
            } elseif ($d['estado'] == 'pendiente') {
                $detalle = $d['fecha_vencimiento'] ? 'Vence el ' . Ag::fecha($d['fecha_vencimiento']) : 'Pendiente de presentación';
            } else {
                $detalle = $d['fecha_presentacion'] ? 'Presentado el ' . Ag::fecha($d['fecha_presentacion']) : '';
            }
            $docs[] = array(
                'id' => $d['id'],
                'descripcion' => $d['descripcion'],
                'detalle' => $detalle,
                'tono' => $est[0],
                'estado' => $est[1],
                'icono' => $est[2],
                'puedeSubir' => ($d['estado'] == 'pendiente' || $d['estado'] == 'vencido'),
            );
        }
        return array('documentos' => $docs);
    }

    /** Guarda un archivo subido para un documento. Devuelve '' si salió bien o el mensaje de error. */
    public function subirDocumento($empleadoId, $documentoId, $archivo)
    {
        $doc = $this->repo->documento($empleadoId, $documentoId);
        if (!$doc) {
            return 'No encontramos ese documento.';
        }
        if (!is_array($archivo) || !isset($archivo['error']) || $archivo['error'] != 0 || !is_uploaded_file($archivo['tmp_name'])) {
            return 'No recibimos el archivo. Vuelva a seleccionarlo e intente de nuevo.';
        }
        if ($archivo['size'] > 10 * 1024 * 1024) {
            return 'El archivo supera los 10 MB. Reduzca su tamaño e intente de nuevo.';
        }
        $ext = strtolower(substr(strrchr($archivo['name'], '.'), 1));
        if (!in_array($ext, array('pdf', 'jpg', 'jpeg', 'png'))) {
            return 'Solo se aceptan archivos PDF, JPG o PNG.';
        }
        $carpeta = 'documentos/' . (int) $empleadoId;
        $destinoDir = Ag::carpetaArchivos() . $carpeta;
        if (!is_dir($destinoDir) && !@mkdir($destinoDir, 0750, true)) {
            return 'No se pudo guardar el archivo. Avise a Recursos Humanos.';
        }
        $nombre = md5(uniqid(mt_rand(), true)) . '.' . $ext;
        if (!@move_uploaded_file($archivo['tmp_name'], $destinoDir . DIRECTORY_SEPARATOR . $nombre)) {
            return 'No se pudo guardar el archivo. Avise a Recursos Humanos.';
        }
        $this->repo->guardarArchivoDocumento($empleadoId, $documentoId, $carpeta . '/' . $nombre, Ag::hoy());
        return '';
    }

    /* ==================================================================
     * Haberes
     * ================================================================== */

    public function haberes($empleadoId, $anioPedido)
    {
        $anios = $this->repo->aniosConRecibos($empleadoId);
        $anio = (int) $anioPedido;
        if (!in_array($anio, $anios)) {
            $anio = count($anios) ? $anios[0] : (int) date('Y');
        }
        $recibos = array();
        foreach ($this->repo->recibos($empleadoId, $anio) as $r) {
            $recibos[] = $this->reciboVista($r);
        }
        return array(
            'anios' => $anios,
            'anio' => $anio,
            'recibos' => $recibos,
            'hayF1359' => $this->repo->formulario1359($empleadoId, $anio) !== null,
        );
    }

    protected function reciboVista($r)
    {
        if (!$r) {
            return null;
        }
        $meses = array(1 => 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC');
        $mes = (int) substr($r['periodo'], 5, 2);
        return array(
            'id' => $r['id'],
            'descripcion' => $r['descripcion'],
            'tipo' => $r['tipo'] == 'sac' ? 'Aguinaldo' : ($r['tipo'] == 'complementaria' ? 'Complementaria' : 'Mensual'),
            'sigla' => $r['tipo'] == 'sac' ? 'SAC' : $meses[$mes],
            'anioCorto' => substr($r['periodo'], 2, 2),
            'fechaPago' => Ag::fecha($r['fecha_pago']),
            'neto' => Ag::dinero($r['neto']),
            'ganancias' => (bool) $r['retiene_ganancias'],
            'tieneArchivo' => $r['archivo'] ? true : false,
        );
    }

    /* ==================================================================
     * Asistencia
     * ================================================================== */

    /** Días de un período 'YYYY-MM' con fichadas, novedades y feriados. */
    public function diasDelMes($empleadoId, $periodo, $e = null)
    {
        $desde = $periodo . '-01';
        $hasta = $periodo . '-' . date('t', Ag::ts($desde));
        $dias = $this->diasRango($empleadoId, $desde, $hasta, $e);

        $laborables = 0;
        $trabajados = 0;
        $suma = 0;
        foreach ($dias as $d) {
            if (!$d['finde'] && !$d['feriado']) {
                $laborables++;
            }
            if ($d['horas'] !== null) {
                $trabajados++;
                $suma += $d['horas'];
            }
        }
        return array(
            'dias' => $dias,
            'resumen' => array(
                'laborables' => $laborables,
                'trabajados' => $trabajados,
                'promedio' => $trabajados ? $suma / $trabajados : null,
            ),
        );
    }

    public function diasRango($empleadoId, $desde, $hasta, $e = null)
    {
        if ($e === null) {
            $e = $this->repo->empleado($empleadoId);
        }
        $hoy = Ag::hoy();
        $feriados = $this->repo->feriados($desde, $hasta);

        // Primera entrada y última salida de cada día.
        $jornadas = array();
        foreach ($this->repo->fichadas($empleadoId, $desde, $hasta) as $f) {
            $fecha = substr($f['fecha_hora'], 0, 10);
            $hora = Ag::aDecimal(substr($f['fecha_hora'], 11, 8));
            if (!isset($jornadas[$fecha])) {
                $jornadas[$fecha] = array('entrada' => null, 'salida' => null, 'relojE' => '', 'relojS' => '');
            }
            if (strtoupper($f['tipo']) == 'E') {
                if ($jornadas[$fecha]['entrada'] === null || $hora < $jornadas[$fecha]['entrada']) {
                    $jornadas[$fecha]['entrada'] = $hora;
                    $jornadas[$fecha]['relojE'] = $f['reloj'];
                }
            } else {
                if ($jornadas[$fecha]['salida'] === null || $hora > $jornadas[$fecha]['salida']) {
                    $jornadas[$fecha]['salida'] = $hora;
                    $jornadas[$fecha]['relojS'] = $f['reloj'];
                }
            }
        }

        $novedades = $this->repo->novedades($empleadoId, $desde, $hasta);

        $dias = array();
        for ($ts = Ag::ts($desde); $ts <= Ag::ts($hasta); $ts += 86400) {
            $fecha = date('Y-m-d', $ts);
            $dow = (int) date('w', $ts);
            $j = isset($jornadas[$fecha]) ? $jornadas[$fecha] : array('entrada' => null, 'salida' => null, 'relojE' => '', 'relojS' => '');
            $finde = ($dow == 0 || $dow == 6);
            $feriado = isset($feriados[$fecha]) ? $feriados[$fecha] : null;
            $esHoy = ($fecha == $hoy);
            $pasado = ($fecha < $hoy);

            $nov = array();
            foreach ($novedades as $n) {
                if ($n['fecha_desde'] <= $fecha && substr($n['fecha_hasta'], 0, 10) >= $fecha) {
                    $nov[] = array(
                        'codigo' => $n['articulo_codigo'],
                        'descripcion' => $n['descripcion'],
                        'estado' => $n['estado'],
                        'jornada' => (int) $n['jornada'],
                        'clase' => $this->claseNovedad($n),
                    );
                }
            }

            $horas = ($j['entrada'] !== null && $j['salida'] !== null) ? $j['salida'] - $j['entrada'] : null;
            $inconsistente = $pasado && !$finde && !$feriado && (($j['entrada'] !== null) != ($j['salida'] !== null));
            $sinRegistro = $pasado && !$finde && !$feriado && $j['entrada'] === null && $j['salida'] === null && !count($nov);

            $dias[] = array(
                'fecha' => $fecha,
                'fechaTexto' => Ag::fecha($fecha),
                'dia' => (int) date('j', $ts),
                'dow' => $dow,
                'diaCorto' => Ag::diaCorto($dow),
                'finde' => $finde,
                'feriado' => $feriado,
                'hoy' => $esHoy,
                'futuro' => $fecha > $hoy,
                'entrada' => $j['entrada'],
                'salida' => $j['salida'],
                'relojE' => $j['relojE'],
                'relojS' => $j['relojS'],
                'horas' => $horas,
                'novedades' => $nov,
                'inconsistente' => $inconsistente,
                'sinRegistro' => $sinRegistro,
            );
        }
        return $dias;
    }

    protected function claseNovedad($n)
    {
        if ($n['estado'] == 'pendiente') {
            return 'pend';
        }
        if ($n['estado'] == 'injustificada' || $n['estado'] == 'descontada') {
            return 'inj';
        }
        return $n['clase'] == 'franq' ? 'franq' : 'nov';
    }

    public function calendario($empleadoId, $periodo)
    {
        $e = $this->repo->empleado($empleadoId);
        $mes = $this->diasDelMes($empleadoId, $periodo, $e);

        $celdas = array();
        $primero = $mes['dias'][0]['dow'];
        for ($i = 0; $i < $primero; $i++) {
            $celdas[] = null;
        }
        foreach ($mes['dias'] as $d) {
            $clases = array();
            if ($d['finde']) {
                $clases[] = 'we';
            }
            if ($d['feriado']) {
                $clases[] = 'fer';
            }
            if ($d['hoy']) {
                $clases[] = 'today';
            }
            $etiquetas = array();
            foreach ($d['novedades'] as $n) {
                $etiquetas[] = array('clase' => $n['clase'], 'texto' => $n['codigo'] . ' · ' . $n['descripcion'] . ($n['estado'] == 'pendiente' ? ' (a justificar)' : ''));
            }
            if ($d['inconsistente']) {
                $etiquetas[] = array('clase' => 'inc', 'texto' => 'Falta fichada de ' . ($d['entrada'] === null ? 'entrada' : 'salida'));
            }
            if ($d['sinRegistro']) {
                $etiquetas[] = array('clase' => 'inj', 'texto' => 'Sin registro');
            }
            $horas = '';
            if ($d['horas'] !== null) {
                $horas = Ag::horas($d['horas']);
            } elseif ($d['entrada'] !== null && $d['hoy']) {
                $horas = 'en curso';
            }
            $d['clases'] = implode(' ', $clases);
            $d['horasTexto'] = $horas;
            $d['etiquetas'] = $etiquetas;
            $celdas[] = $d;
        }
        while (count($celdas) % 7 != 0) {
            $celdas[] = null;
        }

        return array_merge($this->cabeceraAsistencia($e, $periodo), array(
            'semanas' => array_chunk($celdas, 7),
        ));
    }

    public function bandas($empleadoId, $periodo)
    {
        $e = $this->repo->empleado($empleadoId);
        $mes = $this->diasDelMes($empleadoId, $periodo, $e);
        $oficialDesde = Ag::aDecimal($e['horario_desde']);
        $oficialHasta = Ag::aDecimal($e['horario_hasta']);
        if ($oficialDesde === null || $oficialHasta === null) {
            $oficialDesde = 9;
            $oficialHasta = 17;
        }
        $ahora = (int) date('G') + ((int) date('i')) / 60;

        $filas = array();
        foreach ($mes['dias'] as $d) {
            $barras = array();
            $texto = '';
            if ($d['feriado']) {
                $texto = 'Feriado · ' . $d['feriado'];
            }
            $novJornada = null;
            $novParcial = null;
            foreach ($d['novedades'] as $n) {
                if ($n['jornada']) {
                    $novJornada = $n;
                } else {
                    $novParcial = $n;
                }
            }
            if ($d['entrada'] !== null) {
                $fin = $d['salida'];
                $clase = $novParcial ? $novParcial['clase'] : '';
                $rotulo = $novParcial ? $novParcial['descripcion'] : '';
                if ($fin === null) {
                    if ($d['hoy']) {
                        $fin = max($d['entrada'] + 0.25, $ahora);
                        $clase = 'live';
                    } else {
                        $fin = $d['entrada'] + 0.6;
                        $clase = 'inc';
                        $rotulo = 'Sin salida';
                    }
                }
                $barras[] = $this->barra($d['entrada'], $fin, $clase, $rotulo);
            } elseif ($novJornada) {
                $barras[] = $this->barra($oficialDesde, $oficialHasta, $novJornada['clase'], $novJornada['codigo'] . ' · ' . $novJornada['descripcion']);
            } elseif ($d['salida'] !== null) {
                $barras[] = $this->barra($d['salida'] - 0.6, $d['salida'], 'inc', 'Sin entrada');
            }
            $total = '';
            if ($d['horas'] !== null) {
                $total = Ag::horas($d['horas']);
            } elseif ($d['hoy'] && $d['entrada'] !== null) {
                $total = 'en curso';
            }
            $filas[] = array(
                'dia' => str_pad($d['dia'], 2, '0', STR_PAD_LEFT),
                'diaCorto' => $d['diaCorto'],
                'clases' => trim(($d['finde'] ? 'we ' : '') . ($d['feriado'] ? 'fer' : '')),
                'texto' => $texto,
                'barras' => $barras,
                'total' => $total,
            );
        }

        $horas = array();
        for ($h = 0; $h < 24; $h++) {
            $horas[] = str_pad($h, 2, '0', STR_PAD_LEFT);
        }

        return array_merge($this->cabeceraAsistencia($e, $periodo), array(
            'filas' => $filas,
            'horasEje' => $horas,
            'oficial' => array('left' => $this->pct($oficialDesde), 'width' => $this->pct($oficialHasta - $oficialDesde)),
            'resumen' => array(
                'horario' => $this->horarioTexto($e),
                'categoria' => $e['categoria_horaria'],
                'laborables' => $mes['resumen']['laborables'],
                'trabajados' => $mes['resumen']['trabajados'],
                'promedio' => Ag::horas($mes['resumen']['promedio']),
            ),
        ));
    }

    protected function barra($desde, $hasta, $clase, $rotulo)
    {
        $desde = max(0, $desde);
        $hasta = min(24, $hasta);
        return array('left' => $this->pct($desde), 'width' => $this->pct(max(0.1, $hasta - $desde)), 'clase' => $clase, 'rotulo' => $rotulo);
    }

    protected function pct($horas)
    {
        return number_format($horas / 24 * 100, 3, '.', '') . '%';
    }

    public function accesos($empleadoId, $periodo)
    {
        $e = $this->repo->empleado($empleadoId);
        $mes = $this->diasDelMes($empleadoId, $periodo, $e);
        $filas = array();
        foreach (array_reverse($mes['dias']) as $d) {
            if ($d['entrada'] === null && $d['salida'] === null) {
                continue;
            }
            if ($d['hoy'] && $d['salida'] === null) {
                $estado = array('info', 'En curso');
            } elseif ($d['inconsistente']) {
                $estado = array('danger', 'Falta fichada de ' . ($d['entrada'] === null ? 'entrada' : 'salida'));
            } elseif (count($d['novedades'])) {
                $n = $d['novedades'][0];
                $estado = array($n['estado'] == 'pendiente' ? 'warn' : 'info', $n['descripcion']);
            } else {
                $estado = array('ok', 'Completa');
            }
            $reloj = $d['relojE'] ? $d['relojE'] : $d['relojS'];
            if ($d['relojS'] && $d['relojS'] != $d['relojE'] && $d['relojE']) {
                $reloj .= ' / ' . $d['relojS'];
            }
            $filas[] = array(
                'diaCorto' => $d['diaCorto'],
                'fecha' => $d['fechaTexto'],
                'entrada' => $d['entrada'] !== null ? Ag::horas($d['entrada']) : '—',
                'salida' => $d['salida'] !== null ? Ag::horas($d['salida']) : '—',
                'reloj' => $reloj,
                'total' => $d['horas'] !== null ? Ag::horas($d['horas']) : '',
                'estado' => $estado,
            );
        }
        return array_merge($this->cabeceraAsistencia($e, $periodo), array('filas' => $filas));
    }

    public function licencias($empleadoId)
    {
        $e = $this->repo->empleado($empleadoId);
        $anioActual = (int) date('Y');
        $tarjetas = array();
        $tabla = array();
        foreach ($this->repo->saldosLicencia($empleadoId) as $s) {
            $v = $this->saldo($s);
            if ($s['destacado'] && count($tarjetas) < 3) {
                $tarjetas[] = $v;
            }
            if ((int) $s['anio'] == $anioActual) {
                $tabla[] = $v;
            }
        }
        return array_merge($this->cabeceraAsistencia($e, substr(Ag::hoy(), 0, 7)), array(
            'tarjetas' => $tarjetas,
            'tabla' => $tabla,
            'anioActual' => $anioActual,
        ));
    }

    protected function saldo($s)
    {
        $otorgado = (float) $s['otorgado'];
        $usado = (float) $s['usado'];
        $disp = max(0, $otorgado - $usado);
        $pct = $otorgado > 0 ? min(100, round($usado / $otorgado * 100)) : 0;
        $unidad = $s['unidad'] == 'horas' ? 'horas' : 'días';
        $hoy = Ag::hoy();
        if ($s['disponible_desde'] && $s['disponible_desde'] > $hoy) {
            $nota = 'Disponible desde el ' . Ag::fecha($s['disponible_desde']);
            $grande = $this->num($otorgado);
            $leyenda = $unidad . ' a otorgar';
        } else {
            $nota = 'Usó ' . $this->num($usado) . ' de ' . $this->num($otorgado) . ($s['vence'] ? ' · vence el ' . Ag::fecha($s['vence']) : '');
            $grande = $this->num($disp);
            $leyenda = $unidad . ' disponibles';
        }
        return array(
            'codigo' => $s['articulo_codigo'],
            'titulo' => $s['descripcion'] . ' ' . $s['anio'],
            'descripcion' => $s['descripcion'],
            'grande' => $grande,
            'leyenda' => $leyenda,
            'nota' => $nota,
            'pct' => $pct,
            'alerta' => $pct > 60,
            'usado' => $this->num($usado) . ' ' . $unidad,
            'disponible' => $this->num($disp) . ' ' . $unidad,
        );
    }

    protected function num($n)
    {
        return ((float) $n == (int) $n) ? (string) (int) $n : str_replace('.', ',', (string) $n);
    }

    protected function cabeceraAsistencia($e, $periodo)
    {
        $ts = Ag::ts($periodo . '-01');
        return array(
            'periodo' => $periodo,
            'periodoTexto' => ucfirst(Ag::nombreMes(date('n', $ts))) . ' ' . date('Y', $ts),
            'anterior' => Ag::moverPeriodo($periodo, -1),
            'siguiente' => Ag::moverPeriodo($periodo, 1),
            'esMesActual' => $periodo == substr(Ag::hoy(), 0, 7),
            'horario' => $this->horarioTexto($e),
        );
    }

    protected function horarioTexto($e)
    {
        if (!$e['horario_desde'] || !$e['horario_hasta']) {
            return '';
        }
        return substr($e['horario_desde'], 0, 5) . ' a ' . substr($e['horario_hasta'], 0, 5);
    }

    /* ==================================================================
     * Carrera y contrato
     * ================================================================== */

    protected function creditos($e)
    {
        $actual = (int) $e['creditos_actuales'];
        $req = (int) $e['creditos_requeridos'];
        return array(
            'actual' => $actual,
            'requeridos' => $req,
            'faltan' => max(0, $req - $actual),
            'pct' => $req > 0 ? min(100, round($actual / $req * 100)) : 0,
        );
    }

    public function carrera($empleadoId)
    {
        $e = $this->repo->empleado($empleadoId);
        $cap = array();
        $estados = array('aprobado' => array('ok', 'Aprobado'), 'en_curso' => array('info', 'En curso'), 'desaprobado' => array('danger', 'Desaprobado'));
        foreach ($this->repo->capacitaciones($empleadoId) as $c) {
            $est = isset($estados[$c['estado']]) ? $estados[$c['estado']] : $estados['en_curso'];
            $cap[] = array('actividad' => $c['actividad'], 'organismo' => $c['organismo'],
                'fin' => $c['fecha_fin'] ? Ag::fecha($c['fecha_fin']) : 'En curso', 'creditos' => $c['creditos'],
                'tono' => $est[0], 'estado' => $est[1]);
        }
        $evals = array();
        foreach ($this->repo->evaluaciones($empleadoId) as $ev) {
            $marcas = array();
            for ($i = 1; $i <= 5; $i++) {
                $marcas[] = $i <= (int) $ev['puntaje'];
            }
            $evals[] = array('anio' => $ev['anio'], 'calificacion' => $ev['calificacion'], 'marcas' => $marcas, 'puntaje' => (int) $ev['puntaje']);
        }
        $hitos = array();
        foreach ($this->repo->carrera($empleadoId) as $h) {
            $fechas = $h['fecha_hasta'] ? Ag::fecha($h['fecha_desde']) . ' – ' . Ag::fecha($h['fecha_hasta']) : 'Desde ' . Ag::fecha($h['fecha_desde']);
            $hitos[] = array('descripcion' => $h['descripcion'], 'detalle' => $fechas . ($h['acto'] ? ' · ' . $h['acto'] : ''), 'actual' => !$h['fecha_hasta']);
        }
        return array(
            'resumen' => array(
                array('Agrupamiento', $e['agrupamiento']),
                array('Nivel', $e['nivel']),
                array('Grado', $e['grado']),
                array('Tramo', $e['tramo']),
                array('Créditos', $e['creditos_actuales'] . ' / ' . $e['creditos_requeridos']),
            ),
            'capacitaciones' => $cap,
            'evaluaciones' => $evals,
            'hitos' => $hitos,
            'creditos' => $this->creditos($e),
        );
    }

    public function contrato($empleadoId)
    {
        $e = $this->repo->empleado($empleadoId);
        return array(
            'revista' => array(
                array('Modalidad', $e['modalidad'], false),
                array('Escalafón', $e['escalafon'], false),
                array('Agrupamiento', $e['agrupamiento'], false),
                array('Nivel y grado', $e['nivel'] . ' · ' . $e['grado'], false),
                array('Fecha de ingreso', Ag::fecha($e['fecha_ingreso']), true),
                array('Antigüedad', $this->antiguedad($e['fecha_ingreso']), false),
            ),
            'lugar' => array(
                array('Dependencia', $e['dependencia_nombre'], false),
                array('Código', $e['dependencia_codigo'], true),
                array('Horario oficial', $this->horarioTexto($e), true),
                array('Categoría horaria', $e['categoria_horaria'], false),
                array('Sede', $e['sede'], false),
            ),
        );
    }

    protected function antiguedad($ingreso)
    {
        if (!$ingreso) {
            return '';
        }
        $hoy = Ag::hoy();
        $meses = ((int) substr($hoy, 0, 4) - (int) substr($ingreso, 0, 4)) * 12 + ((int) substr($hoy, 5, 2) - (int) substr($ingreso, 5, 2));
        if ((int) substr($hoy, 8, 2) < (int) substr($ingreso, 8, 2)) {
            $meses--;
        }
        $a = floor($meses / 12);
        $m = $meses % 12;
        return $a . ($a == 1 ? ' año' : ' años') . ($m ? ', ' . $m . ($m == 1 ? ' mes' : ' meses') : '');
    }

    /* ==================================================================
     * Mensajes y solicitudes
     * ================================================================== */

    public function mensajes($empleadoId)
    {
        $lista = array();
        foreach ($this->repo->mensajes($empleadoId) as $m) {
            $resumen = trim(preg_replace('/\s+/', ' ', strip_tags($m['cuerpo'])));
            if (strlen($resumen) > 140) {
                $resumen = substr($resumen, 0, 140);
                // No cortar un carácter UTF-8 por la mitad.
                $resumen = preg_replace('/[\x80-\xBF]+$/', '', $resumen);
                $resumen = preg_replace('/[\xC0-\xFF]$/', '', $resumen) . '…';
            }
            $lista[] = array('id' => $m['id'], 'remitente' => $m['remitente'], 'iniciales' => Ag::iniciales($m['remitente'], ''),
                'asunto' => $m['asunto'], 'resumen' => $resumen, 'fecha' => substr(Ag::fecha($m['fecha']), 0, 5), 'leido' => (bool) $m['leido']);
        }
        return array('mensajes' => $lista);
    }

    public function mensaje($empleadoId, $mensajeId)
    {
        $m = $this->repo->mensaje($empleadoId, $mensajeId);
        if (!$m) {
            return null;
        }
        if (!$m['leido']) {
            $this->repo->marcarLeido($empleadoId, $mensajeId);
        }
        return array('mensaje' => array(
            'remitente' => $m['remitente'],
            'asunto' => $m['asunto'],
            'parrafos' => preg_split('/\r?\n\s*\r?\n/', trim($m['cuerpo'])),
            'fecha' => Ag::fecha($m['fecha']) . ' ' . substr($m['fecha'], 11, 5),
        ));
    }

    public function tiposSolicitud()
    {
        return array(
            'licencia' => array('Solicitar licencia', 'Elija el artículo y las fechas. Su superior recibirá el pedido para aprobarlo.'),
            'reclamo' => array('Reclamar una inconsistencia', 'Indique el día y qué ocurrió (por ejemplo, una fichada que no se registró).'),
            'datos' => array('Solicitar actualización de datos', 'Indique qué dato hay que corregir y el valor correcto. Recursos Humanos puede pedirle documentación.'),
        );
    }

    public function formularioSolicitud($tipo, $fecha)
    {
        $tipos = $this->tiposSolicitud();
        return array(
            'tipo' => $tipo,
            'tituloForm' => $tipos[$tipo][0],
            'ayuda' => $tipos[$tipo][1],
            'articulos' => $tipo == 'licencia' ? $this->repo->articulosSolicitables() : array(),
            'fechaSugerida' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fecha) ? $fecha : '',
        );
    }

    /** Valida y guarda una solicitud. Devuelve un array de errores (vacío si se guardó). */
    public function guardarSolicitud($empleadoId, $tipo, $entrada)
    {
        $errores = array();
        $articulo = null;
        $desde = null;
        $hasta = null;
        $detalle = trim(isset($entrada['detalle']) ? (string) $entrada['detalle'] : '');

        if ($tipo == 'licencia') {
            $articulo = isset($entrada['articulo']) ? (string) $entrada['articulo'] : '';
            if ($articulo === '' || !$this->repo->articuloExiste($articulo)) {
                $errores['articulo'] = 'Elija un artículo de la lista.';
            }
        }
        if ($tipo == 'licencia' || $tipo == 'reclamo') {
            $desde = isset($entrada['desde']) ? (string) $entrada['desde'] : '';
            if (!$this->fechaValida($desde)) {
                $errores['desde'] = 'Ingrese una fecha válida.';
            }
            $hasta = isset($entrada['hasta']) && $entrada['hasta'] !== '' ? (string) $entrada['hasta'] : $desde;
            if (isset($errores['desde']) && $hasta === $desde) {
                $hasta = null;
            } elseif (!$this->fechaValida($hasta)) {
                $errores['hasta'] = 'Ingrese una fecha válida.';
            } elseif (!isset($errores['desde']) && $hasta < $desde) {
                $errores['hasta'] = 'La fecha final no puede ser anterior a la inicial.';
            }
        }
        if ($detalle === '' && $tipo != 'licencia') {
            $errores['detalle'] = 'Cuéntenos qué necesita.';
        }
        if (strlen($detalle) > 2000) {
            $errores['detalle'] = 'El texto es demasiado largo (máximo 2000 caracteres).';
        }
        if (count($errores)) {
            return $errores;
        }
        $this->repo->crearSolicitud($empleadoId, $tipo, $articulo, $desde, $hasta, $detalle, date('Y-m-d H:i:s'));
        return array();
    }

    protected function fechaValida($ymd)
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) {
            return false;
        }
        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }
}
