@extends('layouts.header-landing')

{{-- Pantallas que muestran un sistema externo dentro de un marco:
     Documentación requerida (personal), Evaluaciones y Carrera administrativa (carrera). --}}
@section('content')
@php $nrE = Vista::estado(isset($subtab) ? $subtab : '', isset($active) ? $active : ''); @endphp
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>{{ $nrE['secTexto'] }}</h1>
        <p>{{ $nrE['pagTexto'] }}</p>
    </div>
</div>

@if ($subtab == 'personal')
    @include('partials.nr-tabs-personal')
@elseif ($subtab == 'carrera')
    @include('partials.nr-tabs-carrera')
@endif

<div class="ncard" style="overflow:hidden">
    @if($usarPost)
        <iframe class="frame" name="iframeSalida" height="800" title="{{ $nrE['pagTexto'] }}"></iframe>
    @else
        <iframe class="frame" src="{{ $urlSalida }}" height="800" title="{{ $nrE['pagTexto'] }}"></iframe>
    @endif
</div>

<form action="{{ $urlSalida }}" method="POST" target="iframeSalida" id="formSalida">
    @csrf
    <input type="hidden" name="legajo" value="{{ $infoUsuario->A01A01 }}">
    @if ($subtab == 'personal')
        <input type="hidden" name="autoconsulta" value="1">
    @elseif ($subtab == 'carrera')
        <input type="hidden" name="carrera" value="1">
    @endif
</form>
@endsection
