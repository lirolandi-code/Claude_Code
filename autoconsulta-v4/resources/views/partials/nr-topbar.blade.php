{{-- Autoconsulta v4 · barra superior --}}
<header class="topbar">
    <button type="button" class="icon-btn menu-toggle" id="nr-menu" aria-label="Abrir menú"><svg class="i"><use xlink:href="#i-menu"></use></svg></button>
    <nav class="crumbs" aria-label="Ubicación">
        <a href="{{ route('general') }}">Autoconsulta</a>
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
        <button type="button" class="icon-btn" style="position:relative" data-bs-toggle="modal" data-bs-target="#observacionModal" aria-label="Observaciones">
            <svg class="i"><use xlink:href="#i-mail"></use></svg>
            <span id="notification-badge" class="count nr-obs-count" style="position:absolute;top:-2px;right:-4px;font-size:10px;line-height:16px;padding:0 5px;{{ $nrSinLeer > 0 ? '' : 'display:none' }}">{{ $nrSinLeer }}</span>
        </button>
        <button type="button" class="icon-btn" aria-label="Cambiar entre modo claro y oscuro" data-nr-tema><svg class="i"><use xlink:href="#i-moon"></use></svg></button>
        <div class="me"><img src="{{ Auth::user()->getImageAttribute() }}" alt=""><span class="me-txt">{{ Vista::e(auth()->user()->nombre) }}<small>Legajo {{ Vista::e($nrLegajo) }}</small></span></div>
    </div>
</header>
