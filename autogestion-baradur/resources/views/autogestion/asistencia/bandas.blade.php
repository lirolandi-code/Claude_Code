@extends('autogestion.layouts.app')

@section('contenido')
@include('autogestion.partials.asistencia_cabecera')

<div class="summary">
    <div><span>Horario oficial</span><b>{{ Ag::e($resumen['horario']) }}</b></div>
    <div><span>Categoría</span><b>{{ Ag::e($resumen['categoria']) }}</b></div>
    <div><span>Días laborables</span><b>{{ Ag::e($resumen['laborables']) }}</b></div>
    <div><span>Días trabajados</span><b>{{ Ag::e($resumen['trabajados']) }}</b></div>
    <div><span>Promedio diario</span><b>{{ Ag::e($resumen['promedio']) }}</b></div>
</div>

@include('autogestion.partials.leyenda')

<div class="card">
    <div class="table-wrap" style="padding:12px 16px">
        <div class="bands">
            <div class="r head">
                <span></span>
                <div class="hours">@foreach($horasEje as $h)<span>{{ $h }}</span>@endforeach</div>
                <span class="tot">Total</span>
            </div>
            @foreach($filas as $f)
            <div class="r {{ Ag::e($f['clases']) }}">
                <span class="lbl"><em>{{ Ag::e($f['diaCorto']) }}</em>{{ Ag::e($f['dia']) }}</span>
                <div class="track">
                    <div class="off" style="left:{{ $oficial['left'] }};width:{{ $oficial['width'] }}"></div>
                    @if($f['texto'] !== '')<span class="tag-in">{{ Ag::e($f['texto']) }}</span>@endif
                    @foreach($f['barras'] as $b)
                    <div class="b {{ Ag::e($b['clase']) }}" style="left:{{ $b['left'] }};width:{{ $b['width'] }}" title="{{ Ag::e($b['rotulo']) }}">{{ Ag::e($b['rotulo']) }}</div>
                    @endforeach
                </div>
                <span class="tot">{{ Ag::e($f['total']) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <div class="pager"><span>La franja sombreada marca su horario oficial.</span></div>
</div>
@endsection
