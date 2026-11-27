<?php

/**
 * Sitio de autogestión del empleado.
 *
 * Capa fina: valida la sesión, lee la entrada y arma la vista. La lógica está en
 * app/autogestion/AutogestionServicio.php y las consultas en AutogestionRepositorio.php.
 *
 * Compatible con PHP 5.1.6 (se escribió sin sintaxis moderna, así no depende del conversor de Baradur).
 */
class AutogestionController extends Controller
{
    /** @return AutogestionServicio */
    protected function servicio()
    {
        return new AutogestionServicio();
    }

    /** Arma una pantalla con los datos comunes + los propios. Si no hay sesión, va al ingreso. */
    protected function pantalla($vista, $seccion, $titulo, $migas, $datos)
    {
        $comun = $this->servicio()->comun(Ag::empleadoId(), $seccion, $titulo, $migas);
        if ($comun === null) {
            Ag::cerrarSesion();
            return redirect(Ag::url('login'));
        }
        return view('autogestion.' . $vista, array_merge($comun, $datos));
    }

    protected function sinSesion()
    {
        return Ag::empleadoId() == 0;
    }

    protected function alIngreso()
    {
        return redirect(Ag::url('login'));
    }

    protected function entrada($clave, $porDefecto = '')
    {
        if (isset($_POST[$clave])) {
            return is_array($_POST[$clave]) ? $porDefecto : $_POST[$clave];
        }
        if (isset($_GET[$clave])) {
            return is_array($_GET[$clave]) ? $porDefecto : $_GET[$clave];
        }
        return $porDefecto;
    }

    /* ---------------- ingreso ---------------- */

    public function login()
    {
        if (!$this->sinSesion()) {
            return redirect(Ag::url('inicio'));
        }
        return view('autogestion.login', array('error' => Ag::tomarFlash(), 'usuario' => ''));
    }

    public function ingresar()
    {
        $usuario = trim((string) $this->entrada('usuario'));
        $clave = (string) $this->entrada('clave');

        $espera = Ag::esperaIngreso();
        if ($espera > 0) {
            Ag::flash('danger', 'Hubo demasiados intentos fallidos. Espere ' . ceil($espera / 60) . ' minutos e intente de nuevo.');
            return redirect(Ag::url('login'));
        }

        $repo = new AutogestionRepositorio();
        $empleado = $repo->empleadoParaIngreso($usuario);
        if (!$empleado || !Ag::verificarClave($clave, $empleado['clave'])) {
            Ag::registrarFallo();
            Ag::flash('danger', 'El CUIL, el legajo o la contraseña no son correctos. Revíselos e intente de nuevo.');
            return redirect(Ag::url('login'));
        }

        Ag::limpiarFallos();
        Ag::iniciarSesion($empleado['id']);
        return redirect(Ag::url('inicio'));
    }

    public function salir()
    {
        Ag::cerrarSesion();
        Ag::flash('ok', 'Cerró su sesión.');
        return redirect(Ag::url('login'));
    }

    /* ---------------- pantallas ---------------- */

