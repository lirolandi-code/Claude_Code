<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Asistencia</h1>
        <p>{{ Ag::e($periodoTexto) }}@if($horario) · horario oficial {{ Ag::e($horario) }}@endif</p>
    </div>
    <div class="actions">
        <a class="btn" href="{{ Ag::url('solicitud/reclamo') }}"><svg class="i"><use xlink:href="#i-alert"></use></svg>Reclamar inconsistencia</a>
        <a class="btn primary" href="{{ Ag::url('solicitud/licencia') }}"><svg class="i"><use xlink:href="#i-plus"></use></svg>Solicitar licencia</a>
    </div>
</div>

<div class="bar-row">
    <div class="seg">
        <a href="{{ Ag::url('asistencia?mes=' . $periodo) }}" class="@if($pestana == 'calendario') on @endif">Calendario</a>
        <a href="{{ Ag::url('asistencia/bandas?mes=' . $periodo) }}" class="@if($pestana == 'bandas') on @endif">Bandas horarias</a>
        <a href="{{ Ag::url('asistencia/licencias') }}" class="@if($pestana == 'licencias') on @endif">Licencias</a>
        <a href="{{ Ag::url('asistencia/accesos?mes=' . $periodo) }}" class="@if($pestana == 'accesos') on @endif">Accesos</a>
    </div>
    @if($pestana != 'licencias')
    <div style="display:flex;align-items:center;gap:6px">
        <a class="icon-btn" href="{{ Ag::url('asistencia' . ($pestana == 'calendario' ? '' : '/' . $pestana) . '?mes=' . $anterior) }}" aria-label="Mes anterior"><svg class="i"><use xlink:href="#i-back"></use></svg></a>
        <b style="font-family:var(--f-display)">{{ Ag::e($periodoTexto) }}</b>
        @if(!$esMesActual)
        <a class="icon-btn" href="{{ Ag::url('asistencia' . ($pestana == 'calendario' ? '' : '/' . $pestana) . '?mes=' . $siguiente) }}" aria-label="Mes siguiente"><svg class="i"><use xlink:href="#i-chev"></use></svg></a>
        @endif
    </div>
    @endif
</div>
