@extends('layouts.header-gerencial')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Consulta de agente</span>
        <h1>Carrera</h1>
        <p>Cursos realizados y créditos obtenidos.</p>
    </div>
</div>

@include('partials.nr-g-tabs-carrera')

<div class="ncard">
    <div class="card-h"><h2>Cursos realizados</h2></div>
    @if (!$cursos->isEmpty())
    <div class="table-wrap">
        <table class="t">
            <thead><tr><th>Curso</th><th>Código</th><th>Fecha</th><th>Créditos</th></tr></thead>
            <tbody>
                @foreach ($cursos as $curso)
                <tr>
                    <td style="white-space:normal;min-width:240px"><b style="font-weight:600">{{ Vista::e($curso->CURDES) }}</b></td>
                    <td><span class="tag">{{ Vista::e($curso->O71S74) }}</span></td>
                    <td class="mono">{{ Vista::e(Utilidades::convertirFecha($curso->O71S31)) }}</td>
                    <td class="mono">{{ Vista::e($curso->CURCRE) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pager"><span>Cursos</span>{{ $cursos->appends(request()->input())->links() }}</div>
    @else
    <div class="empty">No hay cursos para mostrar.</div>
    @endif
</div>
@endsection
