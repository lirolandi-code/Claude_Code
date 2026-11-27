@extends('autogestion.layouts.app')

@section('contenido')
<div class="page-h">
    <div>
        <span class="eyebrow">{{ Ag::e($fechaLarga) }}</span>
        <h1>{{ Ag::e($saludo) }}, {{ Ag::e($primerNombre) }}</h1>
        <p>Esto es lo más reciente de su información laboral.</p>
    </div>
</div>

@if($ultimoRecibo)
<div class="hero-pay">
    <div>
        <span class="eyebrow">Último recibo · {{ Ag::e($ultimoRecibo['descripcion']) }} · pagado el {{ Ag::e($ultimoRecibo['fechaPago']) }}</span>
        <div class="amt">$ <span class="money-v">{{ Ag::e($ultimoRecibo['neto']) }}</span></div>
        <span style="color:#9fb7d6;font-size:13px">Importe neto depositado</span>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button type="button" class="btn" data-importes><svg class="i"><use xlink:href="#i-eye"></use></svg><span>Mostrar importes</span></button>
        @if($ultimoRecibo['tieneArchivo'])
        <a class="btn primary" href="{{ Ag::url('haberes/recibo/' . (int) $ultimoRecibo['id']) }}"><svg class="i"><use xlink:href="#i-download"></use></svg>Descargar recibo</a>
        @endif
    </div>
</div>
@endif

<div class="home-grid">
    <div class="card span2">
        <div class="card-h"><h2>Mi semana</h2><a class="btn ghost sm" href="{{ Ag::url('asistencia') }}">Ver calendario</a></div>
        <div class="card-b">
            <div class="week">
                @foreach($semana as $d)
                <div class="@if($d['hoy']) today @endif">
                    <span>{{ Ag::e($d['diaCorto']) }} {{ Ag::e($d['dia']) }}</span>
                    @if($d['feriado'])
                        <b style="color:var(--celeste)">Feriado</b>
                        <small class="muted">{{ Ag::e($d['feriado']) }}</small>
                    @elseif($d['entrada'] !== null)
                        <b>{{ Ag::horas($d['entrada']) }} – {{ $d['salida'] !== null ? Ag::horas($d['salida']) : '…' }}</b>
                        @if(count($d['novedades']))
                            <span class="pill {{ Ag::e($d['novedades'][0]['clase']) }}">{{ Ag::e($d['novedades'][0]['descripcion']) }}</span>
                        @elseif($d['horas'] !== null)
                            <small class="muted">{{ Ag::horas($d['horas']) }} h</small>
                        @else
                            <small class="muted">Entrada registrada</small>
                        @endif
                    @elseif(count($d['novedades']))
                        <span class="pill {{ Ag::e($d['novedades'][0]['clase']) }}">{{ Ag::e($d['novedades'][0]['descripcion']) }}</span>
                    @else
                        <b class="muted">—</b>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card card-b lic">
        <span class="eyebrow">Vacaciones disponibles</span>
        @if($vacaciones)
            <span class="big">{{ Ag::e($vacaciones['grande']) }}<small>{{ Ag::e($vacaciones['leyenda']) }}</small></span>
            <div class="meter"><i style="width:{{ (int) $vacaciones['pct'] }}%"></i></div>
            <small class="muted">{{ Ag::e($vacaciones['nota']) }}</small>
        @else
            <small class="muted">Todavía no hay saldo cargado.</small>
        @endif
        <a class="btn sm" href="{{ Ag::url('asistencia/licencias') }}" style="align-self:flex-start">Ver licencias</a>
    </div>

    <div class="card">
        <div class="card-h"><h2>Pendientes</h2>@if(count($pendientes))<span class="chip warn">{{ count($pendientes) }}</span>@endif</div>
        @if(count($pendientes))
        <ul class="todo">
            @foreach($pendientes as $p)
            <li>
                <span class="ti" style="background:var(--{{ $p['tono'] == 'brand' ? 'brand' : $p['tono'] }}-soft);color:var(--{{ $p['tono'] == 'brand' ? 'brand' : $p['tono'] }})"><svg class="i"><use xlink:href="#i-{{ Ag::e($p['icono']) }}"></use></svg></span>
                <div class="grow"><b>{{ Ag::e($p['titulo']) }}</b><small>{{ Ag::e($p['detalle']) }}</small></div>
                <a class="btn sm" href="{{ Ag::e($p['url']) }}">{{ Ag::e($p['accion']) }}</a>
            </li>
            @endforeach
        </ul>
        @else
        <div class="empty">No tiene trámites pendientes.</div>
        @endif
    </div>

    <div class="card card-b lic">
        <span class="eyebrow">Promedio diario · este mes</span>
        <span class="big">{{ Ag::horas($promedio) }}<small>horas</small></span>
        <small class="muted">@if($horario)Horario oficial {{ Ag::e($horario) }} · @endif{{ Ag::e($diasCompletos) }} días con fichada completa</small>
        <a class="btn sm" href="{{ Ag::url('asistencia/bandas') }}" style="align-self:flex-start">Ver bandas horarias</a>
    </div>

    <div class="card card-b lic">
        <span class="eyebrow">Capacitación</span>
        <span class="big">{{ Ag::e($creditos['actual']) }}<small>de {{ Ag::e($creditos['requeridos']) }} créditos</small></span>
        <div class="meter"><i style="width:{{ (int) $creditos['pct'] }}%"></i></div>
        <small class="muted">@if($creditos['faltan'] > 0)Le faltan {{ Ag::e($creditos['faltan']) }} créditos para el próximo grado @else Ya reúne los créditos para el próximo grado @endif</small>
        @if($proximoFeriado)
        <small class="muted">Próximo feriado: {{ Ag::e($proximoFeriado['texto']) }} · {{ Ag::e($proximoFeriado['descripcion']) }}</small>
        @endif
        <a class="btn sm" href="{{ Ag::url('carrera') }}" style="align-self:flex-start">Ver carrera</a>
    </div>
</div>
@endsection
