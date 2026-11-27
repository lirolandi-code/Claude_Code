@php $nrE = Vista::estado(isset($subtab) ? $subtab : '', isset($active) ? $active : ''); $nrPag = $nrE['pag']; @endphp
<div class="bar-row">
    <div class="seg">
        <a class="@if($nrPag == 'general') on @endif" href="{{ route('general') }}">General</a>
        <a class="@if($nrPag == 'documentacion') on @endif" href="{{ route('tabs', 'S2302', 'G','false','1','personal') }}">Documentación requerida</a>
    </div>
</div>
