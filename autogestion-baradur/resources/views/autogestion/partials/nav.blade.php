<aside class="nav" id="nav">
    <a class="nav-brand" href="{{ Ag::url('inicio') }}"><span class="logo">N</span><span><b>NEWROL</b><small>AUTOGESTIÓN</small></span></a>

    <div class="nav-me">
        <span class="avatar">{{ Ag::e($iniciales) }}<i title="Activo"></i></span>
        <b>{{ Ag::e($nombreCorto) }}</b>
        <small>Legajo {{ Ag::e($empleado['legajo']) }}</small>
    </div>

    <div class="nav-group">
        <div class="nav-label">Mi información</div>
        <a class="item @if($seccion == 'inicio') active @endif" href="{{ Ag::url('inicio') }}"><svg class="i"><use xlink:href="#i-home"></use></svg><span>Inicio</span></a>
        <a class="item @if($seccion == 'datos') active @endif" href="{{ Ag::url('datos') }}"><svg class="i"><use xlink:href="#i-user"></use></svg><span>Datos personales</span></a>
        <a class="item @if($seccion == 'haberes') active @endif" href="{{ Ag::url('haberes') }}"><svg class="i"><use xlink:href="#i-dollar"></use></svg><span>Haberes</span></a>
        <a class="item" href="{{ Ag::url('asistencia') }}"><svg class="i"><use xlink:href="#i-clock"></use></svg><span>Asistencia</span></a>
        @if($seccion == 'asistencia')
        <div class="sub">
            <a class="item @if(isset($pestana) && $pestana == 'calendario') active @endif" href="{{ Ag::url('asistencia') }}">Calendario</a>
            <a class="item @if(isset($pestana) && $pestana == 'bandas') active @endif" href="{{ Ag::url('asistencia/bandas') }}">Bandas horarias</a>
            <a class="item @if(isset($pestana) && $pestana == 'licencias') active @endif" href="{{ Ag::url('asistencia/licencias') }}">Licencias</a>
            <a class="item @if(isset($pestana) && $pestana == 'accesos') active @endif" href="{{ Ag::url('asistencia/accesos') }}">Accesos</a>
        </div>
        @endif
        <a class="item @if($seccion == 'carrera') active @endif" href="{{ Ag::url('carrera') }}"><svg class="i"><use xlink:href="#i-trend"></use></svg><span>Carrera</span></a>
        <a class="item @if($seccion == 'contrato') active @endif" href="{{ Ag::url('contrato') }}"><svg class="i"><use xlink:href="#i-briefcase"></use></svg><span>Contrato</span></a>
        <a class="item @if($seccion == 'mensajes') active @endif" href="{{ Ag::url('mensajes') }}"><svg class="i"><use xlink:href="#i-mail"></use></svg><span>Mensajes</span>@if($sinLeer > 0)<span class="count">{{ Ag::e($sinLeer) }}</span>@endif</a>
    </div>

    <div class="nav-group">
        <div class="nav-label">Sesión</div>
        <form method="post" action="{{ Ag::url('salir') }}">
            @csrf
            <button type="submit" class="item"><svg class="i"><use xlink:href="#i-out"></use></svg><span>Salir</span></button>
        </form>
    </div>

    <div class="nav-foot">Autogestión · NEWROL RRHH</div>
</aside>
