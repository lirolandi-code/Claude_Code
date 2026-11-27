@php
    $nrE = Vista::estadoGerencial(isset($subtab) ? $subtab : '', isset($active) ? $active : '');
    $nrPag = $nrE['pag'];
    $nrQ = Vista::q($legajo_coded);
    $nrYM = '&year=' . (isset($year) ? $year : date('Y')) . '&month=' . (isset($month) ? $month : date('m'));
@endphp
<div class="bar-row">
    <div class="seg">
        <a class="@if($nrPag == 'resumen') on @endif" href="{{ route('gerencial.asistencia.dashboard') }}{{ $nrQ }}">Resumen por mes</a>
        <a class="@if($nrPag == 'calendario') on @endif" href="{{ route('gerencial.asistencia.index') }}{{ $nrQ }}{{ Vista::e($nrYM) }}">Calendario</a>
        <a class="bandas-link @if($nrPag == 'bandas') on @endif" href="{{ route('gerencial.asistencia.bandasHorarias') }}{{ $nrQ }}{{ Vista::e($nrYM) }}">Bandas horarias</a>
        <a class="@if($nrPag == 'licencias') on @endif" href="{{ route('gerencial.asistencia.licencias') }}{{ $nrQ }}{{ Vista::e($nrYM) }}">Licencias</a>
        <a class="accesos-link @if($nrPag == 'accesos') on @endif" href="{{ route('gerencial.asistencia.accesos') }}{{ $nrQ }}{{ Vista::e($nrYM) }}">Accesos</a>
    </div>
</div>
