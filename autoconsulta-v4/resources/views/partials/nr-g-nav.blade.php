{{-- Autoconsulta v4 · barra lateral de la vista gerencial (consulta de un agente desde el tablero) --}}
@php $nrQ = Vista::q($legajo_coded); @endphp
<aside class="sidenav" id="nr-nav">
    <span class="nav-brand"><span class="logo">A</span><span><b>AUTOCONSULTA</b><small>CONSULTA GERENCIAL</small></span></span>

    <div class="nav-me">
        <img class="foto" src="{{ Vista::fotoLegajo($infoUsuario) }}" alt="Foto de {{ Vista::e($infoUsuario->A01A02) }}">
        <b>{{ Vista::e($infoUsuario->A01A02) }}</b>
        <small>Legajo {{ Vista::e($infoUsuario->A01A01) }}</small>
        <span class="chip" style="background:rgba(255,255,255,.08);color:#cfe0f5;border-color:transparent">Solo lectura</span>
    </div>

    <div class="nav-group">
        <div class="nav-label">Información del agente</div>
        <a class="item @if($nrSec == 'personal') active @endif" href="{{ route('gerencial.index') }}{{ $nrQ }}"><svg class="i"><use xlink:href="#i-user"></use></svg><span>Datos personales</span></a>
        <a class="item @if($nrSec == 'haberes') active @endif" href="{{ route('gerencial.recibos.show') }}{{ $nrQ }}"><svg class="i"><use xlink:href="#i-dollar"></use></svg><span>Haberes</span></a>

        <a class="item @if($nrSec == 'asistencia') active @endif" href="{{ route('gerencial.asistencia.dashboard') }}{{ $nrQ }}"><svg class="i"><use xlink:href="#i-clock"></use></svg><span>Asistencia</span></a>
        @if($nrSec == 'asistencia')
        @php $nrYM = '&year=' . (isset($year) ? $year : date('Y')) . '&month=' . (isset($month) ? $month : date('m')); @endphp
        <div class="sub">
            <a class="item @if($nrPag == 'resumen') active @endif" href="{{ route('gerencial.asistencia.dashboard') }}{{ $nrQ }}">Resumen por mes</a>
            <a class="item @if($nrPag == 'calendario') active @endif" href="{{ route('gerencial.asistencia.index') }}{{ $nrQ }}{{ Vista::e($nrYM) }}">Calendario</a>
            <a class="item bandas-link @if($nrPag == 'bandas') active @endif" href="{{ route('gerencial.asistencia.bandasHorarias') }}{{ $nrQ }}{{ Vista::e($nrYM) }}">Bandas horarias</a>
            <a class="item @if($nrPag == 'licencias') active @endif" href="{{ route('gerencial.asistencia.licencias') }}{{ $nrQ }}{{ Vista::e($nrYM) }}">Licencias</a>
            <a class="item accesos-link @if($nrPag == 'accesos') active @endif" href="{{ route('gerencial.asistencia.accesos') }}{{ $nrQ }}{{ Vista::e($nrYM) }}">Accesos</a>
        </div>
        @endif

        <a class="item @if($nrSec == 'carrera') active @endif" href="{{ route('gerencial.cursos.index') }}{{ $nrQ }}"><svg class="i"><use xlink:href="#i-trend"></use></svg><span>Carrera</span></a>
        @if($nrSec == 'carrera')
        <div class="sub">
            <a class="item @if($nrPag == 'capacitacion') active @endif" href="{{ route('gerencial.cursos.index') }}{{ $nrQ }}">Capacitación</a>
            <a class="item @if($nrPag == 'evaluaciones') active @endif" href="{{ route('gerencial.tabs', 'O6704', 'G','false','1','carrera') }}{{ $nrQ }}">Evaluaciones</a>
            <a class="item @if($nrPag == 'carrera') active @endif" href="{{ route('gerencial.cursos.carrera','2') }}{{ $nrQ }}">Carrera administrativa</a>
        </div>
        @endif

        <a class="item @if($nrSec == 'contrato') active @endif" href="{{ route('gerencial.contrato.index') }}{{ $nrQ }}"><svg class="i"><use xlink:href="#i-briefcase"></use></svg><span>Contrato</span></a>
    </div>

    <div class="nav-foot">Está viendo la información de otro agente en modo consulta.</div>
</aside>
