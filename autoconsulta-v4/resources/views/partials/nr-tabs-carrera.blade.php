@php $nrE = Vista::estado(isset($subtab) ? $subtab : '', isset($active) ? $active : ''); $nrPag = $nrE['pag']; @endphp
<div class="bar-row">
    <div class="seg">
        <a class="@if($nrPag == 'capacitacion') on @endif" href="{{ route('cursos.index') }}">Capacitación</a>
        <a class="@if($nrPag == 'evaluaciones') on @endif" href="{{ route('tabs', 'O6704', 'G','false','1','carrera') }}">Evaluaciones</a>
        <a class="@if($nrPag == 'carrera') on @endif" href="{{ route('cursos.carrera','2') }}">Carrera administrativa</a>
    </div>
</div>
