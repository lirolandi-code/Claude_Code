<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · Autogestión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Public+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/autogestion/css/newrol.css') }}">
    <script>
        try { var t = localStorage.getItem('ag_tema'); if (t) { document.documentElement.setAttribute('data-theme', t); } } catch (e) {}
    </script>
</head>
<body>
@include('autogestion.partials.iconos')

<section class="login">
    <div class="login-art">
        <span class="nav-brand"><span class="logo">N</span><span><b>NEWROL</b><small>AUTOGESTIÓN DEL EMPLEADO</small></span></span>
        <h1>Su información laboral, en un solo lugar</h1>
        <p>Consulte y descargue sus recibos de sueldo, revise su asistencia y licencias, y siga su carrera y capacitación desde la computadora o el celular.</p>
        <div class="mods"><span>Recibos de sueldo</span><span>Formulario 1359</span><span>Asistencia y fichadas</span><span>Vacaciones y licencias</span><span>Capacitación</span><span>Evaluaciones</span></div>
        <div class="foot"><span>Conexión segura</span></div>
    </div>

    <div class="login-form">
        <form method="post" action="{{ Ag::url('login') }}" autocomplete="on">
            @csrf
            {{-- Reemplace este bloque por el logo del organismo. --}}
            <div class="org"><span class="ph"><svg class="i"><use xlink:href="#i-home"></use></svg></span><span>Logo del organismo</span></div>

            <div>
                <h2>Ingresar</h2>
                <p class="muted" style="margin:6px 0 0">Use su CUIL o número de legajo y su contraseña.</p>
            </div>

            @if($error)
            <div class="flash {{ $error['tipo'] == 'ok' ? 'ok' : 'danger' }}" role="alert">
                <svg class="i"><use xlink:href="#i-{{ $error['tipo'] == 'ok' ? 'check' : 'alert' }}"></use></svg>
                <span>{{ Ag::e($error['texto']) }}</span>
            </div>
            @endif

            <div class="field">
                <label for="usuario">CUIL o legajo</label>
                <div class="input-icon"><svg class="i"><use xlink:href="#i-user"></use></svg>
                    <input class="input mono" id="usuario" name="usuario" value="{{ Ag::e($usuario) }}" autocomplete="username" inputmode="numeric" required autofocus>
                </div>
            </div>
            <div class="field">
                <label for="clave">Contraseña</label>
                <div class="input-icon"><svg class="i"><use xlink:href="#i-lock"></use></svg>
                    <input class="input" id="clave" name="clave" type="password" autocomplete="current-password" required>
                </div>
            </div>
            <button class="btn primary block" type="submit" style="height:44px">Ingresar</button>
            <p class="help" style="margin:0;text-align:center">¿Olvidó su contraseña? Pídale a Recursos Humanos que la restablezca.</p>
        </form>
    </div>
</section>
</body>
</html>
