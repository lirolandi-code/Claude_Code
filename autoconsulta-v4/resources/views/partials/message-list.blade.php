{{-- Lista de observaciones (se carga por AJAX dentro de la ventana "Observaciones"). Mismas consultas que antes. --}}
@foreach($observaciones as $observacion)
<div class="obs-item {{ $observacion->estado ? 'leida' : '' }}" id="message-{{ $observacion->id }}">
    <div>
        <b>{{ Vista::e(Novp05::where('codigo',$observacion->codigo)->first()->descripcion) }}</b>
        <div class="meta">{{ Vista::e($observacion->fecha) }} · {{ Vista::e(Migweba::where('A01A01',$observacion->creador_legajo)->first()->nombre) }}</div>
    </div>
    <div class="fechas">Desde {{ date('d/m/Y',strtotime($observacion->desde)) }}<br>Hasta {{ date('d/m/Y',strtotime($observacion->hasta)) }}</div>
    <p style="grid-column:1/-1">{{ Vista::e($observacion->texto) }}</p>
</div>
@endforeach
