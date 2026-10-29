@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Calendario</li>
            <li class="breadcrumb-item active" aria-current="page">Editar Calendario</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Calendario de disponibilidad</h3>
                </div>
                <br>
                <div class="row mb-3">
                    <div class="col-md-1 ml-1">
                        <label for="fecha_inicio">FECHA INICIO <b style="color: red;">(*)</b></label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" 
                               value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                        @error('fecha_inicio')
                            <small style="color: red">{{ $message }}</small>
                        @enderror
                    </div>
                        <div class="col-md-1 ml-1">
                            <label for="fecha_fin">FECHA FIN <b style="color: red;">(*)</b></label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" 
                                   value="{{ date('Y-m-d', strtotime('+30 days')) }}" min="{{ date('Y-m-d') }}" required>
                            @error('fecha_fin')
                                <small style="color: red">{{ $message }}</small>
                            @enderror
                    </div>
                    <div class="col-md-1">
                        <label for="estado">ESTADO <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="estado" name="estado" required>
                            <option value="Disponible" selected>Disponible</option>
                            <option value="Todas">Todas</option>
                            <option value="No Disponible">No Disponible</option>
                            <option value="Reservada">Reservada</option>
                            <option value="Ocupada">Ocupada</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label for="pasajeros">TOTAL PAX <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="pasajeros" name="pasajeros" required>
                            <option value="0" selected>0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                        </select>
                    </div>
{{--                     <div class="col-md-1">
                        <label for="doble">CAMA DOBLE <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="doble" name="doble" required>
                            <option value="0" selected>0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label for="simple">CAMA SIMPLE <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="simple" name="simple" required>
                            <option value="0" selected>0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label for="cuna">CUNA <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="cuna" name="cuna" required>
                            <option value="Todas" selected>Todas</option>
                            <option value="NO">NO</option>
                            <option value="SI">SI</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label for="vista">VISTA <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="vista" name="vista" required>
                            <option value="Todas" selected>Todas</option>
                            <option value="Calle">Calle</option>
                            <option value="Cerro">Cerro</option>
                            <option value="Jardín">Jardín</option>
                            <option value="Patio">Patio</option>
                            <option value="Pileta">Pileta</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label for="aire">AIRE <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="aire" name="aire" required>
                            <option value="Todas" selected>Todas</option>
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label for="bano">BAÑO <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="bano" name="bano" required>
                            <option value="Todas" selected>Todas</option>
                            <option value="G">Grande</option>
                            <option value="N">Normal</option>
                        </select>
                    </div>
                </div> --}}
                <div class="row mb-1">
                    <div class="col-md-4 d-flex align-items-center mt-4">
                        <button type="button" class="btn btn-primary ml-3 mr-2" id="buscarTurnoBtn"
                            onclick="filtrarCalendario()">
                            Buscar
                        </button>
                        <button type="button" class="btn btn-danger ml-3 mr-2" id="suspenderTurnoBtn"
                            onclick="limpiarFiltros()">
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ocupacionModal" tabindex="-1" role="dialog" aria-labelledby="ocupacionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('calendarios.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="origen" value="calendario">
                    <input type="hidden" name="estado" value="Ocupada">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ocupacionModalLabel">Cargar fechas de ocupación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="ocupacion_habitacion_id">Habitación</label>
                            <select class="form-control" id="ocupacion_habitacion_id" name="habitacion_id" required>
                                <option value="">Seleccione una habitación</option>
                            </select>
                            @error('habitacion_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="ocupacion_fecha_inicio">Fecha de ingreso</label>
                                <input type="date" class="form-control" id="ocupacion_fecha_inicio" name="fecha_inicio"
                                    value="{{ old('fecha_inicio') }}" required>
                                @error('fecha_inicio')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="ocupacion_fecha_fin">Fecha de egreso</label>
                                <input type="date" class="form-control" id="ocupacion_fecha_fin" name="fecha_fin"
                                    value="{{ old('fecha_fin') }}" required>
                                @error('fecha_fin')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar ocupación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>


    <script>
        let calendario;
        let habitacionesFiltradasActuales = [];
        const eventosCalendario = @json($calendario ?? []);
        const habitaciones = @json($habitaciones ?? []);

        const normalizar = valor => String(valor ?? '')
            .trim()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('es')
            .replace(/[ _-]/g, '');

        const obtenerFecha = valor => {
            if (!valor) return '';
            const texto = String(valor).trim();
            return texto.length >= 10 ? texto.slice(0, 10) : texto;
        };

        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
        
            calendario = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 'auto',
                displayEventTime: false,
                eventDisplay: 'block',
                selectable: true,
                selectMirror: true,
                eventDidMount: function(info) {
                    info.el.title = info.event.title;
                },
                eventContent: function(info) {
                    const title = info.event.title;
                    const match = title.match(/^(Habitación\s+\S+)(.*)$/);
                    const content = document.createElement('span');
                    content.style.display = 'block';
                    content.style.whiteSpace = 'nowrap';
                    content.style.overflow = 'hidden';
                    content.style.textOverflow = 'ellipsis';

                    if (!match) {
                        content.textContent = title;
                        return { domNodes: [content] };
                    }

                    const habitacion = document.createElement('strong');
                    habitacion.textContent = match[1];
                    content.appendChild(habitacion);
                    content.appendChild(document.createTextNode(match[2]));

                    return { domNodes: [content] };
                },
                dateClick: function(info) {
                    abrirModalOcupacion(info.dateStr, info.dateStr);
                },
                select: function(info) {
                    const fechaFin = new Date(info.endStr + 'T00:00:00');
                    fechaFin.setDate(fechaFin.getDate() - 1);
                    abrirModalOcupacion(info.startStr, formatoFechaLocal(fechaFin));
                    calendario.unselect();
                }
            });
        
            calendario.render();

            @if ($errors->any())
                abrirModalOcupacion(
                    @json(old('fecha_inicio', '')),
                    @json(old('fecha_fin', ''))
                );
            @endif
        });

        function abrirModalOcupacion(fechaInicio, fechaFin) {
            actualizarSelectHabitaciones();

            const inicioInput = document.getElementById('ocupacion_fecha_inicio');
            const finInput = document.getElementById('ocupacion_fecha_fin');

            inicioInput.value = fechaInicio;
            finInput.value = fechaFin;

            if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                jQuery('#ocupacionModal').modal('show');
            }
        }

        function actualizarSelectHabitaciones() {
            const selectHabitacion = document.getElementById('ocupacion_habitacion_id');
            const seleccionAnterior = selectHabitacion.value || @json(old('habitacion_id', ''));

            selectHabitacion.replaceChildren(new Option('Seleccione una habitación', ''));
            habitacionesFiltradasActuales.forEach(habitacion => {
                const opcion = new Option(
                    `${habitacion.numero ?? ''} - ${habitacion.tipo ?? ''}`,
                    habitacion.id
                );
                opcion.selected = String(habitacion.id) === String(seleccionAnterior);
                selectHabitacion.add(opcion);
            });
        }

        function formatoFechaLocal(fecha) {
            const anio = fecha.getFullYear();
            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
            const dia = String(fecha.getDate()).padStart(2, '0');

            return `${anio}-${mes}-${dia}`;
        }

        function limpiarFiltros() {
            const hoy = new Date();
            const inicioDefault = hoy.toISOString().slice(0, 10);
            const finDefault = new Date(hoy.getTime() + 30 * 24 * 60 * 60 * 1000)
                .toISOString()
                .slice(0, 10);
        
            document.getElementById('fecha_inicio').value = inicioDefault;
            document.getElementById('fecha_fin').value = finDefault;
        
            ['estado', 'pasajeros', 'doble', 'simple', 'cuna', 'vista', 'aire', 'bano']
                .forEach(id => {
                    const select = document.getElementById(id);
                    if (select) select.selectedIndex = 0;
                });

            const mensaje = document.getElementById('resultadoBusqueda');
            mensaje.classList.add('d-none');
            mensaje.textContent = '';
            document.getElementById('habitacionesResultadosBody').replaceChildren();
            document.getElementById('habitacionesResultados').classList.add('d-none');
            habitacionesFiltradasActuales = [];
            actualizarSelectHabitaciones();
            
            if (!calendario) return;
            
            calendario.getEventSources().forEach(fuente => fuente.remove());
            calendario.removeAllEvents();
            calendario.gotoDate(inicioDefault);
        }

        function filtrarCalendario() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const mensaje = document.getElementById('resultadoBusqueda');

            if (!fechaInicio || !fechaFin || fechaInicio > fechaFin) {
                mensaje.textContent = 'Seleccione un rango de fechas válido.';
                mensaje.classList.remove('d-none');
                return;
            }

            const estado = document.getElementById('estado').value;
            const pasajeros = Number(document.getElementById('pasajeros').value);
