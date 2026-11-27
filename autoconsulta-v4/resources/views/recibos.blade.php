@extends('layouts.header-landing')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Haberes</h1>
        <p>Recibos de sueldo del año 20{{ Vista::e($anioSeleccion) }}. Puede verlos en pantalla o descargarlos.</p>
    </div>
    <div class="actions">
        @if (Migweba::where('A01A01', Auth::user()->primario()->first()->A01A01)->first()->form1359()->first())
        <a class="nbtn primary" href="{{ route('recibos.f1359') }}" data-bs-toggle="modal" data-bs-target="#pdfModal" onclick="loadPdf('{{ route('recibos.f1359') }}');"><svg class="i"><use xlink:href="#i-file"></use></svg>Formulario 1359</a>
        @endif
    </div>
</div>

<div class="ncard">
    <form class="toolbar" action="{{ route('recibos.show') }}" method="post" name="formAnios" id="formAnios">
        @csrf
        <label for="seleccionAnio" class="muted">Año</label>
        {{-- Al cambiar el año se envía el formulario (assets/js/datepicker.js, igual que antes) --}}
        <select class="select" name="seleccionAnio" id="seleccionAnio" style="min-width:140px">
            <option value=" " disabled>Seleccionar año</option>
            @foreach ($anios as $anio)
            <option value="{{ $anio->HABANIO }}" @if ($anio->HABANIO == $anioSeleccion) selected @endif>20{{ $anio->HABANIO }}</option>
            @endforeach
        </select>
    </form>

    @if ($paginatedLiq->isEmpty())
        <div class="empty">No se encontraron recibos para el año seleccionado.</div>
    @else
        @foreach ($paginatedLiq as $liquidacion)
        @php
            $tieneGanancias = false;
            foreach ($listadoGanancias as $ganancias) {
                if ($ganancias->HABLIQ == $liquidacion->HABLIQ) {
                    $tieneGanancias = true;
                    break;
                }
            }
        @endphp
        <div class="receipt">
            <span class="ri">{{ Vista::e(str_pad($liquidacion->HABMES, 2, '0', STR_PAD_LEFT)) }}<small>{{ Vista::e($liquidacion->HABANIO) }}</small></span>
            <div>
                <a class="title" href="{{ route('recibos.generarRecibo', $liquidacion->HABLIQ) }}" data-bs-toggle="modal" data-bs-target="#pdfModal" onclick="loadPdf('{{ route('recibos.generarRecibo', $liquidacion->HABLIQ, 0) }}');">{{ Vista::e($liquidacion->HABDES) }}</a>
                <small>Legajo {{ Vista::e($liquidacion->A04A01) }}</small>
            </div>
            <div class="hide-sm"><small>Período</small><div class="mono">{{ Vista::e($liquidacion->HABMES) }}/20{{ Vista::e($liquidacion->HABANIO) }}</div></div>
            <div class="hide-sm" style="grid-column:span 2">
                @if ($tieneGanancias)
                    <span class="chip info"><a href="{{ route('generate.dg', $liquidacion->HABLIQ) }}" data-bs-toggle="modal" data-bs-target="#pdfModal" onclick='loadPdf("{{ route("generate.dg", $liquidacion->HABLIQ) }}");'>Ganancias: ver detalle</a></span>
                @else
                    <span class="chip">Sin retención de Ganancias</span>
                @endif
            </div>
            <div style="display:flex;gap:2px">
                <a class="icon-btn" href="{{ route('recibos.generarRecibo', $liquidacion->HABLIQ) }}" data-bs-toggle="modal" data-bs-target="#pdfModal" onclick="loadPdf('{{ route('recibos.generarRecibo', $liquidacion->HABLIQ, 0) }}');" aria-label="Ver recibo"><svg class="i"><use xlink:href="#i-eye"></use></svg></a>
                <a class="icon-btn" href="{{ route('recibos.generarRecibo', $liquidacion->HABLIQ, 1) }}" aria-label="Descargar recibo"><svg class="i"><use xlink:href="#i-download"></use></svg></a>
            </div>
        </div>
        @endforeach
        <div class="pager"><span>Recibos del año 20{{ Vista::e($anioSeleccion) }}</span>{{ $paginatedLiq->appends(request())->links() }}</div>
    @endif
</div>

<!-- Visor de PDF (recibos, detalle de Ganancias y Formulario 1359) -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalLabel">Recibo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" style="padding:0">
                <div id="pdfLoader" style="display:flex;justify-content:center;align-items:center;height:560px;">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando…</span></div>
                </div>
                <iframe id="pdfFrame" src="" style="width:100%;height:560px;border:none;display:none;" onload="hideLoader()" title="Recibo"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection
