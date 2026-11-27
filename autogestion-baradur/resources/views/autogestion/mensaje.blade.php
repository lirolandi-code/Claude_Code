@extends('autogestion.layouts.app')

@section('contenido')
<div class="page-h">
    <div>
        <a href="{{ Ag::url('mensajes') }}" class="btn ghost sm" style="margin-left:-12px"><svg class="i"><use xlink:href="#i-back"></use></svg>Volver a mensajes</a>
        <h1 style="margin-top:6px">{{ Ag::e($mensaje['asunto']) }}</h1>
        <p>{{ Ag::e($mensaje['remitente']) }} · <span class="mono">{{ Ag::e($mensaje['fecha']) }}</span></p>
    </div>
</div>

<div class="card card-b msg-body">
    @foreach($mensaje['parrafos'] as $p)
    <p>{{ nl2br(Ag::e($p)) }}</p>
    @endforeach
</div>
@endsection
