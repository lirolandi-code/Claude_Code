<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Autoconsulta</title>
    <link rel="shortcut icon" href="{{asset('/assets/favicon.ico'). '?v=' . date('dmYhis')}}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        {{-- v4: aplica el tema antes de dibujar para evitar un destello --}}
        try { var nrT = localStorage.getItem('nr_tema'); if (nrT) { document.documentElement.setAttribute('data-theme', nrT); document.documentElement.setAttribute('data-bs-theme', nrT); } } catch (e) {}
    </script>
    <script src="{{ asset('assets/js/jquery-3.5.1.min.js') . '?v=' . date('dmYhis')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.css" rel="stylesheet"/>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Public+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap">
    <link href="{{ asset('assets/css/imageupload.css') }}" rel="stylesheet" />
    {{-- v4: estilos del diseño nuevo (van después de Bootstrap para poder adaptarlo) --}}
    <link href="{{ asset('assets/css/newrol.css') . '?v=' . date('dmYhis') }}" rel="stylesheet">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const anioInicial = {{ $anio ?? 'parseInt(urlParams.get("year")) || new Date().getFullYear()' }};
            const mesInicial = {{ $mes ?? 'parseInt(urlParams.get("month")) || (new Date().getMonth() + 1)' }};
            var legajo = {{ Auth::user()->primario()->first()->A01A01 }};
            var calendarEl = document.getElementById('calendar');
            if (!calendarEl) { return; } // v4: solo en la pantalla de calendario
            var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            buttonText: {
                today: 'hoy',
                month: 'mes',
                week: 'semana',
                day: 'día',
                list: 'lista'
            },
            headerToolbar: {
              left: 'prev,next',
              center: 'title',
              right: 'dayGridMonth'
            },
            navLinks: true, // can click day/week names to navigate views
            editable: false,
            displayEventTime: true,
            handleWindowResize: true,
            dayMaxEventRows: true, // for all non-TimeGrid views
            views: {
                dayGridMonth: {
                dayMaxEventRows: 3 // adjust to 6 only for timeGridWeek/timeGridDay
                }
            },
            // Estilo para días de fin de semana
            dayCellDidMount: function(info) {
                var day = info.date.getDay(); // 0 = Domingo, 6 = Sábado
                if (day === 0 || day === 6) {
                    info.el.style.backgroundColor = '#e0e0e0'; // Gris claro
                }
            },
           eventSources: [
                {
                    url: ' https://sv3000-prod.mecon.gob.ar/Calendario/api/novedades/' + legajo, // use the `url` property
                    textColor: 'black',  // an option!
                },
                {
                    url: ' https://sv3000-prod.mecon.gob.ar/Calendario/api/pendientes/' + legajo, // use the `url` property
                    textColor: 'black', // an option!
                },
                {
                    url: ' https://sv3000-prod.mecon.gob.ar/Calendario/api/feriados/' + legajo,
                     // use the `url` property
                    textColor: 'black',
                    allDay : true
                },
                {
                    url: ' https://sv3000-prod.mecon.gob.ar/Calendario/api/fichadas/' + legajo,
                     // use the `url` property
                    textColor: 'black'
                },
                {
                    url: ' https://sv3000-prod.mecon.gob.ar/Calendario/api/situacionesParticulares/' + legajo,
                     // use the `url` property
                    textColor: 'black',
                    totalizador: false
                }
            ],
            // events: [
            //     {
            //         start: '2025-02-04',
            //         end: '2025-02-04',
            //         display: 'background',
            //         color: "#8b1e2f",
            //     }
            // ],
            eventClick: function (info) {
                var date = info.event.start;
                var day = date.getDay();
                if (day === 0 || day === 6) {
                    // Es fin de semana, no hacer nada
                    return;
                }

                $("#dialog").modal({
                    autoOpen: false,
                    draggable: false,
                });
                const start = new Date(info.event.start).toLocaleDateString() + ' ' + new Date(info.event.start).toLocaleTimeString();
                const startFichada = new Date(info.event.start).toLocaleDateString();
                const end = info.event.end ? new Date(info.event.end).toLocaleDateString() + ' ' + new Date(info.event.end).toLocaleTimeString() : info.event.end;
                console.log(info.event)
                if(info.event.extendedProps.totalizador)
                {
                    document.getElementById("eventTitle").innerHTML = "<strong>Total de horas:</strong> " + info.event.title;
                    document.getElementById("eventStart").innerHTML = "<strong>Fecha:</strong> " + startFichada;
                    document.getElementById("eventDescription").innerText = info.event.extendedProps.descripcion || 'Total de horas realizadas en el dia';
                    document.getElementById("eventEnd").innerHTML = "";
                    document.getElementById("form-actualizar").style.display = 'none';
                }
                else
                {
                    document.getElementById("eventTitle").innerHTML = "<strong>Codigo de novedad:</strong> " +info.event.title;
                    document.getElementById("eventStart").innerHTML = "<strong>Fecha desde:</strong> " + start;
                    document.getElementById("eventEnd").innerHTML = "<strong>Fecha hasta:</strong> " + end || "<strong>Fecha hasta:</strong>" + 'no se especifico';
                    document.getElementById("eventDescription").innerText = info.event.extendedProps.descripcion || 'sin descripcion';
                }
                if(info.event.extendedProps.adjuntos) {
                    console.log('entre');
                    document.getElementById("form-actualizar").style.display = 'block';
                    document.getElementById("adjuntos").style.display = 'block';
                    cargarArchivosAdjuntos(info.event.extendedProps.adjuntos);
                } else {
                    document.getElementById("adjuntos").style.display = 'none';
                    borrarArchivosAdjuntos();
                }
                if(info.event.backgroundColor == "#000000" && info.event.extendedProps.usuario == 'AUT') {
                    console.log(info.event.extendedProps.usuario);
                    document.getElementById("formElim").style.display = 'block';
                    document.getElementById("actualizar-archivos").style.display = 'block';
                    document.getElementById("att_btn").disabled = false;
                    document.getElementById("legajo").value = info.event.extendedProps.legajo;
                    document.getElementById("legajoDel").value = info.event.extendedProps.legajo;
                    console.log(info.event.extendedProps.legajo);
                    document.getElementById("novedad").value = info.event.extendedProps.novedad;
                    document.getElementById("novedadDel").value = info.event.extendedProps.novedad;
                    console.log(info.event.extendedProps.novedad);
                } else {
                    document.getElementById("att_btn").disabled = true;
                    document.getElementById("formElim").style.display = 'none';
                    document.getElementById("actualizar-archivos").style.display = 'none';
                }
                var eventModal = new bootstrap.Modal(document.getElementById('dialog'), {});
                eventModal.show();
            },
            navLinkDayClick: function(date, jsEvent) {
                const day = date.getDay();
                if (day === 0 || day === 6) return;
                calendar.changeView('dayGrid', date);
            },
            datesSet: function(info) {
                const currentDate = info.view.currentStart;
                const year = currentDate.getFullYear();
                const month = String(currentDate.getMonth() + 1).padStart(2, '0');

                // Construir URL con query params
                const urlBandas = "{{ route('asistencia.bandasHorarias') }}?year=" + year + "&month=" + month;
                const urlaccesos = "{{ route('asistencia.accesos') }}?year=" + year + "&month=" + month;

                // Actualizar el enlace del tab
                document.querySelectorAll('.bandas-link').forEach(function (a) { a.href = urlBandas; });
                document.querySelectorAll('.accesos-link').forEach(function (a) { a.href = urlaccesos; });

            // Este hook se llama después de que la vista cambia
            // Podemos forzar la recarga de eventos si es necesario
            if (info.view.type === 'dayGrid') {
                calendar.refetchEvents(); // Esto fuerza una nueva petición al backend
            }
        },
        // Agregar esto para manejar después de que se carguen todos los eventos
            loading: function(isLoading) {
                if (!isLoading) {
                    // Cuando termina de cargar, esperar un poco más y luego agregar backgrounds
                        
                    setTimeout(() => {
                        agregarBackgroundDiasSinEventos(calendar);
                    }, 500);
                }
            }
        });
        calendar.render();
        calendar.updateSize()
      });

      function agregarBackgroundDiasSinEventos(calendar) {

        const COLOR_BACKGROUND = '#ad1111';
        const MS_DIA = 86400000;

        const eventos = calendar.getEvents();

        /* =========================
        1. Limpiar backgrounds propios
        ========================= */

        eventos.forEach(e => {
            if (e.extendedProps?.generatedBySystem) {
                e.remove();
            }
        });

        /* =========================
        2. Rango visible
        ========================= */

        let startView = new Date(calendar.view.currentStart);
        let endView   = new Date(calendar.view.currentEnd);

        const hoy = new Date();
        hoy.setDate(hoy.getDate() - 1);
        hoy.setHours(0,0,0,0);

        if (endView > hoy) {
            endView = new Date(hoy);
            endView.setDate(endView.getDate() + 1);
        }

        /* =========================
        3. Indexar días ocupados
        ========================= */

        const diasOcupados = new Set();

        eventos.forEach(evento => {

            if (evento.extendedProps?.generatedBySystem) return;

            let start = new Date(evento.start);
            let end   = evento.end ? new Date(evento.end) : new Date(evento.start);

            start.setHours(0,0,0,0);
            end.setHours(0,0,0,0);

            // Ajuste allDay
            if (evento.allDay && evento.end) {
                end.setDate(end.getDate() - 1);
            }

            // Si no tiene end → solo start
            if (!evento.end) {
                if (esLaborable(start)) {
                    diasOcupados.add(start.getTime());
                }
                return;
            }

            // Limitar rango al visible
            if (end < startView || start > endView) return;

            let cursor = new Date(start);

            while (cursor <= end) {

                if (cursor >= startView && cursor < endView) {
                    if (esLaborable(cursor)) {
                        diasOcupados.add(cursor.getTime());
                    }
                }

                cursor.setDate(cursor.getDate() + 1);
            }
        });

        /* =========================
        4. Crear backgrounds faltantes
        ========================= */

        let cursor = new Date(startView);

        while (cursor < endView) {

            if (esLaborable(cursor)) {

                const key = cursor.getTime();

                if (!diasOcupados.has(key)) {

                    calendar.addEvent({
                        start: new Date(cursor),
                        end: new Date(cursor.getTime() + MS_DIA),
                        display: 'background',
                        backgroundColor: COLOR_BACKGROUND,
                        allDay: true,
                        extendedProps: {
                            generatedBySystem: true
                        }
                    });
                }
            }

            cursor.setDate(cursor.getDate() + 1);
        }


        function esLaborable(fecha) {
            const d = fecha.getDay();
            return d !== 0 && d !== 6;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        
        // ========================================
        // 🔧 CONFIGURACIÓN Y SELECTORES
        // ========================================
        const CONFIG = {
            modalId: 'modalReclamo',
            formId: 'formReclamo',
            btnEnviarId: 'btnEnviarReclamo',
            btnTextId: 'btnText',
            btnSpinnerId: 'btnSpinner',
            mensajeId: 'reclamoMensaje',
            chkBandaId: 'adjuntarBanda',
            bandaOptionsId: 'bandaOptions',
            bandaStatusId: 'bandaStatus',
            bandaAnioId: 'bandaAnio',
            bandaMesId: 'bandaMes',
            apiBandasUrl: ' https://sv3000-prod.mecon.gob.ar/novedades/api/pdf-bandas-horarias'
        };
        
        const $ = (sel) => document.getElementById(sel);
        const $$ = (sel) => document.querySelector(sel);
        
        // ========================================
        // TOGGLE: MOSTRAR/OCULTAR OPCIONES DE BANDA
        // ========================================
        function initBandaToggle() {
            const chk = $(CONFIG.chkBandaId);
            const options = $(CONFIG.bandaOptionsId);
            const status = $(CONFIG.bandaStatusId);
            
            if (!chk || !options) return;
            
            chk.addEventListener('change', function() {
                const checked = this.checked;
                options.classList.toggle('d-none', !checked);
                
                if (!checked) {
                    status.textContent = '';
                    status.classList.remove('text-success', 'text-danger');
                    // Remover campo oculto si existe
                    const hidden = document.getElementById('banda_pdf_path');
                    if (hidden) hidden.remove();
                }
            });
        }
        
        // ========================================
        // FEEDBACK: PREVISUALIZAR SELECCIÓN DE FECHA
        // ========================================
        function initBandaFeedback() {
            const chk = $(CONFIG.chkBandaId);
            const anioSel = $(CONFIG.bandaAnioId);
            const mesSel = $(CONFIG.bandaMesId);
            const status = $(CONFIG.bandaStatusId);
            const legajoInput = $$('input[name="legajo"]');
            
            if (!chk || !anioSel || !mesSel) return;
            
            function updateFeedback() {
                if (!chk.checked) return;
                
                const anio = anioSel.value;
                const mes = mesSel.value;
                const legajo = legajoInput?.value || 'XXXX';
                
                if (anio && mes) {
                    const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                    status.innerHTML = `📄 Se adjuntará: <strong>banda_${legajo}_${meses[mes-1]}${anio}.pdf</strong>`;
                    status.classList.remove('text-danger');
                    status.classList.add('text-success');
                } else {
                    status.textContent = '⚠️ Seleccioná año y mes';
                    status.classList.remove('text-success');
                    status.classList.add('text-danger');
                }
            }
            
            anioSel.addEventListener('change', updateFeedback);
            mesSel.addEventListener('change', updateFeedback);
            // Inicializar
            if (chk.checked) updateFeedback();
        }
        
        // ========================================
        // UTILS: UI FEEDBACK
        // ========================================
        function setLoading(loading) {
            const btn = $(CONFIG.btnEnviarId);
            const text = $(CONFIG.btnTextId);
            const spinner = $(CONFIG.btnSpinnerId);
            
            if (btn) btn.disabled = loading;
            if (text) text.textContent = loading ? 'Enviando...' : 'Enviar Reclamo';
            if (spinner) spinner.classList.toggle('d-none', !loading);
        }
        
        function mostrarMensaje(texto, tipo) {
            const el = $(CONFIG.mensajeId);
            if (!el) return;
            
            el.textContent = texto;
            el.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
            el.classList.add('alert', `alert-${tipo}`);
            
            // Auto-ocultar mensajes de éxito después de 3s
            if (tipo === 'success') {
                setTimeout(() => {
                    el.classList.add('d-none');
                }, 3000);
            }
        }
        
        // ========================================
        //API: OBTENER RUTA DEL PDF DE BANDAS
        // ========================================
        async function obtenerRutaBandaHoraria(legajo, anio, mes) {
            try {
                const url = `${CONFIG.apiBandasUrl}?legajo=${encodeURIComponent(legajo)}&a=${encodeURIComponent(anio)}&m=${encodeURIComponent(mes)}`;
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                        // Si requiere auth: 'Authorization': 'Bearer {{ auth()->user()->api_token ?? "" }}'
                    },
                    // Timeout de 15 segundos
                    signal: AbortSignal.timeout(15000)
                });
                
                if (!response.ok) {
                    throw new Error(`API Error ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                // La API puede devolver: { descarga: "..."}
                const ruta = data.descarga;
                
                if (!ruta || typeof ruta !== 'string') {
                    throw new Error('La API no devolvió una ruta válida para el PDF');
                }
                
                return ruta;
                
            } catch (error) {
                console.error('❌ Error al obtener banda horaria:', error);
                throw error;
            }
        }
        
        // ========================================
        // ENVÍO DEL FORMULARIO (AJAX + FETCH)
        // ========================================
        function initFormSubmit() {
            // Event delegation con capture phase para asegurar prioridad
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.id === CONFIG.formId) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const form = e.target;
                    const chkBanda = $(CONFIG.chkBandaId);
                    const bandaStatus = $(CONFIG.bandaStatusId);
                    const legajo = form.querySelector('input[name="legajo"]')?.value;
                    
                    // Validaciones básicas
                    const motivo = form.querySelector('#motivo')?.value?.trim();
                    const descripcion = form.querySelector('#descripcion')?.value?.trim();
                    
                    if (!motivo || !descripcion) {
                        mostrarMensaje('⚠️ Completá los campos obligatorios (Motivo y Descripción)', 'warning');
                        return;
                    }
                    
                    // Estado de carga inicial
                    setLoading(true);
                    mostrarMensaje('', 'success'); // Limpiar mensajes previos
                    
                    // Función principal de envío
                    const enviarReclamo = async (bandaPdfPath = null) => {
                        try {
                            // Si hay ruta de PDF, agregar/actualizar campo oculto
                            if (bandaPdfPath) {
                                let hidden = document.getElementById('banda_pdf_path');
                                if (!hidden) {
                                    hidden = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.id = 'banda_pdf_path';
                                    hidden.name = 'banda_pdf_path';
                                    form.appendChild(hidden);
                                }
                                hidden.value = bandaPdfPath;
                            }
                            
                            const formData = new FormData(form);
                            
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                                }
                            });
                            
                            // Validar que sea JSON
                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                throw new Error('Respuesta inválida del servidor');
                            }
                            
                            if (!response.ok) {
                                const errorData = await response.json().catch(() => ({}));
                                throw new Error(errorData.message || `Error ${response.status}`);
                            }
                            
                            const data = await response.json();
                            
                            // ✅ ÉXITO
                            mostrarMensaje(data.message || 'Reclamo enviado correctamente', 'success');
                            form.reset();
                            
                            // Limpiar estado de banda
                            if (chkBanda) {
                                chkBanda.checked = false;
                                chkBanda.dispatchEvent(new Event('change'));
                            }
                            
                            // Cerrar modal después de 1.5s
                            setTimeout(() => {
                                const modalEl = $(CONFIG.modalId);
                                const modal = bootstrap.Modal?.getInstance(modalEl);
                                if (modal) modal.hide();
                            }, 1500);
                            
                        } catch (error) {
                            console.error('❌ Error al enviar reclamo:', error);
                            mostrarMensaje('❌ ' + (error.message || 'Error al enviar. Intentá nuevamente.'), 'danger');
                        } finally {
                            setLoading(false);
                        }
                    };
                    
                    // Si NO quiere adjuntar banda → enviar directo
                    if (!chkBanda?.checked) {
                        enviarReclamo();
                        return;
                    }
                    
                    // Si QUIERE adjuntar banda → consultar API primero
                    const anio = form.querySelector('#bandaAnio')?.value;
                    const mes = form.querySelector('#bandaMes')?.value;
                    
                    if (!legajo || !anio || !mes) {
                        setLoading(false);
                        if (bandaStatus) {
                            bandaStatus.textContent = '⚠️ Completá legajo, año y mes';
                            bandaStatus.classList.add('text-danger');
                        }
                        return;
                    }
                    
                    // Mostrar estado de consulta a API
                    if (bandaStatus) {
                        bandaStatus.textContent = '⏳ Consultando banda horaria...';
                        bandaStatus.classList.remove('text-success', 'text-danger');
                    }
                    
                    obtenerRutaBandaHoraria(legajo, anio, mes)
                        .then(rutaPdf => {
                            if (bandaStatus) {
                                bandaStatus.textContent = '✅ PDF listo. Enviando reclamo...';
                                bandaStatus.classList.add('text-success');
                            }
                            // Continuar con envío incluyendo la ruta
                            return enviarReclamo(rutaPdf);
                        })
                        .catch(error => {
                            // Error en API de bandas → preguntar si continúa sin adjunto
                            if (bandaStatus) {
                                bandaStatus.textContent = '⚠️ No se pudo obtener el PDF';
                                bandaStatus.classList.add('text-danger');
                            }
                            
                            const continuar = confirm(
                                'No se pudo generar la banda horaria.\n\n¿Deseás enviar el reclamo SIN el adjunto?'
                            );
                            
                            if (continuar) {
                                // Enviar sin adjunto
                                if (bandaStatus) {
                                    bandaStatus.textContent = '📤 Enviando sin banda horaria...';
                                    bandaStatus.classList.remove('text-success');
                                    bandaStatus.classList.add('text-warning');
                                }
                                return enviarReclamo(null);
                            } else {
                                // Cancelar envío
                                setLoading(false);
                                mostrarMensaje('⚠️ Envío cancelado. Verificá los datos e intentá nuevamente.', 'warning');
                            }
                        });
                }
            }, true); //Capture phase para prioridad
        }
        
        // ========================================
        // 🧹 LIMPIEZA AL CERRAR MODAL
        // ========================================
        function initModalCleanup() {
            const modalEl = $(CONFIG.modalId);
            if (!modalEl) return;
            
            modalEl.addEventListener('hidden.bs.modal', function() {
                const form = $(CONFIG.formId);
                const mensaje = $(CONFIG.mensajeId);
                const bandaStatus = $(CONFIG.bandaStatusId);
                const chkBanda = $(CONFIG.chkBandaId);
                
                // Resetear formulario
                if (form) form.reset();
                
                // Ocultar mensajes
                if (mensaje) mensaje.classList.add('d-none');
                if (bandaStatus) {
                    bandaStatus.textContent = '';
                    bandaStatus.classList.remove('text-success', 'text-danger', 'text-warning');
                }
                
                // Desmarcar checkbox y ocultar opciones
                if (chkBanda) {
                    chkBanda.checked = false;
                    chkBanda.dispatchEvent(new Event('change'));
                }
                
                // Remover campo oculto de PDF si existe
                const hidden = document.getElementById('banda_pdf_path');
                if (hidden) hidden.remove();
                
                // Restaurar botón
                setLoading(false);
            });
        }
        
        // ========================================
        // 🚀 INICIALIZACIÓN
        // ========================================
        function init() {
            initBandaToggle();
            initBandaFeedback();
            initFormSubmit();
            initModalCleanup();
            console.log('Módulo de Reclamos inicializado');
        }
        
        // Ejecutar
        init();
    });
      </script>
</head>
<body>
@php
    // v4: datos comunes del diseño (legajo, observaciones sin leer, sección y pantalla activas)
    $nrLegajo = Auth::user()->primario()->first()->A01A01;
    $nrSinLeer = Migweba::where('A01A01', $nrLegajo)->first()->observaciones()->where('estado', 0)->count();
    $nrE = Vista::estado(isset($subtab) ? $subtab : '', isset($active) ? $active : '');
    $nrSec = $nrE['sec'];
    $nrPag = $nrE['pag'];
    $nrSecTexto = $nrE['secTexto'];
    $nrPagTexto = $nrE['pagTexto'];
@endphp
@include('partials.nr-iconos')

<div id="nr-app" class="app">
    @include('partials.nr-nav')
    <div class="main">
        @include('partials.nr-topbar')
        <main class="content">
            @yield('content')
        </main>
    </div>
</div>

<!-- Observaciones (mismo comportamiento que antes: se cargan por AJAX y se marcan como leídas) -->
<div class="modal fade" id="observacionModal" tabindex="-1" aria-labelledby="observacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="observacionModalLabel">Observaciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="obs" id="observacionContainer">
                    <div class="empty">Cargando…</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cambio de contraseña (se conserva igual que en la versión anterior) -->
<div id="changePasswordModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="changePasswordForm" method="POST" action="{{ route('change_password') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label" for="current_password">Contraseña actual</label><input type="password" name="current_password" id="current_password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label" for="new_password">Nueva contraseña</label><input type="password" name="new_password" id="new_password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label" for="confirm_password">Confirmar contraseña</label><input type="password" name="confirm_password" id="confirm_password" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/newrol.js') . '?v=' . date('dmYhis') }}"></script>
<script type="text/javascript">

                                $(document).ready(function () {
                                    $('#salir').on('click', function () {
                                        javascript:window.location.href='{{ route("logout") }}';
                                    });

                                });

                                function loadPdf(pdfUrl) {
                                    // Show the loader
                                    document.getElementById('pdfLoader').style.display = 'flex';
                                    document.getElementById('pdfFrame').style.display = 'none';

                                    // Update the iframe's src attribute with the provided PDF URL
                                    document.getElementById('pdfFrame').src = pdfUrl;
                                }
                                function hideLoader() {
                                    // Hide the loader and show the iframe
                                    document.getElementById('pdfLoader').style.display = 'none';
                                    document.getElementById('pdfFrame').style.display = 'block';
                                };

                                $(document).ready(function() {
                                    // When the modal is about to be shown, load the content dynamically
                                    $('#dynamicModal').on('show.bs.modal', function() {
                                        // Make an AJAX request to fetch the modal content
                                        $.ajax({
                                            url: "{{route('rrhh.novedades.crear', $legajo)}}",
                                            type: "GET",
                                            success: function(response) {
                                                // Insert the response (view) into the modal body
                                                $('#modalContent').html(response);
                                            },
                                            error: function(xhr) {
                                                // Handle error
                                                $('#modalContent').html('<p>An error occurred while loading the content.</p>');
                                            }
                                        });
                                    });
                                    // Cuando se cierra el modal, limpiar su contenido para la próxima vez
                                    $('#dynamicModal').on('hidden.bs.modal', function () {
                                        window.location.reload();
                                        $('#modalContent').html('');
                                    });
                                });
                                
                                
                                function cargarArchivosAdjuntos(adjuntos) {
                                    let contenedor = document.getElementById('contenedor_adjuntos');
                                
                                    contenedor.innerHTML = '';

                                    precargarAdjuntos(adjuntos, true, 'AUT');
                                }

                                function borrarArchivosAdjuntos()
                                {
                                    let contenedor = document.getElementById('contenedor_adjuntos');
                                    contenedor.innerHTML = '';
                                }

                                function confirmarAccion() {
                                    // Copiamos los adjuntos actuales del modal
                                    let legajo = $('#actualizar-legajo').val();
                                    let novedad = $('#actualizar-novedad').val();
                                    let files = document.getElementById('adjuntos').files;
                                    let dt = new DataTransfer();
                                    let input = document.getElementById('actualizar-adjuntos');

                                    for(let i=0; i<files.length; i++) {
                                        let f = files[i];
                                        dt.items.add(new File([f.slice(0, f.size, f.type)],f.name));
                                    }

                                    input.files = dt.files;

                                    $('#form-actualizar').submit();
                                    }
                                
                                
                                    document.addEventListener('DOMContentLoaded', function() {
                                    // Function to update the badge count
                                    function updateBadgeCount() {
                                        if (! {{ Auth::check() ? 'true' : 'false' }}) return;

                                        fetch('{{route("observaciones.unreadCount")}}')
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data) {
                                                    const badge = document.getElementById('notification-badge');
                                                    // v4: el contador se muestra en la barra superior y en el menú
                                                    document.querySelectorAll('.nr-obs-count').forEach(function (b) {
                                                        b.textContent = data.count;
                                                        b.style.display = data.count > 0 ? '' : 'none';
                                                    });
                                                }
                                            });
                                    }

                                    // Initial check + poll every 30 seconds
                                    updateBadgeCount();
                                    setInterval(updateBadgeCount, 10000);

                                    // Existing modal logic
                                    const messagesModal = document.getElementById('observacionModal');
                                    messagesModal.addEventListener('show.bs.modal', function() {
                                        fetch('{{route("observaciones.unread")}}')
                                            .then(response => response.text())
                                            .then(html => {
                                                document.getElementById('observacionContainer').innerHTML = html;
                                            // 2. Mark all as read after messages are loaded
                                                    return fetch('{{route("observaciones.markAsRead")}}', {
                                                        method: 'GET',
                                                        headers: {
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                        }
                                                    });
                                                })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        // 3. Update badge count immediately
                                                        updateBadgeCount();
                                                    }
                                                });
                                        });
                                    });

                                    //Cambio de contraseña
                                    $(document).ready(function () {
                                        $('#changePasswordForm').on('submit', function (e) {
                                            e.preventDefault(); // Prevent default form submission

                                            $.ajax({
                                                url: $(this).attr('action'),
                                                method: $(this).attr('method'),
                                                data: $(this).serialize(),
                                                success: function (response) {
                                                    if (response.success) {
                                                        // Show success message
                                                        alert(response.message);

                                                        // Close the modal
                                                        $('#changePasswordModal').modal('hide');

                                                        // Reset the form
                                                        $('#changePasswordForm')[0].reset();
                                                    } else {
                                                        // Show error message
                                                        alert(response.message);
                                                    }
                                                },
                                                error: function () {
                                                    alert('An error occurred while processing your request.');
                                                }
                                            });
                                        });
                                    });
                                    
                                    if (document.getElementById('formSalida')) { document.getElementById('formSalida').submit(); } // v4: solo donde hay marco externo

                                    function mesAnterior() {
                                        const urlBandas = "{{ route('asistencia.bandasHorarias') }}?year=" + {{$mes==1? $anio-1: $anio}} + "&month=" + {{$mes==1? 12 : $mes -1}};
                                        window.location.href = urlBandas;
                                    }

                                    function mesSiguiente() {
                                        const urlBandas = "{{ route('asistencia.bandasHorarias') }}?year=" + {{$mes==12? $anio+1: $anio}} + "&month=" + {{$mes==12? 1 : $mes +1}};
                                        window.location.href = urlBandas;
                                    }
                                    
                                </script>
                                <script src="{{ asset('assets/js/imageupload.js') }}?v={{date('Ymdhi')}}"></script>
                                <script src="{{ asset('assets/js/datepicker.js')}}"></script>
</body>
</html>
