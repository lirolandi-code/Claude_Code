@extends('autogestion.layouts.app')

@section('contenido')
@include('autogestion.partials.asistencia_cabecera')

<div class="card">
    @if(count($filas))
    <div class="table-wrap">
        <table class="t">
            <thead><tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Reloj</th><th>Total</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach($filas as $f)
                <tr>
                    <td><span class="muted" style="text-transform:capitalize">{{ Ag::e($f['diaCorto']) }}</span> <span class="mono">{{ Ag::e($f['fecha']) }}</span></td>
                    <td class="mono">{{ Ag::e($f['entrada']) }}</td>
                    <td class="mono">{{ Ag::e($f['salida']) }}</td>
                    <td>{{ Ag::e($f['reloj']) }}</td>
                    <td class="mono">{{ Ag::e($f['total']) }}</td>
                    <td><span class="chip {{ Ag::e($f['estado'][0]) }}">{{ Ag::e($f['estado'][1]) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty">No hay fichadas registradas en {{ Ag::e($periodoTexto) }}.</div>
    @endif
</div>
@endsection
