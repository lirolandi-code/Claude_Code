<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · Autoconsulta</title>
    <link rel="shortcut icon" href="{{ asset('assets/favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Public+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap">
    <link href="{{ asset('assets/css/newrol.css') . '?v=' . date('dmYhis') }}" rel="stylesheet">
    <script>
        try { var nrT = localStorage.getItem('nr_tema'); if (nrT) { document.documentElement.setAttribute('data-theme', nrT); } } catch (e) {}
    </script>
</head>
<body>
@include('partials.nr-iconos')

<section class="login">
    <div class="login-art">
        <span class="nav-brand"><span class="logo">A</span><span><b>AUTOCONSULTA</b><small>RECURSOS HUMANOS</small></span></span>
        <h1>Su información laboral, en un solo lugar</h1>
        <p>Consulte sus recibos de sueldo, su asistencia y licencias, y su información personal y de carrera.</p>
        <div class="mods"><span>Recibos de sueldo</span><span>Formulario 1359</span><span>Calendario de asistencia</span><span>Bandas horarias</span><span>Licencias</span><span>Capacitación</span></div>
        <div class="foot"><a href="{{ asset('assets/video/autoconsulta.mp4') }}" download="autoconsulta.mp4" style="color:#9fb7d6">Descargar el video explicativo</a></div>
    </div>

    <div class="login-form">
        <form method="post" action="{{ route('confirm_login') }}">
            @csrf
            <div class="org" style="border:0;padding:0"><img src="{{ asset('assets/images/LOGO-MECON_Dos-lineas-AZUL.png') }}" alt="Logo del organismo" style="max-height:44px;max-width:100%"></div>

            <div>
                <h2>Ingresar</h2>
                <p class="muted" style="margin:6px 0 0">Use su usuario y contraseña de la Autoconsulta.</p>
            </div>

            @if ( $message || $error )
            <div class="flash {{ $message ? 'ok' : 'danger' }} login-error" role="alert">
                <svg class="i"><use xlink:href="#i-{{ $message ? 'check' : 'alert' }}"></use></svg>
                <span>@if ( $message ){{ Vista::e($message) }}@else{{ Vista::e($error) }}@endif</span>
            </div>
            @endif

            <div class="field">
                <label for="username">Usuario</label>
                <div class="input-icon"><svg class="i"><use xlink:href="#i-user"></use></svg>
                    <input class="input" type="text" id="username" name="username" autocomplete="username" required autofocus>
                </div>
            </div>
            <div class="field">
                <label for="password">Contraseña</label>
                <div class="input-icon"><svg class="i"><use xlink:href="#i-lock"></use></svg>
                    <input class="input" type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
            </div>
            <button class="nbtn primary block" type="submit" style="height:44px">Ingresar</button>
        </form>
    </div>
</section>
</body>
</html>
