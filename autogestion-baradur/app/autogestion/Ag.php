<?php

/**
 * Autogestión del empleado - utilidades comunes.
 *
 * Compatible con PHP 5.1.6: sin closures, sin namespaces, sin sintaxis [] para arrays,
 * sin json_*, sin DateTime y sin el operador ?:.
 *
 * IMPORTANTE (Baradur): en las vistas Blade de Baradur {{ }} NO escapa el HTML.
 * Por eso todo dato variable se imprime como {{ Ag::e($valor) }}.
 */
class Ag
{
    const SESION = 'ag_autogestion';
    const ZONA_HORARIA = 'America/Argentina/Buenos_Aires';
    const MAX_INTENTOS = 5;
    const BLOQUEO_SEGUNDOS = 300;

    /* ------------------------------------------------------------------
     * Escape y formatos
     * ------------------------------------------------------------------ */

    /** Escapa un valor para imprimirlo en HTML (texto o atributo). */
    public static function e($valor)
    {
        if ($valor === null) {
            return '';
        }
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }

    /** 'YYYY-MM-DD' (o 'YYYY-MM-DD HH:MM:SS') -> 'DD/MM/YYYY'. */
    public static function fecha($ymd)
    {
        if (!$ymd || substr($ymd, 0, 4) == '0000') {
            return '';
        }
        return substr($ymd, 8, 2) . '/' . substr($ymd, 5, 2) . '/' . substr($ymd, 0, 4);
    }

