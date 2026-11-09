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
        <div class="modal-dialog modal-lg" role="document">
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
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="ocupacion_habitacion_id">Habitación <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="ocupacion_habitacion_id" name="habitacion_id" onchange="cambiarHabitacionModal()" required>
                                    <option value="">Seleccione</option>
                                </select>
                                @error('habitacion_id')
                                    <small class="text-danger ocupacion-error">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="ocupacion_fecha_inicio">Check-In <b style="color: red;">(*)</b></label>
                                <input type="date" class="form-control" id="ocupacion_fecha_inicio" name="fecha_inicio"
                                    value="{{ old('fecha_inicio') }}" required>
                                @error('fecha_inicio')
                                    <small class="text-danger ocupacion-error">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="ocupacion_fecha_fin">Check-Out <b style="color: red;">(*)</b></label>
                                <input type="date" class="form-control" id="ocupacion_fecha_fin" name="fecha_fin"
                                    value="{{ old('fecha_fin') }}" required>
                                @error('fecha_fin')
                                    <small class="text-danger ocupacion-error">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="ocupacion_pasajero_id">Pasajero <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="ocupacion_pasajero_id" name="pasajero_id" required>
                                    <option value="">Seleccione un pasajero</option>
                                    @foreach ($pasajeros ?? [] as $pasajero)
                                        @php
                                            $edadPasajero = $pasajero->fechaNacimiento
                                                ? (int) floor(abs(\Carbon\Carbon::parse($pasajero->fechaNacimiento)->diffInYears(now())))
                                                : null;
                                        @endphp
                                        <option value="{{ $pasajero->id }}" data-cuenta-pax="{{ is_numeric($edadPasajero) && $edadPasajero < 5 ? '0' : '1' }}"  {{ old('pasajero_id') == $pasajero->id ? 'selected' : '' }} {{ is_numeric($edadPasajero) && $edadPasajero < 5 ? 'style=color:red' : '' }}>
                                            {{ $pasajero->apelyNombre }} - {{ $pasajero->documento }} - Edad: {{ $edadPasajero ?? 'N/D' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pasajero_id')
                                    <small class="text-danger ocupacion-error">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="ocupacion_comanda">Comanda? <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="ocupacion_comanda" name="comanda" required>
                                    <option value="">Elija</option>
                                    <option value="SI" {{ old('comanda') == 'SI' ? 'selected' : '' }}>SI</option>
                                    <option value="NO" {{ old('comanda') == 'NO' ? 'selected' : '' }}>NO</option>
                                </select>
                                @error('comanda')
                                    <small class="text-danger ocupacion-error">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary" onclick="agregarPasajeroModal()">Asignar</button>
                            </div>
                        </div>
                        <div id="pasajerosModalMensaje" class="text-danger mb-2" role="alert"></div>
{{--                         <p class="text-muted">La lista es temporal y no se guarda al reservar.</p> --}}
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Pasajero</th>
                                        <th>Comanda</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="pasajerosModalBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="limpiarFormularioOcupacion()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Reservar</button>
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
        let habitacionAnterior = @json(old('habitacion_id', ''));
        let habitacionSeleccionadaModal = '';
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

        function agregarPasajeroModal() {
            const habitacion = document.getElementById('ocupacion_habitacion_id');
            const pasajero = document.getElementById('ocupacion_pasajero_id');
            const comanda = document.getElementById('ocupacion_comanda');
            const cuerpo = document.getElementById('pasajerosModalBody');
            const mensaje = document.getElementById('pasajerosModalMensaje');
            mensaje.textContent = '';

            if (!habitacion.value) {
                mensaje.textContent = 'Seleccione una habitación.';
                habitacion.focus();
                return;
            }

            if (!pasajero.value || !comanda.value) {
                mensaje.textContent = 'Seleccione un pasajero y una opción de comanda.';
                return;
            }

            if (Array.from(cuerpo.rows).some(fila => fila.dataset.pasajeroId === pasajero.value)) {
                mensaje.textContent = 'El pasajero ya está en la lista.';
                return;
            }

            const plazas = obtenerPlazasHabitacion(habitacion.value);
            if (plazas === null) {
                mensaje.textContent = 'No se pudo determinar la cantidad de plazas de la habitación.';
                return;
            }
            const cuentaPax = pasajero.options[pasajero.selectedIndex].dataset.cuentaPax !== '0';
            if (cuentaPax && contarPasajerosModal() >= plazas) {
                mensaje.textContent = `La habitación tiene ${plazas} plaza(s); no se pueden agregar más pasajeros.`;
                return;
            }

            const fila = document.createElement('tr');
            fila.dataset.pasajeroId = pasajero.value;
            fila.dataset.cuentaPax = cuentaPax ? '1' : '0';
            const nombre = document.createElement('td');
            nombre.textContent = pasajero.options[pasajero.selectedIndex].textContent.trim();
            const etiquetaNombre = nombre.textContent;
            if (!cuentaPax) nombre.textContent += ' (menor de 5 años, no cuenta como pasajero)';
            const opcionComanda = document.createElement('td');
            opcionComanda.textContent = comanda.value;
            const acciones = document.createElement('td');
            const quitar = document.createElement('button');
            quitar.type = 'button';
            quitar.className = 'btn btn-sm btn-outline-danger';
            quitar.textContent = 'Quitar';
            quitar.setAttribute('aria-label', `Quitar a ${etiquetaNombre}`);
            quitar.addEventListener('click', () => {
                fila.remove();
                mensaje.textContent = '';
            });
            acciones.appendChild(quitar);
            fila.appendChild(nombre);
            fila.appendChild(opcionComanda);
            fila.appendChild(acciones);
            cuerpo.appendChild(fila);
        }

        function contarPasajerosModal() {
            return Array.from(document.getElementById('pasajerosModalBody').rows)
                .filter(fila => fila.dataset.cuentaPax !== '0').length;
        }

        function obtenerPlazasHabitacion(id) {
            const habitacion = habitaciones.find(item => String(item.id) === String(id));
            if (!habitacion || habitacion.plazas === null || habitacion.plazas === '') return null;
            const plazas = Number(habitacion.plazas);
            return Number.isInteger(plazas) && plazas >= 0 ? plazas : null;
        }

        function cambiarHabitacionModal() {
            const select = document.getElementById('ocupacion_habitacion_id');
            const cantidad = contarPasajerosModal();
            const mensaje = document.getElementById('pasajerosModalMensaje');
            const plazas = obtenerPlazasHabitacion(select.value);
            mensaje.textContent = '';

            if (cantidad > 0 && (plazas === null || cantidad > plazas)) {
                select.value = habitacionSeleccionadaModal;
                mensaje.textContent = 'No se puede cambiar de habitación: quite pasajeros o elija una habitación con suficientes plazas.';
                return;
            }

            habitacionSeleccionadaModal = select.value;
        }

        function limpiarFormularioOcupacion() {
            document.getElementById('pasajerosModalBody').replaceChildren();
            document.getElementById('pasajerosModalMensaje').textContent = '';
            ['ocupacion_habitacion_id', 'ocupacion_fecha_inicio', 'ocupacion_fecha_fin',
                'ocupacion_pasajero_id', 'ocupacion_comanda'].forEach(id => {
                document.getElementById(id).value = '';
            });
            document.querySelectorAll('#ocupacionModal .ocupacion-error').forEach(error => error.remove());
            habitacionAnterior = '';
            habitacionSeleccionadaModal = '';
        }

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
            const seleccionAnterior = selectHabitacion.value || habitacionAnterior;

            selectHabitacion.replaceChildren(new Option('Seleccione una habitación', ''));
            habitacionesFiltradasActuales.forEach(habitacion => {
                const opcion = new Option(
                    `${habitacion.numero ?? ''} - ${habitacion.tipo ?? ''}`,
                    habitacion.id
                );
                opcion.selected = String(habitacion.id) === String(seleccionAnterior);
                selectHabitacion.add(opcion);
            });
            if (!selectHabitacion.value && document.getElementById('pasajerosModalBody').rows.length > 0) {
                document.getElementById('pasajerosModalBody').replaceChildren();
                document.getElementById('pasajerosModalMensaje').textContent =
                    'La habitación seleccionada ya no está en los resultados; se limpió la lista temporal.';
            }
            habitacionSeleccionadaModal = selectHabitacion.value;
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
