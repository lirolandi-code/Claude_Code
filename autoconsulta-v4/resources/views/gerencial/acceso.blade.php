@extends('layouts.header-gerencial')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Consulta de agente</span>
        <h1>Asistencia</h1>
        <p>Registros de los molinetes y salidas autorizadas.</p>
    </div>
    <div class="actions">
        <a class="nbtn" href="{{ route('gerencial.asistencia.dashboard') }}{{ Vista::q($legajo_coded) }}&year={{ Vista::e(isset($year) ? $year : date('Y')) }}"><svg class="i"><use xlink:href="#i-back"></use></svg>Volver al resumen por mes</a>
    </div>
</div>

@include('partials.nr-g-tabs-asistencia')

<div class="ncard" style="margin-bottom:20px">
    <div class="card-h"><h2>Accesos</h2></div>
    @if (!$accesos->isEmpty())
    <div class="table-wrap">
        <table class="t">
            <thead><tr><th>Fecha</th><th>Hora</th><th>Tipo</th><th>Molinete</th></tr></thead>
            <tbody>
                @foreach ($accesos as $acceso)
                <tr>
                    <td class="mono">{{ Vista::e($acceso->FECHA) }}</td>
                    <td class="mono">{{ Vista::e($acceso->HORA) }}</td>
                    <td>{{ Vista::e($acceso->TIPO) }}</td>
                    <td>{{ Vista::e($acceso->MOLINETE) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pager"><span>Registros de acceso</span>{{ $accesos->appends(request()->input())->links() }}</div>
    @else
    <div class="empty">No hay accesos para mostrar.</div>
    @endif
</div>

<div class="ncard">
    <div class="card-h"><h2>Salidas autorizadas</h2></div>
    @if (!$salidas->isEmpty())
    <div class="table-wrap">
        <table class="t">
            <thead><tr><th>Fecha</th><th>Hora desde</th><th>Hora hasta</th><th>Tipo</th></tr></thead>
            <tbody>
                @foreach ($salidas as $salida)
                <tr>
                    <td class="mono">{{ Vista::e($salida->FECHA) }}</td>
                    <td class="mono">{{ Vista::e($salida->HORA_DESDE) }}</td>
                    <td class="mono">{{ Vista::e($salida->HORA_HASTA) }}</td>
                    <td>{{ Vista::e($salida->TIPO) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty">No hay salidas autorizadas para mostrar.</div>
    @endif
</div>
@endsection
