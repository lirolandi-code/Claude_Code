@php
    $nrE = Vista::estadoGerencial(isset($subtab) ? $subtab : '', isset($active) ? $active : '');
    $nrPag = $nrE['pag'];
    $nrQ = Vista::q($legajo_coded);
@endphp
<div class="bar-row">
    <div class="seg">
        <a class="@if($nrPag == 'capacitacion') on @endif" href="{{ route('gerencial.cursos.index') }}{{ $nrQ }}">Capacitación</a>
        <a class="@if($nrPag == 'evaluaciones') on @endif" href="{{ route('gerencial.tabs', 'O6704', 'G','false','1','carrera') }}{{ $nrQ }}">Evaluaciones</a>
        <a class="@if($nrPag == 'carrera') on @endif" href="{{ route('gerencial.cursos.carrera','2') }}{{ $nrQ }}">Carrera administrativa</a>
    </div>
</div>
