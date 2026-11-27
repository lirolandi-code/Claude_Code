@extends('layouts.header-gerencial')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Consulta de agente</span>
        <h1>Datos personales</h1>
        <p>Datos generales de {{ Vista::e($infoUsuario->A01A02) }}.</p>
    </div>
</div>

<div class="ncard" style="overflow:hidden;position:relative">
    <div id="iframeLoader" style="display:none;position:absolute;inset:0;background:color-mix(in srgb,var(--surface) 90%,transparent);z-index:2;justify-content:center;align-items:center;flex-direction:column;gap:12px">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando…</span></div>
        <p class="muted" style="margin:0">Cargando contenido, por favor espere…</p>
    </div>
    @if($usarPost)
        <iframe class="frame" name="iframeSalida" height="800" title="Datos generales"></iframe>
    @else
        <iframe class="frame" src="{{ $urlSalida }}" height="800" title="Datos generales"></iframe>
    @endif
</div>

<form action="{{ $urlSalida }}" method="POST" target="iframeSalida" id="formSalida">
    @csrf
    <input type="hidden" name="legajo" value="{{ $infoUsuario->A01A01 }}">
    <input type="hidden" name="autoconsulta" value="1">
</form>
@endsection
