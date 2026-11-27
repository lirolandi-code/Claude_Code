@extends('layouts.header-landing')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Datos personales</h1>
        <p>Si algún dato es incorrecto, comuníquese con Recursos Humanos.</p>
    </div>
</div>

@include('partials.nr-tabs-personal')

<div class="two">
    <div class="ncard">
        <div class="card-h"><h2>Información básica</h2></div>
        <div class="card-b" style="padding-block:4px">
            <dl class="dl">
                <dt>Legajo</dt><dd class="mono">{{ Vista::e($primario) }}</dd>
                <dt>Nombre</dt><dd>{{ Vista::e($usrpa01->A01A02) }}</dd>
                <dt>Documento</dt><dd class="mono">{{ Vista::e($usrpa01->A01A22) }}</dd>
                <dt>CUIL</dt><dd class="mono">{{ Vista::e($usrpo10->O10O74) }}</dd>
                <dt>Fecha de nacimiento</dt><dd class="mono">{{ Vista::e(Utilidades::convertirFecha($datosBasicos->O74A20)) }}</dd>
                <dt>Lugar de nacimiento</dt><dd>{{ Vista::e($datosBasicos->O74T69) }}</dd>
                <dt>Sexo</dt><dd>@if($datosBasicos->O74A23 == 'F') Femenino @else Masculino @endif</dd>
                <dt>Estado civil</dt><dd>{{ Vista::e(Tabpdat2::where('TABCOD', 'EST')->where('TABITE', $datosBasicos->O74A84)->first()->TABNOM) }}</dd>
                <dt>Nacionalidad</dt><dd>{{ Vista::e(Tabpdat2::where('TABCOD', 'NAA')->where('TABITE', $datosBasicos->O74T80)->first()->TABNOM) }}</dd>
                <dt>Nivel de estudios</dt><dd>{{ Vista::e($estudios->TABNOM) }}</dd>
                <dt>Título</dt><dd>{{ Vista::e($estudios->O87T40) }}</dd>
            </dl>
        </div>
    </div>

    <div class="ncard">
        <div class="card-h"><h2>Domicilio</h2></div>
        <div class="card-b" style="padding-block:4px">
            <dl class="dl">
                <dt>Calle</dt><dd>{{ Vista::e($domicilios->O63S02) }}</dd>
                <dt>Número</dt><dd class="mono">{{ Vista::e($domicilios->O63A25) }}</dd>
                <dt>Piso</dt><dd class="mono">{{ Vista::e($domicilios->O63A26) }}</dd>
                <dt>Departamento</dt><dd class="mono">{{ Vista::e($domicilios->O63A27) }}</dd>
                <dt>Entre calle 1</dt><dd>{{ Vista::e($domicilios->O63U08) }}</dd>
                <dt>Entre calle 2</dt><dd>{{ Vista::e($domicilios->O63U09) }}</dd>
                <dt>Código postal</dt><dd class="mono">{{ Vista::e($domicilios->O63S03) }}</dd>
                {{-- En Avellaneda es localidad, en el resto de los organismos es dom_localidad --}}
                <dt>Localidad</dt><dd>{{ Vista::e($domicilios->O63A28) }}</dd>
                <dt>Provincia</dt><dd>{{ Vista::e(Tabpdat2::where('TABCOD', 'PRO')->where('TABITE', $domicilios->O63A29)->first()->TABNOM) }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
