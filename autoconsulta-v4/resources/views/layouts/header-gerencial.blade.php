<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Consulta de agente · Autoconsulta</title>
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
            const coded = urlParams.get('legajo');
            let decoded = atob(coded);
            let legajoconzero = decoded.slice(0, 6);
            const legajo = legajoconzero.replace(/^0+/, '');
            console.log('Legajo desde URL:', legajo);
            const anioInicial = {{ $anio ?? 'parseInt(urlParams.get("year")) || new Date().getFullYear()' }};
            const mesInicial = {{ $mes ?? 'parseInt(urlParams.get("month")) || (new Date().getMonth() + 1)' }};
            const year = urlParams.get('year'); // e.g., "2023"
            const month = urlParams.get('month'); // e.g., "08"
            var startDate = (year && month) ? `${year}-${month.padStart(2, '0')}-01` : new Date();
            var calendarEl = document.getElementById('calendar');
            if (!calendarEl) { return; } // v4: solo en la pantalla de calendario
            var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            initialDate: startDate,
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
                    url: 'https://sv3000-prod.mecon.gob.ar/Calendario/api/novedades/' + legajo, // use the `url` property
                    textColor: 'black',  // an option!
                },
                {
                    url: 'https://sv3000-prod.mecon.gob.ar/Calendario/api/pendientes/' + legajo, // use the `url` property
                    textColor: 'black', // an option!
                },
                {
                    url: 'https://sv3000-prod.mecon.gob.ar/Calendario/api/feriados/' + legajo,
                     // use the `url` property
                    textColor: 'black',
                    allDay : true
                },
                {
                    url: 'https://sv3000-prod.mecon.gob.ar/Calendario/api/fichadas/' + legajo,
                     // use the `url` property
                    textColor: 'black'
                },
                {
                    url: 'https://sv3000-prod.mecon.gob.ar/Calendario/api/situacionesParticulares/' + legajo,
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
                const params = new URLSearchParams(window.location.search);
                const legajo = params.get('legajo');
                const year = currentDate.getFullYear();
                const month = String(currentDate.getMonth() + 1).padStart(2, '0');

                // Construir URL con query params
                const urlBandas = "{{ route('gerencial.asistencia.bandasHorarias') }}?year=" + year + "&month=" + month + "&legajo=" + legajo;
                const urlaccesos = "{{ route('gerencial.asistencia.accesos') }}?year=" + year + "&month=" + month + "&legajo=" + legajo;

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
      </script>
</head>
<body>
@php
    // v4: sección y pantalla activas de la vista gerencial
    $nrE = Vista::estadoGerencial(isset($subtab) ? $subtab : '', isset($active) ? $active : '');
    $nrSec = $nrE['sec'];
    $nrPag = $nrE['pag'];
    $nrSecTexto = $nrE['secTexto'];
    $nrPagTexto = $nrE['pagTexto'];
@endphp
@include('partials.nr-iconos')

<div id="nr-app" class="app">
    @include('partials.nr-g-nav')
    <div class="main">
        @include('partials.nr-g-topbar')
        <main class="content">
            @yield('content')
        </main>
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
                                                    if (!badge) { return; } // v4: la vista gerencial no muestra observaciones
                                                    if (data.count > 0) {
                                                        badge.textContent = data.count;
                                                        badge.style.display = 'block';
                                                    } else {
                                                        badge.style.display = 'none';
                                                    }
                                                }
                                            });
                                    }

                                    // Initial check + poll every 30 seconds
                                    updateBadgeCount();
                                    setInterval(updateBadgeCount, 10000);

                                    // Existing modal logic
                                    const messagesModal = document.getElementById('observacionModal');
                                    if (messagesModal) messagesModal.addEventListener('show.bs.modal', function() {
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
                                        const params = new URLSearchParams(window.location.search);
                                        const legajo = params.get('legajo');
                                        const urlBandas = "{{ route('gerencial.asistencia.bandasHorarias') }}?year=" + {{$mes==1? $anio-1: $anio}} + "&month=" + {{$mes==1? 12 : $mes -1}} + "&legajo=" + legajo;
                                        window.location.href = urlBandas;
                                    }

                                    function mesSiguiente() {
                                        const params = new URLSearchParams(window.location.search);
                                        const legajo = params.get('legajo');
                                        const urlBandas = "{{ route('gerencial.asistencia.bandasHorarias') }}?year=" + {{$mes==12? $anio+1: $anio}} + "&month=" + {{$mes==12? 1 : $mes +1}} + "&legajo=" + legajo;
                                        window.location.href = urlBandas;
                                    }

                                    document.addEventListener('DOMContentLoaded', function() {
                                        const loader = document.getElementById('iframeLoader');
                                        const iframe = document.querySelector('iframe[name="iframeSalida"], iframe[src]');
                                        
                                        // Función para mostrar el loader
                                        function showLoader() {
                                            if (loader) { loader.style.display = 'flex'; }
                                        }
                                        
                                        // Función para ocultar el loader
                                        function hideLoader() {
                                            if (loader) { loader.style.display = 'none'; }
                                        }
                                        
                                        // Evento cuando el iframe termina de cargar
                                        if (iframe) {
                                            iframe.addEventListener('load', function() {
                                                hideLoader();
                                            });
                                            
                                            // Si es carga por POST, mostrar loader antes de enviar el formulario
                                            @if(isset($usarPost) && $usarPost)
                                                // Pequeño delay para asegurar que el loader se renderice antes del submit
                                                setTimeout(function() {
                                                    showLoader();
                                                    if (document.getElementById('formSalida')) { document.getElementById('formSalida').submit(); }
                                                }, 100);
                                            @else
                                                // Si es carga directa por src, mostrar loader inmediatamente
                                                showLoader();
                                            @endif
                                        }
                                    });                      
                                </script>
                                <script src="{{ asset('assets/js/imageupload.js') }}?v={{date('Ymdhi')}}"></script>
                                <script src="{{ asset('assets/js/datepicker.js')}}"></script>
</body>
</html>
