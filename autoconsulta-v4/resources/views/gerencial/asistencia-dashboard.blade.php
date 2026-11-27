@extends('layouts.header-gerencial')

@section('content')
@php
    $meses = array(1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre');
    // Jerarquía: 1. URL (?year=) → 2. POST (seleccionAnio) → 3. Año actual (igual que antes)
    $anioActivo = isset($seleccionAnio) && $seleccionAnio ? $seleccionAnio : date('Y');
    $nrQ = Vista::q($legajo_coded);
@endphp

<div class="page-h">
    <div>
        <span class="eyebrow">Consulta de agente</span>
        <h1>Asistencia</h1>
        <p>Resumen de días y horas trabajadas por mes. Use “Ver” para abrir el calendario de ese mes.</p>
    </div>
</div>

@include('partials.nr-g-tabs-asistencia')

<div class="ncard">
    <form class="toolbar" action="{{ route('gerencial.asistencia.dashboard') }}" method="POST" name="formAnios" id="formAnios">
        @csrf
        <input type="hidden" name="legajo" value="{{ Vista::e($legajo_coded) }}">
        <label for="seleccionAnio" class="muted">Año</label>
        <select class="select" name="seleccionAnio" id="seleccionAnio" onchange="this.form.submit()" style="min-width:140px">
            <option value="" disabled>Seleccionar año</option>
            @for ($i = date('Y') - 1; $i <= date('Y'); $i++)
            <option value="{{ $i }}" {{ $anioActivo == $i ? 'selected' : '' }}>{{ $i }}</option>
            @endfor
        </select>
    </form>

    @if ($horas_trabajadas->isEmpty())
        <div class="empty">No se encontraron horas trabajadas para el año seleccionado.</div>
    @else
    <div class="table-wrap">
        <table class="t">
            <thead>
                <tr><th>Mes</th><th>Días trabajados</th><th>Días laborables</th><th style="width:22%">Presencia</th><th>Promedio diario</th><th>Total de horas</th><th></th></tr>
            </thead>
            <tbody>
                @if (count($horas_trabajadas) < 12)
                <tr>
                    <td><b>Mes en curso</b></td>
                    <td class="muted" colspan="5">Todavía no cerró.</td>
                    <td style="text-align:right"><a class="nbtn sm" href="{{ route('gerencial.asistencia.index') }}{{ $nrQ }}&year={{ date('Y') }}&month={{ date('m') }}">Ver</a></td>
                </tr>
                @endif
                @foreach ($horas_trabajadas as $horas)
                @php
                    $nrPct = $horas->dias_laborables > 0 ? round($horas->dias_trabajados / $horas->dias_laborables * 100) : 0;
                    $nrTono = $nrPct >= 90 ? 'ok' : ($nrPct >= 75 ? 'warn' : 'danger');
                @endphp
                <tr>
                    <td><b>{{ Vista::e($meses[$horas->mes]) }}</b></td>
                    <td class="mono">{{ Vista::e($horas->dias_trabajados) }}</td>
                    <td class="mono">{{ Vista::e($horas->dias_laborables) }}</td>
                    <td><div style="display:flex;align-items:center;gap:8px"><div class="meter {{ $nrTono == 'ok' ? '' : 'warn' }}" style="flex:1"><i style="width:{{ min(100, $nrPct) }}%"></i></div><span class="chip {{ $nrTono }}">{{ $nrPct }} %</span></div></td>
                    <td class="mono">{{ Vista::e($horas->promedio) }} h</td>
                    <td class="mono">{{ Vista::e($horas->total_horas) }} h</td>
                    <td style="text-align:right"><a class="nbtn sm" href="{{ route('gerencial.asistencia.index') }}{{ $nrQ }}&year={{ Vista::e($horas->anio) }}&month={{ Vista::e($horas->mes) }}">Ver</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pager"><span>Presencia: días trabajados sobre días laborables del mes.</span></div>
    @endif
</div>
@endsection