/*             const doble = Number(document.getElementById('doble').value);
            const simple = Number(document.getElementById('simple').value);
            const cuna = document.getElementById('cuna').value;
            const vista = document.getElementById('vista').value;
            const aire = document.getElementById('aire').value;
            const bano = document.getElementById('bano').value; */

            const eventosFiltrados = eventosCalendario.filter(evento => {
                const habitacion = evento.habitacion || {};
                const inicioEvento = obtenerFecha(evento.start);
                const finEvento = obtenerFecha(evento.end || evento.start);
                const estadoEvento = normalizar(evento.estado);

                if (!inicioEvento || !finEvento || inicioEvento > fechaFin || finEvento < fechaInicio) {
                    return false;
                }

                if (estado !== 'Todas' && estadoEvento !== normalizar(estado)) {
                    return false;
                }

                if (Number(habitacion.plazas || 0) !== pasajeros) return false;
/*                 if (doble > 0 && Number(habitacion.matri || 0) < doble) return false;
                if (simple > 0 && Number(habitacion.simple || 0) < simple) return false;
                if (cuna !== 'Todas' && normalizar(habitacion.cuna) !== normalizar(cuna)) return false;
                if (vista !== 'Todas' && normalizar(habitacion.vista) !== normalizar(vista)) return false;
                if (aire !== 'Todas' && normalizar(habitacion.aire) !== normalizar(aire)) return false;
                if (bano !== 'Todas' && normalizar(habitacion.toilette) !== normalizar(bano)) return false; */

                return true;
            });

            const idsHabitacionesFiltradas = new Set(eventosFiltrados.map(evento => (
                String(evento.habitacion_id || evento.habitacion?.id)
            )));
            habitacionesFiltradasActuales = habitaciones.filter(habitacion => (
                idsHabitacionesFiltradas.has(String(habitacion.id))
            ));

            actualizarSelectHabitaciones();

            calendario.removeAllEvents();
            calendario.addEventSource(eventosFiltrados);
            calendario.gotoDate(fechaInicio);

            const cuerpoResultados = document.getElementById('habitacionesResultadosBody');
            const tablaResultados = document.getElementById('habitacionesResultados');
            cuerpoResultados.replaceChildren();

            habitacionesFiltradasActuales.forEach(habitacion => {
                const fila = document.createElement('tr');
                [
                    habitacion.numero,
                    habitacion.tipo,
                    habitacion.plazas
/*                     habitacion.matri,
                    habitacion.simple,
                    habitacion.vista,
                    habitacion.aire,
                    habitacion.cuna,
                    habitacion.toilette === 'G' ? 'Grande' : habitacion.toilette === 'N' ? 'Normal' : habitacion.toilette */
                ].forEach(valor => {
                    const celda = document.createElement('td');
                    celda.textContent = valor ?? '-';
                    fila.appendChild(celda);
                });
                cuerpoResultados.appendChild(fila);
            });

            actualizarSelectHabitaciones();

            tablaResultados.classList.toggle('d-none', habitacionesFiltradasActuales.length === 0);
            mensaje.textContent = habitacionesFiltradasActuales.length
                ? `Se encontraron ${habitacionesFiltradasActuales.length} habitación(es).`
                : 'No se encontraron habitaciones disponibles para los filtros seleccionados.';
            mensaje.classList.remove('d-none');
        }
    </script>
@stop