    /** Horas decimales (7.5) -> '07:30'. Devuelve '—' si es null. */
    public static function horas($decimal)
    {
        if ($decimal === null) {
            return '—';
        }
        $minutos = (int) round($decimal * 60);
        return str_pad(floor($minutos / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutos % 60, 2, '0', STR_PAD_LEFT);
    }

    /** 'HH:MM:SS' -> horas decimales. */
    public static function aDecimal($hhmmss)
    {
        if (!$hhmmss) {
            return null;
        }
        $p = explode(':', $hhmmss);
        return (int) $p[0] + ((int) $p[1]) / 60 + (isset($p[2]) ? ((int) $p[2]) / 3600 : 0);
    }

    /** 1842350.12 -> '1.842.350,12' */
    public static function dinero($numero)
    {
        return number_format((float) $numero, 2, ',', '.');
    }

    /** Número entero con separador de miles: 25817734 -> '25.817.734' */
    public static function miles($numero)
    {
        return number_format((float) $numero, 0, ',', '.');
    }

    /** Primera letra (segura para UTF-8) en mayúscula. */
    public static function inicial($texto)
    {
        $texto = trim((string) $texto);
        if ($texto === '') {
            return '';
        }
        if (preg_match('/^./u', $texto, $m)) {
            return strtoupper($m[0]);
        }
        return strtoupper(substr($texto, 0, 1));
    }

    public static function iniciales($nombre, $apellido)
    {
        return self::inicial($nombre) . self::inicial($apellido);
    }

    public static function nombreMes($mes)
    {
        $nombres = array(1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
            'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
        return $nombres[(int) $mes];
    }

    public static function diaCorto($diaSemana)
    {
        $dias = array('dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb');
        return $dias[(int) $diaSemana];
    }

    public static function diaLargo($diaSemana)
    {
        $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
        return $dias[(int) $diaSemana];
    }

    /** Fecha de hoy en formato 'YYYY-MM-DD' en la zona horaria del organismo. */
    public static function hoy()
    {
        self::zonaHoraria();
        return date('Y-m-d');
    }

    public static function zonaHoraria()
    {
        if (function_exists('date_default_timezone_set')) {
            @date_default_timezone_set(self::ZONA_HORARIA);
        }
    }

    /** 'YYYY-MM-DD' -> timestamp (mediodía, para evitar saltos por horario de verano). */
    public static function ts($ymd)
    {
        return mktime(12, 0, 0, (int) substr($ymd, 5, 2), (int) substr($ymd, 8, 2), (int) substr($ymd, 0, 4));
    }

    /** Valida un período 'YYYY-MM'; si no es válido devuelve el mes actual. */
    public static function periodo($valor)
    {
        if (is_string($valor) && preg_match('/^(\d{4})-(\d{2})$/', $valor, $m)) {
            $anio = (int) $m[1];
            $mes = (int) $m[2];
            if ($anio >= 1990 && $anio <= 2100 && $mes >= 1 && $mes <= 12) {
                return sprintf('%04d-%02d', $anio, $mes);
            }
        }
        return substr(self::hoy(), 0, 7);
    }

    /** Suma meses a un período 'YYYY-MM'. */
    public static function moverPeriodo($periodo, $meses)
    {
        $ts = mktime(12, 0, 0, (int) substr($periodo, 5, 2) + $meses, 1, (int) substr($periodo, 0, 4));
        return date('Y-m', $ts);
    }

    /* ------------------------------------------------------------------
     * Sesión del empleado (independiente del Auth de usuarios del sistema)
     * ------------------------------------------------------------------ */

    public static function empleadoId()
    {
        if (isset($_SESSION[self::SESION]['empleado_id'])) {
            return (int) $_SESSION[self::SESION]['empleado_id'];
        }
        return 0;
    }

    public static function iniciarSesion($empleadoId)
    {
        // Evita la fijación de sesión.
        if (function_exists('session_regenerate_id')) {
            @session_regenerate_id(true);
        }
        $_SESSION[self::SESION] = array('empleado_id' => (int) $empleadoId, 'inicio' => time());
    }

    public static function cerrarSesion()
    {
        unset($_SESSION[self::SESION]);
        if (function_exists('session_regenerate_id')) {
            @session_regenerate_id(true);
        }
    }

    /** Límite simple de intentos de ingreso por sesión. Devuelve segundos de espera (0 si puede intentar). */
    public static function esperaIngreso()
    {
        if (!isset($_SESSION['ag_intentos'])) {
            return 0;
        }
        $i = $_SESSION['ag_intentos'];
        if ($i['cantidad'] >= self::MAX_INTENTOS) {
            $resta = $i['desde'] + self::BLOQUEO_SEGUNDOS - time();
            if ($resta > 0) {
                return $resta;
            }
            unset($_SESSION['ag_intentos']);
        }
        return 0;
    }

    public static function registrarFallo()
    {
        if (!isset($_SESSION['ag_intentos'])) {
            $_SESSION['ag_intentos'] = array('cantidad' => 0, 'desde' => time());
        }
        $_SESSION['ag_intentos']['cantidad']++;
        $_SESSION['ag_intentos']['desde'] = time();
    }

    public static function limpiarFallos()
    {
        unset($_SESSION['ag_intentos']);
    }

    /**
     * Verifica la contraseña. Usa el Hash del framework (el mismo que Auth) si está disponible.
     * Si su sistema ya valida a los empleados de otra forma, este es el único punto a cambiar.
     */
    public static function verificarClave($clave, $hashGuardado)
    {
        if ($hashGuardado === null || $hashGuardado === '' || $clave === '') {
            return false;
        }
        if (class_exists('Hash')) {
            return Hash::check($clave, $hashGuardado);
        }
        return false;
    }

    /* ------------------------------------------------------------------
     * Mensajes flash propios (no dependen del helper session())
     * ------------------------------------------------------------------ */

    public static function flash($tipo, $texto)
    {
        $_SESSION['ag_flash'] = array('tipo' => $tipo, 'texto' => $texto);
    }

    public static function tomarFlash()
    {
        if (!isset($_SESSION['ag_flash'])) {
            return null;
        }
        $f = $_SESSION['ag_flash'];
        unset($_SESSION['ag_flash']);
        return $f;
    }

    /* ------------------------------------------------------------------
     * URLs y archivos
     * ------------------------------------------------------------------ */

    /** URL absoluta dentro del sitio de autogestión. */
    public static function url($ruta = '')
    {
        $base = rtrim((string) config('app.url'), '/');
        return $base . '/autogestion' . ($ruta === '' ? '' : '/' . ltrim($ruta, '/'));
    }

    /** Carpeta privada donde se guardan recibos y documentos (fuera de public). */
    public static function carpetaArchivos()
    {
        return rtrim(storage_path('app/autogestion'), '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Devuelve la ruta absoluta de un archivo guardado, solo si está dentro de la carpeta privada.
     * Evita que un valor manipulado en la base apunte a otro lugar del servidor (../).
     */
    public static function rutaSegura($relativa)
    {
        $relativa = str_replace('\\', '/', (string) $relativa);
        if ($relativa === '' || strpos($relativa, '..') !== false || substr($relativa, 0, 1) == '/') {
            return null;
        }
        $ruta = self::carpetaArchivos() . $relativa;
        $real = realpath($ruta);
        $base = realpath(self::carpetaArchivos());
        if ($real === false || $base === false || strpos($real, $base) !== 0 || !is_file($real)) {
            return null;
        }
        return $real;
    }

    /** Envía un archivo al navegador y termina la ejecución. */
    public static function enviarArchivo($rutaAbsoluta, $nombreDescarga)
    {
        $nombre = preg_replace('/[^A-Za-z0-9._-]/', '_', $nombreDescarga);
        $ext = strtolower(substr(strrchr($rutaAbsoluta, '.'), 1));
        $tipos = array('pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png');
        $tipo = isset($tipos[$ext]) ? $tipos[$ext] : 'application/octet-stream';

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        header('Content-Type: ' . $tipo);
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Content-Length: ' . filesize($rutaAbsoluta));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($rutaAbsoluta);
        exit;
    }
}
