@extends('autogestion.layouts.app')

@section('contenido')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Datos personales</h1>
        <p>Mantenga su documentación al día para evitar demoras en trámites y liquidaciones.</p>
    </div>
</div>

<div class="bar-row">
    <div class="seg">
        <a href="{{ Ag::url('datos') }}">General</a>
        <a class="on" href="{{ Ag::url('documentacion') }}">Documentación requerida</a>
    </div>
</div>

<div class="card">
    @if(count($documentos))
    <ul class="todo">
        @foreach($documentos as $d)
        <li>
            <span class="ti" style="background:var(--{{ $d['tono'] == 'info' ? 'brand' : $d['tono'] }}-soft);color:var(--{{ $d['tono'] == 'info' ? 'brand' : $d['tono'] }})"><svg class="i"><use xlink:href="#i-{{ Ag::e($d['icono']) }}"></use></svg></span>
            <div class="grow"><b>{{ Ag::e($d['descripcion']) }}</b><small>{{ Ag::e($d['detalle']) }}</small></div>
            <span class="chip {{ Ag::e($d['tono']) }}">{{ Ag::e($d['estado']) }}</span>
            @if($d['puedeSubir'])
            <form class="upload" method="post" enctype="multipart/form-data" action="{{ Ag::url('documentacion/' . (int) $d['id']) }}">
                @csrf
                <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required aria-label="Archivo para {{ Ag::e($d['descripcion']) }}">
                <button class="btn sm" type="submit"><svg class="i"><use xlink:href="#i-upload"></use></svg>Subir</button>
            </form>
            @endif
        </li>
        @endforeach
    </ul>
    @else
    <div class="empty">No hay documentación requerida.</div>
    @endif
</div>
<p class="help" style="margin-top:12px">Formatos aceptados: PDF, JPG o PNG, hasta 10 MB.</p>
@endsection
