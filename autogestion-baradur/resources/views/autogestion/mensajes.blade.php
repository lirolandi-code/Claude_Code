@extends('autogestion.layouts.app')

@section('contenido')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Mensajes</h1>
        <p>Avisos de Recursos Humanos sobre sus trámites.</p>
    </div>
</div>

<div class="card">
    @if(count($mensajes))
        @foreach($mensajes as $m)
        <div class="msg @if(!$m['leido']) unread @endif">
            <a href="{{ Ag::url('mensajes/' . (int) $m['id']) }}">
                <span class="avatar" style="background:#1d5ca8">{{ Ag::e($m['iniciales']) }}</span>
                <div class="grow"><b>{{ Ag::e($m['asunto']) }}</b><p>{{ Ag::e($m['remitente']) }} · {{ Ag::e($m['resumen']) }}</p></div>
                <time>{{ Ag::e($m['fecha']) }}</time>
            </a>
        </div>
        @endforeach
    @else
        <div class="empty">No tiene mensajes.</div>
    @endif
</div>
@endsection
