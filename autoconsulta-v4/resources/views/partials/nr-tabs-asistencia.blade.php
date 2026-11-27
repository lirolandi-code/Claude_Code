@php $nrE = Vista::estado(isset($subtab) ? $subtab : '', isset($active) ? $active : ''); $nrPag = $nrE['pag']; @endphp
<div class="bar-row">
    <div class="seg">
        <a class="@if($nrPag == 'calendario') on @endif" href="{{ route('asistencia.index') }}">Calendario</a>
        <a class="bandas-link @if($nrPag == 'bandas') on @endif" href="{{ route('asistencia.bandasHorarias') }}">Bandas horarias</a>
        <a class="@if($nrPag == 'licencias') on @endif" href="{{ route('asistencia.licencias') }}">Licencias</a>
        <a class="accesos-link @if($nrPag == 'accesos') on @endif" href="{{ route('asistencia.accesos') }}">Accesos</a>
    </div>
</div>
