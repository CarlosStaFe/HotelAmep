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
                    <div class="col-md-1">
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
@stop

@section('css')
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>


    <script>
        let calendario;
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
                displayEventTime: false
            });
        
            calendario.render();
        });

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
            const doble = Number(document.getElementById('doble').value);
            const simple = Number(document.getElementById('simple').value);
            const cuna = document.getElementById('cuna').value;
            const vista = document.getElementById('vista').value;
            const aire = document.getElementById('aire').value;
            const bano = document.getElementById('bano').value;

            const habitacionesFiltradas = habitaciones.filter(habitacion => {
                if (estado !== 'Todas' && normalizar(habitacion.estado) !== normalizar(estado)) {
                    return false;
                }

                const tieneEventoNoDisponible = eventosCalendario.some(evento => {
                    const idHabitacion = evento.habitacion_id || evento.habitacion?.id;
                    const inicioEvento = obtenerFecha(evento.start);
                    const finEvento = obtenerFecha(evento.end || evento.start);

                    return String(idHabitacion) === String(habitacion.id)
                        && normalizar(evento.estado) !== normalizar('Disponible')
                        && inicioEvento <= fechaFin
                        && finEvento >= fechaInicio;
                });

                if (estado === 'Disponible' && tieneEventoNoDisponible) return false;
                if (pasajeros > 0 && Number(habitacion.plazas || 0) < pasajeros) return false;
                if (doble > 0 && Number(habitacion.matri || 0) < doble) return false;
                if (simple > 0 && Number(habitacion.simple || 0) < simple) return false;
                if (cuna !== 'Todas' && normalizar(habitacion.cuna) !== normalizar(cuna)) return false;
                if (vista !== 'Todas' && normalizar(habitacion.vista) !== normalizar(vista)) return false;
                if (aire !== 'Todas' && normalizar(habitacion.aire) !== normalizar(aire)) return false;
                if (bano !== 'Todas' && normalizar(habitacion.toilette) !== normalizar(bano)) return false;

                return true;
            });

            const eventosFiltrados = eventosCalendario.filter(evento => {
                const habitacion = evento.habitacion || {};
                const inicioEvento = obtenerFecha(evento.start);
                const finEvento = obtenerFecha(evento.end || evento.start);
                const estadoEvento = normalizar(evento.estado);
                const estadoHabitacion = normalizar(habitacion.estado);

                if (!inicioEvento || !finEvento || inicioEvento > fechaFin || finEvento < fechaInicio) {
                    return false;
                }

                if (estado !== 'Todas' && estadoEvento !== normalizar(estado)) {
                    return false;
                }

                if (estado === 'Disponible' && estadoHabitacion !== normalizar('Disponible')) {
                    return false;
                }

                if (pasajeros > 0 && Number(habitacion.plazas || 0) < pasajeros) return false;
                if (doble > 0 && Number(habitacion.matri || 0) < doble) return false;
                if (simple > 0 && Number(habitacion.simple || 0) < simple) return false;
                if (cuna !== 'Todas' && normalizar(habitacion.cuna) !== normalizar(cuna)) return false;
                if (vista !== 'Todas' && normalizar(habitacion.vista) !== normalizar(vista)) return false;
                if (aire !== 'Todas' && normalizar(habitacion.aire) !== normalizar(aire)) return false;
                if (bano !== 'Todas' && normalizar(habitacion.toilette) !== normalizar(bano)) return false;

                return true;
            });

            calendario.removeAllEvents();
            calendario.addEventSource(eventosFiltrados);
            calendario.gotoDate(fechaInicio);

            const cuerpoResultados = document.getElementById('habitacionesResultadosBody');
            const tablaResultados = document.getElementById('habitacionesResultados');
            cuerpoResultados.replaceChildren();

            habitacionesFiltradas.forEach(habitacion => {
                const fila = document.createElement('tr');
                [
                    habitacion.numero,
                    habitacion.tipo,
                    habitacion.plazas,
                    habitacion.matri,
                    habitacion.simple,
                    habitacion.vista,
                    habitacion.aire,
                    habitacion.cuna,
                    habitacion.toilette === 'G' ? 'Grande' : habitacion.toilette === 'N' ? 'Normal' : habitacion.toilette
                ].forEach(valor => {
                    const celda = document.createElement('td');
                    celda.textContent = valor ?? '-';
                    fila.appendChild(celda);
                });
                cuerpoResultados.appendChild(fila);
            });

            tablaResultados.classList.toggle('d-none', habitacionesFiltradas.length === 0);
            mensaje.textContent = habitacionesFiltradas.length
                ? `Se encontraron ${habitacionesFiltradas.length} habitación(es).`
                : 'No se encontraron habitaciones disponibles para los filtros seleccionados.';
            mensaje.classList.remove('d-none');
        }
    </script>
@stop
