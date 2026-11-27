<?php

/**
 * Ayudas para las vistas de la Autoconsulta v4.
 *
 * En Baradur {{ }} imprime sin escapar el HTML. Las vistas nuevas usan
 * {{ Vista::e($valor) }} para que un dato con <, > o comillas no rompa la página
 * ni permita inyectar código.
 *
 * Se usa el juego de caracteres ISO-8859-1 a propósito: escapa solo & < > " ' y deja
 * intactos los acentos, tanto si la base está en latin1 como en utf8, y nunca
 * devuelve una cadena vacía (cosa que sí pasa con 'UTF-8' ante datos en latin1).
 * Compatible con PHP 5.1.6.
 */
class Vista
{
    public static function e($valor)
    {
        if ($valor === null) {
            return '';
        }
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'ISO-8859-1');
    }

    /** Iniciales para el avatar cuando no hay foto. */
    public static function iniciales($nombre)
    {
        $partes = preg_split('/[\s,]+/', trim((string) $nombre));
        $txt = '';
        foreach ($partes as $p) {
            if ($p !== '' && strlen($txt) < 2) {
                $txt .= strtoupper(substr($p, 0, 1));
            }
        }
        return $txt;
    }

    /**
     * Sección y pantalla activas, para el menú, las pestañas y la ruta de navegación.
     * Se calcula a partir de la ruta actual y de $subtab/$active (los mismos datos que
     * ya envían los controladores). Devuelve: sec, pag, secTexto, pagTexto.
     */
    public static function estado($subtab, $active)
    {
        $sec = 'personal';
        $pag = 'general';
        if (Route::is('recibos.show')) { $sec = 'haberes'; $pag = ''; }
        elseif (Route::is('asistencia.index')) { $sec = 'asistencia'; $pag = 'calendario'; }
        elseif (Route::is('asistencia.bandasHorarias')) { $sec = 'asistencia'; $pag = 'bandas'; }
        elseif (Route::is('asistencia.licencias')) { $sec = 'asistencia'; $pag = 'licencias'; }
        elseif (Route::is('asistencia.accesos')) { $sec = 'asistencia'; $pag = 'accesos'; }
        elseif (Route::is('cursos.index')) { $sec = 'carrera'; $pag = 'capacitacion'; }
        elseif ($subtab == 'carrera') { $sec = 'carrera'; $pag = ($active == 2 ? 'carrera' : 'evaluaciones'); }
        elseif (Route::is('contrato.index')) { $sec = 'contrato'; $pag = ''; }
        elseif ($subtab == 'personal') { $sec = 'personal'; $pag = 'documentacion'; }

        $secciones = array('personal' => 'Datos personales', 'haberes' => 'Haberes', 'asistencia' => 'Asistencia',
            'carrera' => 'Carrera', 'contrato' => 'Contrato');
        $paginas = array('general' => 'General', 'documentacion' => 'Documentación requerida', 'calendario' => 'Calendario',
            'bandas' => 'Bandas horarias', 'licencias' => 'Licencias', 'accesos' => 'Accesos', 'capacitacion' => 'Capacitación',
            'evaluaciones' => 'Evaluaciones', 'carrera' => 'Carrera administrativa');

        return array(
            'sec' => $sec,
            'pag' => $pag,
            'secTexto' => $secciones[$sec],
            'pagTexto' => isset($paginas[$pag]) ? $paginas[$pag] : '',
        );
    }

    /* ------------------------------------------------------------------
     * Vista gerencial (consulta de un agente desde el tablero)
     * ------------------------------------------------------------------ */

    /** Parámetro ?legajo= para los enlaces gerenciales. Se codifica para que '+', '/' o '=' no se alteren. */
    public static function q($legajoCoded)
    {
        return '?legajo=' . rawurlencode((string) $legajoCoded);
    }

    /** Foto del agente consultado (misma lógica que User::getImageAttribute). */
    public static function fotoLegajo($infoUsuario)
    {
        $foto = $infoUsuario ? $infoUsuario->Fotolegajo()->first() : null;
        if ($foto && $foto->fotos) {
            return asset('assets/docs/' . $foto->fotos->Nombre);
        }
        return asset('assets/images/not-found.png');
    }

    /** Sección y pantalla activas en la vista gerencial. Devuelve: sec, pag, secTexto, pagTexto. */
    public static function estadoGerencial($subtab, $active)
    {
        $sec = 'personal';
        $pag = '';
        if (Route::is('gerencial.recibos.show')) { $sec = 'haberes'; }
        elseif (Route::is('gerencial.asistencia.dashboard')) { $sec = 'asistencia'; $pag = 'resumen'; }
        elseif (Route::is('gerencial.asistencia.index')) { $sec = 'asistencia'; $pag = 'calendario'; }
        elseif (Route::is('gerencial.asistencia.bandasHorarias')) { $sec = 'asistencia'; $pag = 'bandas'; }
        elseif (Route::is('gerencial.asistencia.licencias')) { $sec = 'asistencia'; $pag = 'licencias'; }
        elseif (Route::is('gerencial.asistencia.accesos')) { $sec = 'asistencia'; $pag = 'accesos'; }
        elseif (Route::is('gerencial.cursos.index')) { $sec = 'carrera'; $pag = 'capacitacion'; }
        elseif ($subtab == 'carrera') { $sec = 'carrera'; $pag = ($active == 2 ? 'carrera' : 'evaluaciones'); }
        elseif (Route::is('gerencial.contrato.index')) { $sec = 'contrato'; }

        $secciones = array('personal' => 'Datos personales', 'haberes' => 'Haberes', 'asistencia' => 'Asistencia',
            'carrera' => 'Carrera', 'contrato' => 'Contrato');
        $paginas = array('resumen' => 'Resumen por mes', 'calendario' => 'Calendario', 'bandas' => 'Bandas horarias',
            'licencias' => 'Licencias', 'accesos' => 'Accesos', 'capacitacion' => 'Capacitación',
            'evaluaciones' => 'Evaluaciones', 'carrera' => 'Carrera administrativa');

        return array(
            'sec' => $sec,
            'pag' => $pag,
            'secTexto' => $secciones[$sec],
            'pagTexto' => isset($paginas[$pag]) ? $paginas[$pag] : '',
        );
    }
}
