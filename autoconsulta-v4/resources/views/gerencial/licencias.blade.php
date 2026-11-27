@extends('layouts.header-gerencial')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Consulta de agente</span>
        <h1>Asistencia</h1>
        <p>Días de licencia ordinaria disponibles y licencias tomadas en el año.</p>
    </div>
    <div class="actions">
        <a class="nbtn" href="{{ route('gerencial.asistencia.dashboard') }}{{ Vista::q($legajo_coded) }}&year={{ Vista::e(isset($year) ? $year : date('Y')) }}"><svg class="i"><use xlink:href="#i-back"></use></svg>Volver al resumen por mes</a>
    </div>
</div>

@include('partials.nr-g-tabs-asistencia')

<div class="two">
    <div class="ncard">
        <div class="card-h"><h2>Licencias ordinarias</h2></div>
        <div class="table-wrap">
            <table class="t">
                <thead><tr><th>Año</th><th>Días otorgados</th><th>Días disponibles</th></tr></thead>
                <tbody>
                    @foreach ($vacaciones as $licenciaOrdinaria)
                    <tr>
                        <td class="mono">{{ Vista::e($licenciaOrdinaria->P33W69) }}</td>
                        <td class="mono">{{ Vista::e($licenciaOrdinaria->P33W83) }}</td>
                        <td><b class="mono" style="font-size:15px">{{ Vista::e($licenciaOrdinaria->P33W26) }}</b></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="ncard">
        <div class="card-h"><h2>Licencias tomadas este año</h2></div>
        <ul class="todo">
            @if ($totalSaldos->isEmpty())
                <li><div class="grow"><b>No tomó licencias este año</b></div><span class="tag">0</span></li>
            @else
                @foreach ($totalSaldos as $saldos)
                <li><div class="grow"><b>{{ Vista::e($saldos->descripcion) }}</b></div><span class="tag">{{ Vista::e($saldos->P20SDO) }}</span></li>
                @endforeach
            @endif
        </ul>
    </div>
</div>
@endsection
