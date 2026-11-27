<header class="topbar">
    <button type="button" class="icon-btn menu-toggle" id="menuBtn" aria-label="Abrir menú"><svg class="i"><use xlink:href="#i-menu"></use></svg></button>
    <nav class="crumbs" aria-label="Ubicación">
        <a href="{{ Ag::url('inicio') }}">Autogestión</a>
        @foreach($migas as $i => $miga)
            <svg class="i"><use xlink:href="#i-chev"></use></svg>
            @if($i == count($migas) - 1)
                <b>{{ Ag::e($miga) }}</b>
            @else
                <span>{{ Ag::e($miga) }}</span>
            @endif
        @endforeach
    </nav>
    <div class="top-actions" style="margin-left:auto">
        <a class="icon-btn @if($sinLeer > 0) dot @endif" href="{{ Ag::url('mensajes') }}" aria-label="Mensajes"><svg class="i"><use xlink:href="#i-mail"></use></svg></a>
        <button type="button" class="icon-btn" aria-label="Cambiar tema" data-tema><svg class="i"><use xlink:href="#i-moon"></use></svg></button>
        <div class="me"><span class="avatar" style="background:#1d5ca8">{{ Ag::e($iniciales) }}</span><span class="me-txt">{{ Ag::e($nombreCorto) }}<small>Legajo {{ Ag::e($empleado['legajo']) }}</small></span></div>
    </div>
</header>
