@extends('layouts.header-landing')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Contrato</h1>
        <p>Su último contrato registrado.</p>
    </div>
</div>

<div class="ncard" style="margin-bottom:20px">
    <div class="card-h"><h2>Contrato</h2></div>
    <div class="table-wrap" id="divContrato">
        <table id="contrato" class="t">
            <thead><tr><th>Tipo de contrato</th><th>Fecha de inicio</th><th>Fecha de fin</th><th style="text-align:right">Descargar</th></tr></thead>
            <tbody><tr><td colspan="4" class="muted">Cargando…</td></tr></tbody>
        </table>
    </div>
    <div class="empty" id="sinContrato" style="display:none">No se encontraron contratos.</div>
</div>

@if (!$autorizacion->isEmpty())
<div class="ncard" style="overflow:hidden">
    <div class="card-h"><h2>Manejo de expedientes</h2></div>
    <iframe class="frame" src=" https://sv3000-prod.mecon.gob.ar/expedientes/api/responsable/{{$legajo}}" height="800" title="Manejo de expedientes"></iframe>
</div>
@endif

<script>
    // Misma consulta que la versión anterior; solo cambia cómo se muestra el resultado.
    document.addEventListener('DOMContentLoaded', function () {
        hacerPeticionAutomatica();
    });

    function nrEscapar(texto) {
        var d = document.createElement('div');
        d.textContent = texto == null ? '' : String(texto);
        return d.innerHTML;
    }

    function hacerPeticionAutomatica() {
        const legajo = @json($legajo);
        var url = 'https://sv3000.mecon.gob.ar/expedientes/api/ultimocontrato/' + legajo;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#contrato tbody');
                tbody.innerHTML = '';
                if (data.contrato === '') {
                    document.getElementById('divContrato').style.display = 'none';
                    document.getElementById('sinContrato').style.display = 'block';
                    return;
                }
                const descarga = (data.descarga && !/^\s*javascript:/i.test(data.descarga)) ? data.descarga : '#';
                tbody.innerHTML = `
                    <tr>
                        <td>${nrEscapar(data.desc_expediente || '')}</td>
                        <td class="mono">${nrEscapar(data.inicio || '')}</td>
                        <td class="mono">${nrEscapar(data.fin || '')}</td>
                        <td style="text-align:right">
                            <a class="icon-btn" href="${nrEscapar(descarga)}" target="_blank" rel="noopener" aria-label="Descargar contrato">
                                <svg class="i"><use xlink:href="#i-download"></use></svg>
                            </a>
                        </td>
                    </tr>`;
            })
            .catch(error => {
                console.error('Error:', error);
                document.querySelector('#contrato tbody').innerHTML = '<tr><td colspan="4">No se pudo consultar el contrato. Intente de nuevo más tarde.</td></tr>';
            });
    }
</script>
@endsection
