{{-- Autoconsulta v4 · barra lateral. Usa las variables calculadas en layouts/header-landing. --}}
<aside class="sidenav" id="nr-nav">
    <a class="nav-brand" href="{{ route('general') }}"><span class="logo">A</span><span><b>AUTOCONSULTA</b><small>RECURSOS HUMANOS</small></span></a>

    <div class="nav-me">
        <img class="foto" src="{{ Auth::user()->getImageAttribute() }}" alt="Foto de {{ Vista::e(auth()->user()->nombre) }}">
        <b>{{ Vista::e(auth()->user()->nombre) }}</b>
        <small>Legajo {{ Vista::e($nrLegajo) }}</small>
    </div>

    <div class="nav-group">
        <div class="nav-label">Mi información</div>

        <a class="item @if($nrSec == 'personal') active @endif" href="{{ route('general') }}"><svg class="i"><use xlink:href="#i-user"></use></svg><span>Datos personales</span></a>
        @if($nrSec == 'personal')
        <div class="sub">
            <a class="item @if($nrPag == 'general') active @endif" href="{{ route('general') }}">General</a>
            <a class="item @if($nrPag == 'documentacion') active @endif" href="{{ route('tabs', 'S2302', 'G','false','1','personal') }}">Documentación requerida</a>
        </div>
        @endif

        <a class="item @if($nrSec == 'haberes') active @endif" href="{{ route('recibos.show') }}"><svg class="i"><use xlink:href="#i-dollar"></use></svg><span>Haberes</span></a>

        <a class="item @if($nrSec == 'asistencia') active @endif" href="{{ route('asistencia.index') }}"><svg class="i"><use xlink:href="#i-clock"></use></svg><span>Asistencia</span></a>
        @if($nrSec == 'asistencia')
        <div class="sub">
            <a class="item @if($nrPag == 'calendario') active @endif" href="{{ route('asistencia.index') }}">Calendario</a>
            <a class="item bandas-link @if($nrPag == 'bandas') active @endif" href="{{ route('asistencia.bandasHorarias') }}">Bandas horarias</a>
            <a class="item @if($nrPag == 'licencias') active @endif" href="{{ route('asistencia.licencias') }}">Licencias</a>
            <a class="item accesos-link @if($nrPag == 'accesos') active @endif" href="{{ route('asistencia.accesos') }}">Accesos</a>
        </div>
        @endif

        <a class="item @if($nrSec == 'carrera') active @endif" href="{{ route('cursos.index') }}"><svg class="i"><use xlink:href="#i-trend"></use></svg><span>Carrera</span></a>
        @if($nrSec == 'carrera')
        <div class="sub">
            <a class="item @if($nrPag == 'capacitacion') active @endif" href="{{ route('cursos.index') }}">Capacitación</a>
            <a class="item @if($nrPag == 'evaluaciones') active @endif" href="{{ route('tabs', 'O6704', 'G','false','1','carrera') }}">Evaluaciones</a>
            <a class="item @if($nrPag == 'carrera') active @endif" href="{{ route('cursos.carrera','2') }}">Carrera administrativa</a>
        </div>
        @endif

        <a class="item @if($nrSec == 'contrato') active @endif" href="{{ route('contrato.index') }}"><svg class="i"><use xlink:href="#i-briefcase"></use></svg><span>Contrato</span></a>

        <button type="button" class="item" data-bs-toggle="modal" data-bs-target="#observacionModal"><svg class="i"><use xlink:href="#i-mail"></use></svg><span>Observaciones</span><span class="count nr-obs-count" style="{{ $nrSinLeer > 0 ? '' : 'display:none' }}">{{ $nrSinLeer }}</span></button>
    </div>

    <div class="nav-group">
        <div class="nav-label">Ayuda</div>
        <a class="item" href="{{ asset('assets/video/autoconsulta.mp4') }}" download="autoconsulta.mp4"><svg class="i"><use xlink:href="#i-play"></use></svg><span>Video explicativo</span></a>
        <a class="item" id="salir" href="{{ route('logout') }}"><svg class="i"><use xlink:href="#i-out"></use></svg><span>Salir</span></a>
    </div>

    <div class="nav-foot">Aquí encontrará sus recibos de sueldo, su asistencia y su información personal.</div>
</aside>
