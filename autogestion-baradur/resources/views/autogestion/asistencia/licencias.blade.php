@extends('autogestion.layouts.app')

@section('contenido')
@include('autogestion.partials.asistencia_cabecera')

@if(count($tarjetas))
<div class="lic-cards">
    @foreach($tarjetas as $t)
    <div class="card lic">
        <span class="eyebrow">{{ Ag::e($t['titulo']) }}</span>
        <span class="big">{{ Ag::e($t['grande']) }}<small>{{ Ag::e($t['leyenda']) }}</small></span>
        <div class="meter @if($t['alerta']) warn @endif"><i style="width:{{ (int) $t['pct'] }}%"></i></div>
        <small class="muted">{{ Ag::e($t['nota']) }}</small>
    </div>
    @endforeach
</div>
@endif

<div class="card">
    <div class="card-h"><h2>Consumo por artículo · {{ (int) $anioActual }}</h2></div>
    @if(count($tabla))
    <div class="table-wrap">
        <table class="t">
            <thead><tr><th>Artículo</th><th>Descripción</th><th style="width:34%">Consumo</th><th>Usado</th><th>Disponible</th></tr></thead>
            <tbody>
                @foreach($tabla as $s)
                <tr>
                    <td><span class="tag">{{ Ag::e($s['codigo']) }}</span></td>
                    <td>{{ Ag::e($s['descripcion']) }}</td>
                    <td><div class="meter @if($s['alerta']) warn @endif"><i style="width:{{ (int) $s['pct'] }}%"></i></div></td>
                    <td class="mono">{{ Ag::e($s['usado']) }}</td>
                    <td class="mono">{{ Ag::e($s['disponible']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty">No hay consumos registrados este año.</div>
    @endif
</div>
@endsection
