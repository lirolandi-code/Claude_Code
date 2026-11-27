@if($flash)
<div class="flash {{ $flash['tipo'] == 'ok' ? 'ok' : 'danger' }}" role="status">
    <svg class="i"><use xlink:href="#i-{{ $flash['tipo'] == 'ok' ? 'check' : 'alert' }}"></use></svg>
    <span>{{ Ag::e($flash['texto']) }}</span>
</div>
@endif
