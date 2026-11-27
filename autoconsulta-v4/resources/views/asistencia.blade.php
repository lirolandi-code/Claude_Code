@extends('layouts.header-landing')

@section('content')
<div class="page-h">
    <div>
        <span class="eyebrow">Mi información</span>
        <h1>Asistencia</h1>
        <p>Toque un día o una novedad del calendario para ver el detalle.</p>
    </div>
    <div class="actions">
        <button type="button" class="nbtn" data-bs-toggle="modal" data-bs-target="#modalReclamo" title="Enviar reclamo sobre esta asistencia"><svg class="i"><use xlink:href="#i-alert"></use></svg>Reclamar inconsistencia</button>
        <button type="button" class="nbtn primary" data-bs-toggle="modal" data-bs-target="#dynamicModal"><svg class="i"><use xlink:href="#i-plus"></use></svg>Solicitud de licencia</button>
    </div>
</div>

@include('partials.nr-tabs-asistencia')

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

                    {{-- MODAL DE RECLAMO --}}
                    <div class="modal fade" id="modalReclamo" tabindex="-1" aria-labelledby="modalReclamoLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="formReclamo" method="POST" action="{{ route('envioReclamo') }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalReclamoLabel">Reclamar una inconsistencia</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        {{-- Datos ocultos del usuario --}}
                                        <input type="hidden" name="dni" value="{{auth()->user()->dni}}">
                                        <input type="hidden" name="nombre" value="{{auth()->user()->nombre}}">
                                        <input type="hidden" name="email" value="{{auth()->user()->email}}">
                                        <input type="hidden" name="legajo" value="{{$legajo}}">
                                        <input type="hidden" name="mailFrom" value="{{auth()->user()->email}}"> {{-- Cambiá por el email real --}}
                                        
                                        {{-- Motivo del reclamo --}}
                                        <div class="mb-3">
                                            <label for="motivo" class="form-label fw-bold">Motivo del reclamo *</label>
                                            <select class="form-select" id="motivo" name="motivo" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="Fichada incorrecta">Fichada inconsistencia</option>
                                            </select>
                                        </div>
                                        
                                        {{-- Descripción detallada --}}
                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label fw-bold">Descripción detallada *</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                                    placeholder="Describí el problema con la mayor claridad posible..." required></textarea>
                                            <small class="text-muted">Incluí fechas, horarios o referencias si es necesario.</small>
                                        </div>

                                        <div class="mb-3 p-3 border rounded bg-light">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="adjuntarBanda" name="adjuntar_banda" value="1">
                                            <label class="form-check-label fw-bold" for="adjuntarBanda">
                                                📎 Adjuntar banda horaria del mes de la inconsistencia
                                            </label>
                                        </div>
                                        
                                        <div id="bandaOptions" class="row g-2 d-none">
                                            <div class="col-6">
                                                <label for="bandaAnio" class="form-label small mb-1">Año</label>
                                                <select class="form-select form-select-sm" id="bandaAnio" name="banda_anio">
                                                    @for ($i = date('Y') - 1; $i <= date('Y'); $i++)
                                                        <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label for="bandaMes" class="form-label small mb-1">Mes</label>
                                                <select class="form-select form-select-sm" id="bandaMes" name="banda_mes">
                                                    <option value="1">Enero</option>
                                                    <option value="2">Febrero</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Abril</option>
                                                    <option value="5">Mayo</option>
                                                    <option value="6">Junio</option>
                                                    <option value="7">Julio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Septiembre</option>
                                                    <option value="10">Octubre</option>
                                                    <option value="11">Noviembre</option>
                                                    <option value="12" selected>Diciembre</option>
                                                </select>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-2" id="bandaStatus"></small>
                                    </div>
                                        
                                        {{-- Mensaje de estado --}}
                                        <div id="reclamoMensaje" class="alert d-none" role="alert"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-warning" id="btnEnviarReclamo">
                                            <span id="btnText">Enviar Reclamo</span>
                                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
@endsection
