@extends('layouts.header-gerencial')

{{-- Pantallas que muestran un sistema externo dentro de un marco:
     Documentación requerida (personal), Evaluaciones y Carrera administrativa (carrera). --}}
@section('content')
@php $nrE = Vista::estadoGerencial(isset($subtab) ? $subtab : '', isset($active) ? $active : ''); @endphp
<div class="page-h">
    <div>
        <span class="eyebrow">Consulta de agente</span>
        <h1>{{ $nrE['secTexto'] }}</h1>
        <p>{{ $nrE['pagTexto'] }}</p>
    </div>
</div>

@if ($subtab == 'carrera')
    @include('partials.nr-g-tabs-carrera')
@endif

<div class="ncard" style="overflow:hidden;position:relative">
    <div id="iframeLoader" style="display:none;position:absolute;inset:0;background:color-mix(in srgb,var(--surface) 90%,transparent);z-index:2;justify-content:center;align-items:center;flex-direction:column;gap:12px">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando…</span></div>
        <p class="muted" style="margin:0">Cargando contenido, por favor espere…</p>
    </div>
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
