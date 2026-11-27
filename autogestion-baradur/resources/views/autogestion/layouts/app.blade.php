<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ Ag::e($titulo) }} · Autogestión</title>
    {{-- Tipografías: si el servidor no tiene salida a internet, se usan las fuentes del sistema. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Public+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/autogestion/css/newrol.css') }}">
    <script>
        try { var t = localStorage.getItem('ag_tema'); if (t) { document.documentElement.setAttribute('data-theme', t); } } catch (e) {}
    </script>
</head>
<body class="ag-oculto">
@include('autogestion.partials.iconos')

<div id="app" class="app">
    @include('autogestion.partials.nav')
    <div class="main">
        @include('autogestion.partials.topbar')
        <main class="content">
            @include('autogestion.partials.flash')
            @yield('contenido')
        </main>
    </div>
</div>

<script src="{{ asset('assets/autogestion/js/newrol.js') }}"></script>
</body>
</html>
