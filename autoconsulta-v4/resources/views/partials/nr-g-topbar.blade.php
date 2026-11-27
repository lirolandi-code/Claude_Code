{{-- Autoconsulta v4 · barra superior de la vista gerencial --}}
<header class="topbar">
    <button type="button" class="icon-btn menu-toggle" id="nr-menu" aria-label="Abrir menú"><svg class="i"><use xlink:href="#i-menu"></use></svg></button>
    <nav class="crumbs" aria-label="Ubicación">
        <span>Consulta de agente</span>
        <svg class="i"><use xlink:href="#i-chev"></use></svg>
        <span>{{ Vista::e($infoUsuario->A01A02) }}</span>
        <svg class="i"><use xlink:href="#i-chev"></use></svg>
        @if($nrPagTexto)
            <span>{{ $nrSecTexto }}</span>
            <svg class="i"><use xlink:href="#i-chev"></use></svg>
            <b>{{ $nrPagTexto }}</b>
        @else
            <b>{{ $nrSecTexto }}</b>
        @endif
    </nav>
    <div class="top-actions" style="margin-left:auto">
        <span class="chip info" style="white-space:nowrap">Modo consulta</span>
        <button type="button" class="icon-btn" aria-label="Cambiar entre modo claro y oscuro" data-nr-tema><svg class="i"><use xlink:href="#i-moon"></use></svg></button>
    </div>
</header>