    public function inicio()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->inicio(Ag::empleadoId());
        return $this->pantalla('inicio', 'inicio', 'Inicio', array('Inicio'), $datos);
    }

    public function datos()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->datos(Ag::empleadoId());
        return $this->pantalla('datos', 'datos', 'Datos personales', array('Datos personales'), $datos);
    }

    public function documentacion()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->documentacion(Ag::empleadoId());
        return $this->pantalla('documentacion', 'datos', 'Documentación requerida', array('Datos personales', 'Documentación requerida'), $datos);
    }

    public function subirDocumento($id)
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $archivo = isset($_FILES['archivo']) ? $_FILES['archivo'] : null;
        $error = $this->servicio()->subirDocumento(Ag::empleadoId(), (int) $id, $archivo);
        if ($error === '') {
            Ag::flash('ok', 'Recibimos el archivo. Recursos Humanos lo va a revisar.');
        } else {
            Ag::flash('danger', $error);
        }
        return redirect(Ag::url('documentacion'));
    }

    public function haberes()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->haberes(Ag::empleadoId(), $this->entrada('anio', date('Y')));
        return $this->pantalla('haberes', 'haberes', 'Haberes', array('Haberes'), $datos);
    }

    public function recibo($id)
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $repo = new AutogestionRepositorio();
        $r = $repo->recibo(Ag::empleadoId(), (int) $id);
        $ruta = $r ? Ag::rutaSegura($r['archivo']) : null;
        if (!$ruta) {
            Ag::flash('danger', 'Ese recibo todavía no está disponible para descargar.');
            return redirect(Ag::url('haberes'));
        }
        Ag::enviarArchivo($ruta, 'recibo_' . $r['descripcion'] . '.pdf');
    }

    public function formulario1359($anio)
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $repo = new AutogestionRepositorio();
        $f = $repo->formulario1359(Ag::empleadoId(), (int) $anio);
        $ruta = $f ? Ag::rutaSegura($f['archivo']) : null;
        if (!$ruta) {
            Ag::flash('danger', 'El Formulario 1359 de ' . (int) $anio . ' todavía no está disponible.');
            return redirect(Ag::url('haberes?anio=' . (int) $anio));
        }
        Ag::enviarArchivo($ruta, 'formulario_1359_' . (int) $anio . '.pdf');
    }

    public function asistencia()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->calendario(Ag::empleadoId(), Ag::periodo($this->entrada('mes')));
        $datos['pestana'] = 'calendario';
        return $this->pantalla('asistencia.calendario', 'asistencia', 'Asistencia', array('Asistencia', 'Calendario'), $datos);
    }

    public function bandas()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->bandas(Ag::empleadoId(), Ag::periodo($this->entrada('mes')));
        $datos['pestana'] = 'bandas';
        return $this->pantalla('asistencia.bandas', 'asistencia', 'Bandas horarias', array('Asistencia', 'Bandas horarias'), $datos);
    }

    public function licencias()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->licencias(Ag::empleadoId());
        $datos['pestana'] = 'licencias';
        return $this->pantalla('asistencia.licencias', 'asistencia', 'Licencias', array('Asistencia', 'Licencias'), $datos);
    }

    public function accesos()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->accesos(Ag::empleadoId(), Ag::periodo($this->entrada('mes')));
        $datos['pestana'] = 'accesos';
        return $this->pantalla('asistencia.accesos', 'asistencia', 'Accesos', array('Asistencia', 'Accesos'), $datos);
    }

    public function carrera()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->carrera(Ag::empleadoId());
        return $this->pantalla('carrera', 'carrera', 'Carrera', array('Carrera'), $datos);
    }

    public function contrato()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->contrato(Ag::empleadoId());
        return $this->pantalla('contrato', 'contrato', 'Contrato', array('Contrato'), $datos);
    }

    public function mensajes()
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->mensajes(Ag::empleadoId());
        return $this->pantalla('mensajes', 'mensajes', 'Mensajes', array('Mensajes'), $datos);
    }

    public function mensaje($id)
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $datos = $this->servicio()->mensaje(Ag::empleadoId(), (int) $id);
        if ($datos === null) {
            return redirect(Ag::url('mensajes'));
        }
        return $this->pantalla('mensaje', 'mensajes', 'Mensaje', array('Mensajes', 'Mensaje'), $datos);
    }

    public function solicitud($tipo)
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $servicio = $this->servicio();
        $tipos = $servicio->tiposSolicitud();
        if (!isset($tipos[$tipo])) {
            return redirect(Ag::url('inicio'));
        }
        $datos = $servicio->formularioSolicitud($tipo, $this->entrada('fecha'));
        $datos['errores'] = array();
        $datos['valores'] = array('articulo' => '', 'desde' => $datos['fechaSugerida'], 'hasta' => '', 'detalle' => '');
        $seccion = $tipo == 'datos' ? 'datos' : 'asistencia';
        return $this->pantalla('solicitud', $seccion, $datos['tituloForm'], array($datos['tituloForm']), $datos);
    }

    public function enviarSolicitud($tipo)
    {
        if ($this->sinSesion()) {
            return $this->alIngreso();
        }
        $servicio = $this->servicio();
        $tipos = $servicio->tiposSolicitud();
        if (!isset($tipos[$tipo])) {
            return redirect(Ag::url('inicio'));
        }
        $valores = array(
            'articulo' => (string) $this->entrada('articulo'),
            'desde' => (string) $this->entrada('desde'),
            'hasta' => (string) $this->entrada('hasta'),
            'detalle' => (string) $this->entrada('detalle'),
        );
        $errores = $servicio->guardarSolicitud(Ag::empleadoId(), $tipo, $valores);
        if (!count($errores)) {
            Ag::flash('ok', 'Enviamos su pedido. Le avisaremos por Mensajes cuando tenga respuesta.');
            return redirect(Ag::url($tipo == 'datos' ? 'datos' : 'asistencia'));
        }
        $datos = $servicio->formularioSolicitud($tipo, '');
        $datos['errores'] = $errores;
        $datos['valores'] = $valores;
        $seccion = $tipo == 'datos' ? 'datos' : 'asistencia';
        return $this->pantalla('solicitud', $seccion, $datos['tituloForm'], array($datos['tituloForm']), $datos);
    }
}
