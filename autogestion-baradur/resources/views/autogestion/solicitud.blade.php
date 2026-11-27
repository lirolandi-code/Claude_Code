@extends('autogestion.layouts.app')

@section('contenido')
<div class="page-h">
    <div>
        <a href="{{ Ag::url($tipo == 'datos' ? 'datos' : 'asistencia') }}" class="btn ghost sm" style="margin-left:-12px"><svg class="i"><use xlink:href="#i-back"></use></svg>Volver</a>
        <h1 style="margin-top:6px">{{ Ag::e($tituloForm) }}</h1>
        <p>{{ Ag::e($ayuda) }}</p>
    </div>
</div>

<form class="form-stack" method="post" action="{{ Ag::url('solicitud/' . $tipo) }}" style="max-width:760px" novalidate>
    @csrf
    <div class="card card-b">
        <div class="grid-form">
            @if($tipo == 'licencia')
            <div class="field full @if(isset($errores['articulo'])) invalid @endif">
                <label for="articulo">Artículo <span class="req">*</span></label>
                <select class="select" id="articulo" name="articulo" required>
                    <option value="">Elija un artículo…</option>
                    @foreach($articulos as $a)
                    <option value="{{ Ag::e($a['codigo']) }}" @if($valores['articulo'] == $a['codigo']) selected @endif>{{ Ag::e($a['codigo']) }} · {{ Ag::e($a['descripcion']) }}</option>
                    @endforeach
                </select>
                @if(isset($errores['articulo']))<span class="err">{{ Ag::e($errores['articulo']) }}</span>@endif
            </div>
            @endif

            @if($tipo != 'datos')
            <div class="field @if(isset($errores['desde'])) invalid @endif">
                <label for="desde">{{ $tipo == 'reclamo' ? 'Día' : 'Desde' }} <span class="req">*</span></label>
                <input class="input" type="date" id="desde" name="desde" value="{{ Ag::e($valores['desde']) }}" required>
                @if(isset($errores['desde']))<span class="err">{{ Ag::e($errores['desde']) }}</span>@endif
            </div>
            <div class="field @if(isset($errores['hasta'])) invalid @endif">
                <label for="hasta">{{ $tipo == 'reclamo' ? 'Hasta (opcional)' : 'Hasta' }}</label>
                <input class="input" type="date" id="hasta" name="hasta" value="{{ Ag::e($valores['hasta']) }}">
                @if(isset($errores['hasta']))<span class="err">{{ Ag::e($errores['hasta']) }}</span>@else<span class="help">Si es un solo día, déjelo vacío.</span>@endif
            </div>
            @endif

            <div class="field full @if(isset($errores['detalle'])) invalid @endif">
                <label for="detalle">{{ $tipo == 'licencia' ? 'Observaciones (opcional)' : 'Detalle' }} @if($tipo != 'licencia')<span class="req">*</span>@endif</label>
                <textarea class="textarea" id="detalle" name="detalle" maxlength="2000" placeholder="{{ $tipo == 'datos' ? 'Ejemplo: cambié de domicilio, el nuevo es…' : ($tipo == 'reclamo' ? 'Ejemplo: fiché la salida a las 18:40 pero no quedó registrada.' : '') }}">{{ Ag::e($valores['detalle']) }}</textarea>
                @if(isset($errores['detalle']))<span class="err">{{ Ag::e($errores['detalle']) }}</span>@endif
            </div>
        </div>
    </div>
    <div class="savebar" style="bottom:0">
        <span class="grow">Los campos con * son obligatorios.</span>
        <a class="btn" href="{{ Ag::url($tipo == 'datos' ? 'datos' : 'asistencia') }}">Cancelar</a>
        <button class="btn primary" type="submit"><svg class="i"><use xlink:href="#i-check"></use></svg>Enviar pedido</button>
    </div>
</form>
@endsection
