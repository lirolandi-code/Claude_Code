@extends('layouts.header-gerencial')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Consulta de agente</span>
        <h1>Asistencia</h1>
        <p>Toque un día o una novedad del calendario para ver el detalle.</p>
    </div>
    <div class="actions">
        <a class="nbtn" href="{{ route('gerencial.asistencia.dashboard') }}{{ Vista::q($legajo_coded) }}&year={{ Vista::e(isset($year) ? $year : date('Y')) }}"><svg class="i"><use xlink:href="#i-back"></use></svg>Volver al resumen por mes</a>
    </div>
</div>

@include('partials.nr-g-tabs-asistencia')

{{-- Los colores coinciden con los que devuelve el servicio del calendario --}}
<div class="refs">
    <span><i style="background:var(--color-novedad)"></i>Novedad asistencial</span>
    <span><i style="background:var(--color-novedad-pendiente)"></i>Pendiente de justificación</span>
    <span><i style="background:var(--color-novedad-injustificada)"></i>Injustificada / vencida</span>
    <span><i style="background:var(--color-novedad-descontada)"></i>Descontada</span>
    <span><i style="background:var(--color-fichada)"></i>Fichada</span>
    <span><i style="background:var(--color-franquicia)"></i>Franquicia horaria</span>
    <span><i class="sq" style="background:var(--color-situacion-particular)"></i>Situación particular</span>
    <span><i class="sq" style="background:var(--color-inconsistencia)"></i>Inconsistencia</span>
    <span><i class="sq" style="background:var(--color-error)"></i>Ausente sin informar</span>
</div>

<div class="ncard card-b">
    <div id="calendar"></div>
</div>

<div class="modal fade" id="dialog" title="Novedad">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header">
                              <h5 class="modal-title">Detalle de novedad</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p><span id="eventTitle"></span></p>
                                <p><strong>Descripcion de novedad:</strong> <span id="eventDescription"></span></p>
                                <p><span id="eventStart"></span></p>
                                <p><span id="eventEnd"></span></p>
                                <form id="form-actualizar" method="post" action="{{ route('rrhh.novedades.actualizar') }}" 
                                enctype="multipart/form-data">
                                    <input type="hidden" name="legajo" id="legajo" value="">
                                    <input type="hidden" name="novedad" id="novedad" value="">
                                    <input type="file" name="adjuntos[]" id="actualizar-adjuntos" multiple hidden>
                                    @csrf
                                    <label for="att_adjuntos">Archivos adjuntos</label>
                                    <div class="attachemnts-card p-2" id="att_adjuntos">
                                        <button class="btn btn-sm btn-plain upload-btn" type="button" id="att_btn" disabled>
                                            <i class="fa fa-plus mr-2 m-0 p-0" style="vertical-align:middle;"></i>Agregar archivo
                                        </button>
                                        <input name="adjuntos[]" id="adjuntos" type="file" class="file d-none" multiple
                                            accept=".csv, .xls, .xlsx, .doc, .docx, .png, .jpeg, .jpg, .txt, .zip, .rar, .csv, .pdf" />
                                        <div class="image-container mt-2" id="contenedor_adjuntos">
                                            {{-- LAS IMAGENES SE AGREGAN ACA CON JAVASCRIPT --}}
                                        </div>
                                    </div>
                                </form>
                              </div>
                            <div class="modal-footer">
                                <form action="{{route('rrhh.novedades.eliminar')}}" id="formElim" method="post">
                                    <input type="hidden" name="legajo" id="legajoDel" value="">
                                    <input type="hidden" name="novedad" id="novedadDel" value="">
                                    <input class="btn btn-danger" type="submit" value="Borrar" />
                                    @method('delete')
                                    @csrf
                                </form>
                                <input class="btn btn-primary" type="submit" id="actualizar-archivos" value="Actualizar" onclick="confirmarAccion()"/>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                            </div>
                            </div>
                        </div>
                    
                    <!-- Modal -->
                    <div class="modal fade" id="dynamicModal" tabindex="-1" aria-labelledby="dynamicModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h5 class="modal-title" id="dynamicModalLabel">Carga de licencia</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                            <!-- Content will be loaded here dynamically -->
                            <div id="modalContent"></div>
                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                        </div>
                    </div>
@endsection
