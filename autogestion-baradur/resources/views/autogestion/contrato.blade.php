@extends('autogestion.layouts.app')

@section('contenido')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Contrato</h1>
        <p>Su situación de revista y lugar de trabajo.</p>
    </div>
</div>

<div class="two">
    <div class="card">
        <div class="card-h"><h2>Situación de revista</h2><span class="chip ok">Vigente</span></div>
        <div class="card-b" style="padding-block:4px">
            <dl class="dl">
                @foreach($revista as $f)
                <dt>{{ Ag::e($f[0]) }}</dt><dd class="@if($f[2]) mono @endif">{{ Ag::e($f[1]) }}</dd>
                @endforeach
            </dl>
        </div>
    </div>
    <div class="card">
        <div class="card-h"><h2>Lugar de trabajo</h2></div>
        <div class="card-b" style="padding-block:4px">
            <dl class="dl">
                @foreach($lugar as $f)
                <dt>{{ Ag::e($f[0]) }}</dt><dd class="@if($f[2]) mono @endif">{{ Ag::e($f[1]) }}</dd>
                @endforeach
            </dl>
        </div>
    </div>
</div>
@endsection
