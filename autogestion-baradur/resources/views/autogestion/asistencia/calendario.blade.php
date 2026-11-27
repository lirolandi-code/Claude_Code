@extends('autogestion.layouts.app')

@section('contenido')
@include('autogestion.partials.asistencia_cabecera')
@include('autogestion.partials.leyenda')

<div class="card" style="overflow:hidden">
    <div class="mcal">
        <div class="h">dom</div><div class="h">lun</div><div class="h">mar</div><div class="h">mié</div><div class="h">jue</div><div class="h">vie</div><div class="h">sáb</div>
        @foreach($semanas as $semana)
            @foreach($semana as $c)
                @if($c === null)
                <div class="c out"></div>
                @else
                <div class="c {{ Ag::e($c['clases']) }}">
                    <span class="dn"><b>{{ (int) $c['dia'] }}</b></span>
                    @if($c['feriado'])
                    <span class="pill fer">Feriado</span>
                    <small class="muted" style="font-size:11px;line-height:1.3">{{ Ag::e($c['feriado']) }}</small>
                    @endif
                    @if($c['horasTexto'] !== '')
                    <span class="hrs">{{ Ag::e($c['horasTexto']) }}</span>
                    @endif
                    @foreach($c['etiquetas'] as $et)
                    <span class="pill {{ Ag::e($et['clase']) }}" title="{{ Ag::e($et['texto']) }}">{{ Ag::e($et['texto']) }}</span>
                    @endforeach
                </div>
                @endif
            @endforeach
        @endforeach
    </div>
</div>
@endsection
