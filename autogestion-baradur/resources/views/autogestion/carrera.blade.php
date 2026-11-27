@extends('autogestion.layouts.app')

@section('contenido')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Carrera</h1>
        <p>Capacitación, evaluaciones de desempeño y su recorrido en la carrera administrativa.</p>
    </div>
</div>

<div class="summary">
    @foreach($resumen as $r)
    <div><span>{{ Ag::e($r[0]) }}</span><b>{{ Ag::e($r[1]) }}</b></div>
    @endforeach
</div>

<div class="home-grid">
    <div class="card span2">
        <div class="card-h"><h2>Capacitación</h2></div>
        @if(count($capacitaciones))
        <div class="table-wrap">
            <table class="t">
                <thead><tr><th>Actividad</th><th>Organismo</th><th>Finalizó</th><th>Créditos</th><th>Estado</th></tr></thead>
                <tbody>
                    @foreach($capacitaciones as $c)
                    <tr>
                        <td style="white-space:normal;min-width:220px"><b style="font-weight:600">{{ Ag::e($c['actividad']) }}</b></td>
                        <td>{{ Ag::e($c['organismo']) }}</td>
                        <td class="mono">{{ Ag::e($c['fin']) }}</td>
                        <td class="mono">{{ Ag::e($c['creditos']) }}</td>
                        <td><span class="chip {{ Ag::e($c['tono']) }}">{{ Ag::e($c['estado']) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty">Todavía no hay actividades de capacitación registradas.</div>
        @endif
    </div>

    <div class="card">
        <div class="card-h"><h2>Evaluaciones de desempeño</h2></div>
        @if(count($evaluaciones))
        <ul class="todo">
            @foreach($evaluaciones as $ev)
            <li>
                <span class="ti" style="background:var(--brand-soft);color:var(--brand)"><svg class="i"><use xlink:href="#i-award"></use></svg></span>
                <div class="grow"><b>{{ Ag::e($ev['calificacion']) }}</b><small>Período {{ Ag::e($ev['anio']) }}</small></div>
                <span class="stars" title="{{ (int) $ev['puntaje'] }} de 5">@foreach($ev['marcas'] as $m)<i class="@if($m) on @endif"></i>@endforeach</span>
            </li>
            @endforeach
        </ul>
        @else
        <div class="empty">Sin evaluaciones registradas.</div>
        @endif
    </div>

    <div class="card card-b span2">
        <h2 style="margin-bottom:18px">Carrera administrativa</h2>
        @if(count($hitos))
        <ul class="timeline">
            @foreach($hitos as $h)
            <li class="@if($h['actual']) now @endif"><b>{{ Ag::e($h['descripcion']) }}</b><small>{{ Ag::e($h['detalle']) }}</small></li>
            @endforeach
        </ul>
        @else
        <p class="muted">Sin movimientos registrados.</p>
        @endif
    </div>

    <div class="card card-b lic">
        <span class="eyebrow">Próximo grado</span>
        <span class="big">{{ Ag::e($creditos['faltan']) }}<small>créditos faltantes</small></span>
        <div class="meter"><i style="width:{{ (int) $creditos['pct'] }}%"></i></div>
        <small class="muted">Lleva {{ Ag::e($creditos['actual']) }} de {{ Ag::e($creditos['requeridos']) }} créditos.</small>
    </div>
</div>
@endsection
