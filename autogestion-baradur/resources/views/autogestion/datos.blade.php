@extends('autogestion.layouts.app')

@section('contenido')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Datos personales</h1>
        <p>Si algún dato es incorrecto, solicite la actualización a Recursos Humanos.</p>
    </div>
    <div class="actions"><a class="btn primary" href="{{ Ag::url('solicitud/datos') }}"><svg class="i"><use xlink:href="#i-edit"></use></svg>Solicitar actualización</a></div>
</div>

<div class="bar-row">
    <div class="seg">
        <a class="on" href="{{ Ag::url('datos') }}">General</a>
        <a href="{{ Ag::url('documentacion') }}">Documentación requerida @if($docsPendientes > 0)<span class="chip warn" style="height:18px;margin-left:4px">{{ (int) $docsPendientes }}</span>@endif</a>
    </div>
</div>

<div class="two">
    <div class="card">
        <div class="card-h"><h2>Identificación</h2></div>
        <div class="card-b" style="padding-block:4px">
            <dl class="dl">
                @foreach($identificacion as $f)
                <dt>{{ Ag::e($f[0]) }}</dt><dd class="@if($f[2]) mono @endif">{{ Ag::e($f[1]) }}</dd>
                @endforeach
            </dl>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:20px;min-width:0">
        <div class="card">
            <div class="card-h"><h2>Formación</h2></div>
            <div class="card-b" style="padding-block:4px">
                <dl class="dl">
                    @foreach($formacion as $f)
                    <dt>{{ Ag::e($f[0]) }}</dt><dd>{{ Ag::e($f[1]) }}</dd>
                    @endforeach
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-h"><h2>Contacto</h2></div>
            <div class="card-b" style="padding-block:4px">
                <dl class="dl">
                    @foreach($contacto as $f)
                    <dt>{{ Ag::e($f[0]) }}</dt><dd class="@if($f[2]) mono @endif">{{ Ag::e($f[1]) }}</dd>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
