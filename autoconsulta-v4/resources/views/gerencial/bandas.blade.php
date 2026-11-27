@extends('layouts.header-gerencial')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Consulta de agente</span>
        <h1>Asistencia</h1>
        <p>Franja horaria de cada día del mes y días con novedades.</p>
    </div>
    <div class="actions">
        <a class="nbtn" href="{{ route('gerencial.asistencia.dashboard') }}{{ Vista::q($legajo_coded) }}&year={{ Vista::e(isset($year) ? $year : date('Y')) }}"><svg class="i"><use xlink:href="#i-back"></use></svg>Volver al resumen por mes</a>
    </div>
</div>

@include('partials.nr-g-tabs-asistencia')

<div class="bar-row" style="justify-content:flex-end">
    <div style="display:flex;align-items:center;gap:6px">
        {{-- mesAnterior() y mesSiguiente() están en el layout, igual que antes --}}
        <button type="button" class="icon-btn" onclick="mesAnterior()" aria-label="Mes anterior"><svg class="i"><use xlink:href="#i-back"></use></svg></button>
        <b style="font-family:var(--f-display)">{{ Vista::e($nombreMes) }} de {{ Vista::e($anio) }}</b>
        <button type="button" class="icon-btn" onclick="mesSiguiente()" aria-label="Mes siguiente"><svg class="i"><use xlink:href="#i-chev"></use></svg></button>
    </div>
</div>

<div class="ncard" style="overflow:hidden">
    <iframe class="frame" name="bandas-iframe" height="800" scrolling="no" title="Bandas horarias de {{ Vista::e($nombreMes) }} de {{ Vista::e($anio) }}"></iframe>
</div>

<form id="formSalida" action=" https://sv3000-prod.mecon.gob.ar/novedades/consulta-bandas" method="POST" target="bandas-iframe">
    @csrf
    <input type="hidden" name="legajo" id="legajo_form" value="{{ $legajo }}">
    <input type="hidden" name="anio" id="anio_form" value="{{ $anio }}">
    <input type="hidden" name="mes" id="mes_form" value="{{ $mes }}">
</form>
@endsection
