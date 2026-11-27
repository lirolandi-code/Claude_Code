@extends('autogestion.layouts.app')

@section('contenido')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Haberes</h1>
        <p>Recibos de sueldo firmados digitalmente. Puede descargarlos en PDF.</p>
    </div>
    <div class="actions">
        <button type="button" class="btn" data-importes><svg class="i"><use xlink:href="#i-eye"></use></svg><span>Mostrar importes</span></button>
        @if($hayF1359)
        <a class="btn primary" href="{{ Ag::url('haberes/formulario1359/' . (int) $anio) }}"><svg class="i"><use xlink:href="#i-download"></use></svg>Formulario 1359 · {{ (int) $anio }}</a>
        @endif
    </div>
</div>

<div class="card">
    <form class="toolbar" method="get" action="{{ Ag::url('haberes') }}">
        <label for="anio" class="muted">Año</label>
        <select class="select" id="anio" name="anio" style="min-width:120px" onchange="this.form.submit()">
            @foreach($anios as $a)
            <option value="{{ (int) $a }}" @if($a == $anio) selected @endif>{{ (int) $a }}</option>
            @endforeach
        </select>
        <noscript><button class="btn sm" type="submit">Ver</button></noscript>
        <span class="muted" style="margin-left:auto">{{ count($recibos) }} {{ count($recibos) == 1 ? 'recibo' : 'recibos' }} en {{ (int) $anio }}</span>
    </form>

    @if(count($recibos))
        @foreach($recibos as $r)
        <div class="receipt">
            <span class="ri">{{ Ag::e($r['sigla']) }}<small>{{ Ag::e($r['anioCorto']) }}</small></span>
            <div><b>{{ Ag::e($r['descripcion']) }}</b><small>{{ Ag::e($r['tipo']) }} · Legajo {{ Ag::e($empleado['legajo']) }}</small></div>
            <div class="hide-sm"><small>Fecha de pago</small><div class="mono">{{ Ag::e($r['fechaPago']) }}</div></div>
            <div class="hide-sm"><small>Neto</small><div class="money">$ <span class="money-v">{{ Ag::e($r['neto']) }}</span></div></div>
            <div class="hide-sm">@if($r['ganancias'])<span class="chip info">Con retención de Ganancias</span>@else<span class="chip">Sin retención</span>@endif</div>
            <div style="display:flex;gap:2px">
                @if($r['tieneArchivo'])
                <a class="icon-btn" href="{{ Ag::url('haberes/recibo/' . (int) $r['id']) }}" aria-label="Descargar recibo de {{ Ag::e($r['descripcion']) }}"><svg class="i"><use xlink:href="#i-download"></use></svg></a>
                @endif
            </div>
        </div>
        @endforeach
    @else
        <div class="empty">No hay recibos para este año.</div>
    @endif
</div>
@endsection
